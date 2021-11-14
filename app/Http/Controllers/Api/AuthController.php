<?php

namespace App\Http\Controllers\Api;

use DB;
use Twilio;
use App\User;
use App\MobileVerification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\User as UserResource;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            /* We first need to understand if the user is logging in
            *  using the web-based application or the USSD application.
            *
            *  Login Via Web:
            *
            *  If they are logging in using their web-based application
            *  then we need to authenticate the user using their email
            *  or mobile number and password.
            *
            *  Login Via USSD:
            *
            *  If they are logging in using the USSD application, then
            *  we must get the application "Grant Token" to check if
            *  this is a valid request from our application, or an
            *  intruder request. If it is valid we must check if
            *  we have a user account that matches the given
            *  mobile number.
            *
            */
            $login_via_ussd = $request->input('login_via_ussd');

            //  Validate "Login Details"
            $validator = Validator::make($request->all(), [
                /* Validation Rules
                 *
                 *  # If we want to login via USSD then make sure that the user provided their mobile number.
                 *  # If we want to login via WEB then make sure that the user provided their mobile number/email and password
                 */

                //  If we must login via USSD, it must be indicated as "true", where "false" has the opposite effect
                'login_via_ussd' => 'sometimes|required|in:true,false',

                //  If we must login via USSD, the mobile number is required and must be a valid mobile number
                'mobile_number' => 'required_if:login_via_ussd,true|regex:/^[0-9]+$/i',

                //  If the mobile number is not provided then a valid email is required by default
                'email' => 'exclude_if:login_via_ussd,true|required_without:mobile_number|email',

                //  If we must login using the mobile number or email then the password is required except on login via USSD
                'password' => 'required_without:login_via_ussd,|required_if:login_via_ussd,false',

            ], [
                //  Login Via USSD Validation Error Messages
                'login_via_ussd.required' => 'The login_via_ussd attribute must be set equal to "true" to implement login via USSD',
                'login_via_ussd.in' => 'The login_via_ussd attribute must be set equal to "true" to implement login via USSD',

                //  Mobile Number Validation Error Messages
                'mobile_number.required_if' => 'The mobile_number attribute is required since you are logging in via USSD',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',

                //  Email Validation Error Messages
                'email.email' => 'Enter a valid email address to login e.g email@example.com',
                'email.required_without' => 'Enter your email to login e.g email@example.com',

                //  Password Validation Error Messages
                'password.required_without' => 'Enter your password to login',
                'password.required_if' => 'Enter your password to login',
            ]);

            //  If the validation failed
            if ($validator->fails()) {
                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());
            }

            //  Get the Email (If provided)
            $email = $request->input('email');

            //  Get the Mobile Number (If provided)
            $mobile_number = $request->input('mobile_number');

            //  Get the Password (If provided)
            $password = $request->input('password');

            //  Get the Password (If provided)
            $password_confirmation = $request->input('password_confirmation');

            //  Get the Verification Code (If provided)
            $verification_code = $request->input('verification_code');

            //  If the confirmation password was provided but does not match the given password
            if (!empty($password_confirmation) && $password != $password_confirmation) {

                //  The passwords do not match
                throw ValidationException::withMessages(['password' => 'The given passwords do not match']);

            }

            //  If we want to login via USSD
            if (in_array($login_via_ussd, [true, 1, 'true', '1'])) {

                /** IMPORTANT SECURITY NOTICE
                 *
                 *  This is where we can check if the API GRANT TOKEN has been provided
                 *  and whether or not the GRANT TOKEN is valid. If it is, we can then
                 *  attempt to login the user that matches the given mobile number
                 *  otherwise we must return a failed login indicating that the
                 *  GRANT TOKEN provided is invalid.
                 */

                //  Get the user that owns the given mobile number
                $user = \App\User::searchMobile($mobile_number)->first();

                //  If the user was found
                if ($user) {

                    //  Login using the given user
                    auth()->loginUsingId($user->id);

                    //  If we are logged in
                    if (auth()->user()) {
                        //  Create new access token
                        return $this->createNewAccessToken();
                    } else {
                        //  Failed to login - Throw 401 error
                        return help_login_failed();
                    }
                } else {

                    //  The account with the given mobile number does not exist. Throw a validation error
                    throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist.']);

                }

                //  If we want to login via WEB
            } else {

                //  Handle the account verification process (If required)
                $handle_account_verification = $this->handleAccountVerification($request);

                $login_requires_update = $handle_account_verification['requires_update'];

                $login_request_data = $handle_account_verification['request_data'];

                $login_user = $handle_account_verification['user'];

                /**
                 *  Its possible that the user could already have an existing account,
                 *  but the mobile number was not verified. In this case we can still
                 *  update the account details and ensure that we mark it as verified
                 */
                if ($login_requires_update) {

                    //  Update the existing user account
                    $login_user->update($login_request_data);

                }

                //  If we must login using the email
                if( !empty( $email ) ){

                    //  Get the login credentials (email and password)
                    $credentials = collect($login_request_data)->only('email', 'password');

                    //  Attempt to login
                    if (auth()->attempt($credentials)) {

                        //  Create new access token
                        return $this->createNewAccessToken();

                    //  If our attempt to login failed
                    } else {

                        //  Check if a user with the given email address exists
                        if (DB::table('users')->where('email', $email)->exists()) {
                            //  Since the email exists, this means that the password is incorrent. Throw a validation error
                            throw ValidationException::withMessages(['password' => 'Your password is incorrect']);
                        } else {
                            //  The account with the given email does not exist. Throw a validation error
                            throw ValidationException::withMessages(['email' => 'The account using the email "'.$email.'" does not exist.']);
                        }
                    }

                //  Otherwise if we must login using the mobile number
                }elseif( !empty( $mobile_number ) ){

                    //  Get the first user that matches the given mobile number
                    $user = \App\User::searchMobile($mobile_number)->first();

                    //  Verify if the provided password matches the user's password
                    if( Hash::check($password, $user->password) ){

                        //  Login using the given user
                        auth()->loginUsingId($user->id);

                        //  If we are logged in
                        if (auth()->user()) {
                            //  Create new access token
                            return $this->createNewAccessToken();
                        } else {
                            //  Failed to login - Throw 401 error
                            return help_login_failed();
                        }

                    }else{

                        //  Check if a user with the given mobile number exists
                        if ( \App\User::searchMobile($mobile_number)->exists() ) {
                            //  Since the mobile number exists, this means that the password is incorrent. Throw a validation error
                            throw ValidationException::withMessages(['password' => 'Your password is incorrect']);
                        } else {
                            //  The account with the given mobile number does not exist. Throw a validation error
                            throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist.']);
                        }
                    }

                }
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function accountExists(Request $request, $complete_user = false, $return_response = true)
    {
        try {

            //  Get the Email (If provided)
            $email = $request->input('email');

            //  Get the Mobile Number (If provided)
            $mobile_number = $request->input('mobile_number');

            //  Set the validation rules
            $validation_rules = [];

            if(!empty($email)){
                $validation_rules['email'] = 'required|email';
            }

            if(!empty($mobile_number)){
                $validation_rules['mobile_number'] = 'required|regex:/^[0-9]+$/i';
            }

            //  Validate
            $validator = Validator::make($request->all(), $validation_rules, [

                //  Email Validation Error Messages
                'email.required' => 'Enter a valid email address e.g email@example.com',
                'email.email' => 'Enter a valid email address e.g email@example.com',

                //  Mobile Number Validation Error Messages
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567'

            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  If we must check if an account exists using the email
            if( !empty( $email ) ){

                //  Get the first user that matches the given email
                $user = $user_via_email_account = \App\User::where('email', $email)->first();

            }

            //  Otherwise if we must check if an account exists using the mobile number
            if( !empty( $mobile_number ) ){

                //  Get the first user that matches the given mobile number
                $user = $user_via_mobile_account = \App\User::searchMobile($mobile_number)->first();

            }

            $response = [
                'user' => !empty($user) ? ($complete_user ? $user : [
                    'last_name' => $user->last_name,
                    'first_name' => $user->first_name,
                    'requires_password' => $user->requires_password,
                    'requires_mobile_number_verification' => $user->requires_mobile_number_verification,
                ]) : null
            ];

            if(!empty($email) && !empty($mobile_number)){

                $response['email_account_exists'] = (isset($user_via_email_account) && !empty($user_via_email_account)) ? true : false;
                $response['mobile_account_exists'] = (isset($user_via_mobile_account) && !empty($user_via_mobile_account)) ? true : false;

            }elseif(!empty($mobile_number)){

                $response['mobile_account_exists'] = (isset($user_via_mobile_account) && !empty($user_via_mobile_account)) ? true : false;

            }elseif(!empty($email)){

                $response['email_account_exists'] = (isset($user_via_email_account) && !empty($user_via_email_account)) ? true : false;

            }

            $response['account_exists'] = (isset($user_via_email_account) && !empty($user_via_email_account)) || (isset($user_via_mobile_account) && !empty($user_via_mobile_account));

            return $return_response ? response()->json($response, 200) : $response;

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function sendMobileAccountVerificationCode(Request $request)
    {
        try {

            //  Validate
            $validator = Validator::make($request->all(), [

                /* Validation Rules
                 *
                 *  # Make sure that the user provided a valid mobile number
                 */
                'mobile_number' => 'required|regex:/^[0-9]+$/i'

            ], [
                //  Mobile Number Validation Error Messages
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Get the Mobile Number
            $mobile_number = $request->input('mobile_number');

            //  Get the first user that matches the given mobile number
            $user = \App\User::searchMobile($mobile_number)->first();

            //  If we have a user that matches the given mobile number
            if($user){

                /**********************************
                 * GENERATE THE VERIFICATION CODE *
                 **********************************/

                //  Generate 6 digit mobile number verification code
                $six_digit_random_number = mt_rand(100000, 999999);

                //  Update the mobile number verification code that matches the given mobile number
                \App\User::searchMobile($mobile_number)->update([
                    'mobile_number_verification_code' => $six_digit_random_number
                ]);

                /************************************************************************
                 * SEND AN SMS TO THE MOBILE NUMBER WITH THE VERIFICATION CODE INCLUDED *
                 ***********************************************************************/

                //  Craft the verification code message
                $message = 'Hi '.$user->first_name.', your verification code is '.$six_digit_random_number;

                //  Send an SMS to the user
                Twilio::message('+'.$mobile_number, $message);

            } else {

                //  The account with the given mobile number does not exist. Throw a validation error
                throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist.']);

            }

            return response()->json(['message' => 'Verification code sent to '.$mobile_number], 200);

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function verifyMobileAccountVerificationCode(Request $request)
    {
        try {

            //  Validate
            $validator = Validator::make($request->all(), [

                /* Validation Rules
                 *
                 *  # Make sure that the user provided a valid mobile number
                 *  # Make sure that the user provided a valid verification code
                 */
                'mobile_number' => 'required|regex:/^[0-9]+$/i',
                'code' => 'required|regex:/^[0-9]+$/i'

            ], [
                //  Mobile Number Validation Error Messages
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',

                //  Mobile Number Validation Error Messages
                'code.required' => 'Enter a valid 6 digit verification code containing only digits e.g 123456',
                'code.regex' => 'Enter a valid 6 digit verification code containing only digits e.g 123456'
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Get the code
            $code = $request->input('code');

            //  Get the Mobile Number
            $mobile_number = $request->input('mobile_number');

            //  Get the first user that matches the given mobile number
            $user = \App\User::searchMobile($mobile_number)->first();

            //  If we have a user that matches the given mobile number
            if($user){

                $exactMatch = false;

                //  If the verification codes match
                if( $user->mobile_number_verification_code === $code ){

                    $exactMatch = true;

                }

                return response()->json(['status' => $exactMatch], 200);

            } else {

                //  The account with the given mobile number does not exist. Throw a validation error
                throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist.']);

            }

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function createNewAccessToken()
    {
        try {
            //  Get the logged in user
            $user = auth()->user();

            //  Create new access token
            $accessToken = $user->createToken('authToken');

            //  Return response
            return response([
                'user' => (new UserResource($user)),
                'access_token' => $accessToken,
            ]);
        } catch (\Exception $e) {
            throw($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            //  Get the authenticated user
            $user = auth()->user();

            //  If we have a user
            if ($user) {
                //  Logout all devices
                if ($request->input('everyone') == 'true' || $request->input('everyone') == '1') {
                    //  This will log out all devices
                    DB::table('oauth_access_tokens')->where('user_id', $user->id)->update([
                        'revoked' => true,
                    ]);

                //  Logout only current device
                } else {
                    //  Get the user's token
                    $token = $user->token();

                    //  Revoke the token
                    $token->revoke();
                }
            }

            //  Return nothing
            return response(null, 200);
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function generateMobileVerificationCode(Request $request)
    {
        try {

            //  Validate
            $validator = Validator::make($request->all(), [

                /* Validation Rules
                 *
                 *  # Make sure that the user provided a valid mobile number
                 */
                'type' => 'required',
                'mobile_number' => 'required|regex:/^[0-9]+$/i',

            ], [
                //  Mobile Number Validation Error Messages
                'type.required' => 'Enter the mobile verification type e.g account_registration, password_reset, order_delivery_confirmation',
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw a validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Generate 6 digit mobile number verification code
            $six_digit_random_number = mt_rand(100000, 999999);

            //  Get the mobile number
            $mobile_number = $request->input('mobile_number');
            $mobile_number = (new MobileVerification)->convertMobileToMsisdn($mobile_number);

            //  Get the mobile verification type
            $type = $request->input('type');

            //  Create new user
            $mobileVerification = MobileVerification::create([
                'type' => (new MobileVerification)->getMobileVerificationType($type),
                'code' => $six_digit_random_number,
                'mobile_number' => $mobile_number
            ]);

            if( $mobileVerification ){

                return response([
                    'message' => 'Mobile verification created successfully'
                ], 200);

            }

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function verifyMobileVerificationCode(Request $request)
    {
        try {

            //  Validate
            $validator = Validator::make($request->all(), [

                /* Validation Rules
                 *
                 *  # Make sure that the user provided a valid mobile number
                 */
                'type' => 'required',
                'code' => 'required',
                'mobile_number' => 'required|regex:/^[0-9]+$/i',

            ], [
                //  Mobile Number Validation Error Messages
                'code.required' => 'Enter the mobile verification code e.g 123456',
                'type.required' => 'Enter the mobile verification type e.g account_registration, password_reset, order_delivery_confirmation',
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw a validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Get the mobile number
            $mobile_number = $request->input('mobile_number');
            $mobile_number = (new MobileVerification)->convertMobileToMsisdn($mobile_number);

            //  Get the mobile verification type
            $type = $request->input('type');

            //  Get the mobile verification code
            $code = $request->input('code');

            //  Find matching mobile verification
            $mobileVerification = MobileVerification::where([
                'code' => $code,
                'type' => $type,
                'mobile_number' => $mobile_number
            ]);

            return response([
                'is_valid' => $mobileVerification->exists()
            ], 200);

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function showMobileVerificationCode(Request $request)
    {
        try {

            //  Get the mobile number
            $mobile_number = $request->input('mobile_number');
            $mobile_number = (new MobileVerification)->convertMobileToMsisdn($mobile_number);

            //  Find matching mobile verification
            $mobileVerification = MobileVerification::where([
                'mobile_number' => $mobile_number
            ])->latest()->first();

            return response([
                'mobile_verification' => $mobileVerification ? $mobileVerification : null
            ], 200);

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function validateRegistration(Request $request)
    {
        try {

            //  Set the verification code (If exists)
            $verification_code = trim( $request->input('verification_code') );

            $custom_validation_messages = [
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ];

            //  If the verificaiton code was not provided
            if( empty( $verification_code ) ){
                /**
                 *  Then we are simply attempting to validate an account that should not exists.
                 *  In this case we should validate the creation of a non-existent account.
                 */
                $validator = Validator::make($request->all(), [
                    'password' => 'required|confirmed',
                    'last_name' => 'required|min:3|max:20',
                    'first_name' => 'required|min:3|max:20',
                    'email' => 'sometimes|required|email|unique:users,email',
                    'mobile_number' => 'sometimes|required|unique:users,mobile_number',
                ], $custom_validation_messages);

            //  If the verificaiton code was provided
            }else{
                /**
                 *  Then we are attempting to validate an account that should be existing.
                 *  In this case we should validate the creation of an existing account.
                 */
                $validator = Validator::make($request->all(), [
                    'password' => 'required|confirmed',
                    'last_name' => 'required|min:3|max:20',
                    'first_name' => 'required|min:3|max:20',
                    'verification_code' => 'required|max:6',
                    'email' => 'sometimes|required|email|unique:users,email',
                    'mobile_number' => 'sometimes|required|unique:users,mobile_number',
                ]);

            }

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw a validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  If the mobile number and email are both not provided
            if(empty($request->input('mobile_number')) && empty($request->input('email'))){

                //  Throw a validation error
                throw ValidationException::withMessages([
                    'mobile_number' => 'The mobile number must be provided if the email is not provided',
                    'email' => 'The emailmust be provided if the mobile number is not provided',
                ]);

            }

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function register(Request $request)
    {
        try {

            //  Validate the registration data
            $this->validateRegistration($request);

            //  Handle the account verification process (If required)
            $handle_account_verification = $this->handleAccountVerification($request);

            $registration_requires_update = $handle_account_verification['requires_update'];

            $registration_request_data = $handle_account_verification['request_data'];

            $user = $handle_account_verification['user'];

            /**
             *  Its possible that the user could already have an existing account,
             *  but the mobile number was not verified. In this case we can still
             *  update the account details and ensure that we mark it as verified
             */
            if ($registration_requires_update) {

                //  Update the existing user account
                $user->update($registration_request_data);

            }else{

                //  Create new user account
                $user = User::create($registration_request_data);

            }

            //  Login using the given user
            auth()->loginUsingId($user->id);

            //  Create new access token
            return $this->createNewAccessToken();


        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function handleAccountVerification($request){

        $request_data = $request->all();

        $email = trim($request->input('email'));

        $mobile_number = trim($request->input('mobile_number'));

        $verification_code = trim($request->input('verification_code'));

        //  Convert the mobile number to MSISDN format
        $request_data['mobile_number'] = (new MobileVerification)->convertMobileToMsisdn($request_data['mobile_number']);

        //  If the verificaiton code was not provided
        if( !empty($verification_code) ){

            $accountExistsInfo = $this->accountExists($request, true, false);
            $account_exists = $accountExistsInfo['account_exists'];
            $user = $accountExistsInfo['user'];

            if( $account_exists ){

                $requires_mobile_number_verification = $user['requires_mobile_number_verification'];
                $requires_password = $user['requires_password'];

                //  If this account required mobile verification
                if( $requires_mobile_number_verification ){

                    //  Search for matching verification codes
                    $verification_code = MobileVerification::searchByMobileAndCode($mobile_number, $verification_code)->first();

                    //  If we have a matching verification code
                    if( $verification_code ){

                        //  Delete account registration verification codes
                        MobileVerification::where(['mobile_number' => $mobile_number, 'type' => 'account_registration'])->delete();

                        //  Set the mobile_number_verified_at
                        $request_data['mobile_number_verified_at'] = \Carbon\Carbon::now();

                    }else{

                        //  Invalid verification code. Throw a validation error
                        throw ValidationException::withMessages(['verification_code' => 'The given mobile verification code is not valid']);

                    }

                }

                //  If this account required a new password
                if( $requires_password == true ){

                    //  If the password was provided
                    if (isset($request_data['password']) && !empty($request_data['password'])) {

                        //  Hash the password using bcrypt
                        $request_data['password'] = bcrypt($request_data['password']);

                    }else{

                        //  Overide the password to the existing password
                        $request_data['password'] = $user->password;

                    }

                }else{

                    //  Overide the password to the existing password
                    $request_data['password'] = $user->password;

                }

                return [
                    'user' => $user,
                    'requires_update' => true,
                    'request_data' => $request_data
                ];

            }else{

                if( !empty($email) ){

                    //  The account with the given mobile number does not exist. Throw a validation error
                    throw ValidationException::withMessages(['email' => 'The account using the email address "'.$email.'" does not exist !!!']);

                }

                if( !empty($mobile_number) ){

                    //  The account with the given mobile number does not exist. Throw a validation error
                    throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist. ***']);

                }

            }

        }

        return [
            'user' => null,
            'requires_update' => false,
            'request_data' => $request_data
        ];
    }

    public function sendPasswordResetLink(Request $request)
    {
        try {
            /** Validate the user input
             *
             *  password_reset_url: The client url used to attach the password reset token
             *  email: The users account email.
             */
            $userData = $request->validate([
                'password_reset_url' => 'url|required',
                'email' => 'email|required',
            ]);

            //  Get the users email
            $email = $userData['email'];

            //  Get the password reset url
            $password_reset_url = $userData['password_reset_url'];

            //  Get the user (If no result is found Laravel will return Null)
            $user = User::where('email', $email)->first();

            /** Check if a user with the given email address exists.
             *  If the $user is equal to NUll, then this will not
             *  pass since the user was not found.
             */
            if ($user) {
                //  Delete any old password reset tokens
                DB::table('password_resets')->where('email', $email)->delete();

                //  Generate a random password reset token (60 characters long)
                $token = Str::random(60);

                //  Create a new password reset token
                DB::table('password_resets')->insert([
                    'email' => $email,
                    'token' => $token,
                ]);

                //  Generate the password reset endpoint and include the users token and email
                $password_reset_link = $password_reset_url.'?token='.$token.'&email='.urlencode($user->email);

                try {
                    //  Send the password reset link to the user's email
                    $sentPasswordResetLinkEmail = Mail::to($user)->send(new SendPasswordResetLink($user, $password_reset_link));

                    //  If the email was not sent successfully
                    if (Mail::failures()) {
                        //  Return fail response
                        return response()->json(['message' => 'We could not send your password reset link to the email provided. Please try again'], 404);
                    }

                    //  Return success response
                    return response(['message' => 'Password reset link has been successfully sent to your email address "'.$email.'"']);
                } catch (\Throwable $e) {
                    //  Handle error
                    return response()->json(['message' => 'Could not reach the mail address to deliver the password reset link. Please try again'], 404);
                }
            } else {
                //  The account with the given email does not exist. Throw a validation error
                throw ValidationException::withMessages(['email' => 'The account using the email "'.$email.'" does not exist.']);
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            //  Validate the user's inputs
            $userData = $request->validate([
                'email' => 'email|required',
                'password' => 'required|confirmed',
            ]);

            //  Get the user's token
            $token = $request->input('token');

            //  Get the user's email
            $email = $request->input('email');

            //  Get the user's password
            $password = $request->input('password');

            //  If the token was not provided
            if (!$token) {
                //  The token was not provided
                return response()->json(['message' => 'You need a token to reset your password'], 404);
            }

            //  Check if a user with the given email address exists
            if (User::where('email', $email)->exists()) {
                //  Check if a user has a valid token
                if (DB::table('password_resets')->where('email', $email)->where('token', $token)->exists()) {
                    //  Get the user
                    $user = User::where('email', $email)->first();

                    //  Hash and update the new password
                    $user->password = bcrypt($password);
                    $user->save();

                    //  Delete all password reset tokens
                    DB::table('password_resets')->where('email', $email)->delete();

                    //  Create new access token
                    $accessToken = $user->createToken('authToken');

                    //  Return response
                    return response([
                        'user' => $user,
                        'access_token' => $accessToken,
                    ]);
                } else {
                    //  The provided token is not valid
                    return response()->json(['message' => 'The token provided is not valid or has expired'], 404);
                }
            } else {
                //  The provided token is not valid
                return response()->json(['message' => 'The account using the email "'.$email.'" does not exist.'], 404);
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }
}
