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
    public function acceptTermsAndConditions(Request $request)
    {
        try {

            $user = auth()->user();

            //  If we are logged in
            if ($user) {

                //  Accept the terms and conditions
                $accepted = $user->update([
                    'accepted_terms_and_conditions' => 1
                ]);

                if( $accepted ){

                    return response()->json([
                        'accepted' => true,
                        'user' => (new UserResource($user))
                    ], 200);

                }

            }

            return response()->json([
                'accepted' => false,
                'user' => (new UserResource($user))
            ], 200);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function register(Request $request)
    {
        try {

            /**
             *  The verification code is required to verify that the mobile number
             *  does indeed belong to the given user. Without the verification
             *  code, the account cannot be created.
             */
            $validator = Validator::make($request->all(), [
                'password' => 'sometimes|required|confirmed',
                'last_name' => 'required|min:3|max:20',
                'first_name' => 'required|min:3|max:20',
                'verification_code' => 'sometimes|required|size:6',
                'mobile_number' => 'required|regex:/^[0-9]+$/i',
            ], [
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw a validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Handle the account verification process (If required)
            $handle_account_verification = $this->handleAccountVerification($request, false);

            $registration_requires_update = $handle_account_verification['requires_update'];

            $registration_request_data = $handle_account_verification['request_data'];

            $user = $handle_account_verification['user'];

            /**
             *  Its possible that the user could already have an existing account,
             *  but the mobile number was not verified or the password was not
             *  set. In this case we can still update the account details and
             *  ensure that we mark it as verified and set the new password
             *  if required. Usually this can happen if the account was
             *  created via USSD.
             */
            if ($registration_requires_update) {

                //  Update the existing user account
                $user->update($registration_request_data);

                //  Log in the user
                return $this->loginUser($user);

            }else{

                //  Get the Mobile Number
                $mobile_number = $request->input('mobile_number');

                //  If the account already exists
                if( \App\User::searchMobile($mobile_number)->exists() ){

                    //  The account with the given mobile number does not exist. Throw a validation error
                    throw ValidationException::withMessages(['mobile_number' => 'Account using the mobile number "'.$mobile_number.'" already exists']);

                }else{

                    //  Create new user account
                    $user = User::create($registration_request_data);

                    //  Log in the user
                    return $this->loginUser($user);

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function login(Request $request)
    {
        try {

            /*  We first need to understand if the user is logging in
             *  using the Mobile App, Web or the USSD application.
             *
             *  Login Via Mobile App / Web:
             *
             *  If they are logging in using their Mobile App or Web application
             *  then we need to authenticate the user using their mobile number
             *  and password.
             *
             *  Login Via USSD:
             *
             *  If they are logging in using the USSD application, then we must
             *  get the application "Grant Token" to check if this is a valid
             *  request from our application, or an intruder request. If it
             *  is valid we must check if we have a user account that
             *  matches the given mobile number.
             *
             */
            $validator = Validator::make($request->all(), [

                /* Validation Rules
                 *
                 *  # If we want to login via USSD then make sure that the user provided their mobile number.
                 *  # If we want to login via Mobile App / WEB then make sure that the user provided their
                 *  mobile number and password
                 */

                //  If we must login via USSD, it must be indicated as "true", where "false" has the opposite effect
                'login_via_ussd' => 'sometimes|required|in:true,false',

                //  The mobile number is required and must be a valid mobile number
                'mobile_number' => 'required|regex:/^[0-9]+$/i',

                //  If we must login using the mobile number then the password is required except on login via USSD
                'password' => 'required_without:login_via_ussd|required_if:login_via_ussd,false|min:6',

            ], [
                //  Login Via USSD Validation Error Messages
                'login_via_ussd.required' => 'The login_via_ussd attribute must be set equal to "true" to implement login via USSD',
                'login_via_ussd.in' => 'The login_via_ussd attribute must be set equal to "true" to implement login via USSD',

                //  Mobile Number Validation Error Messages
                'mobile_number.required_if' => 'The mobile_number attribute is required since you are logging in via USSD',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',

                //  Password Validation Error Messages
                'password.min' => 'Password must contain atleast 6 characters',
                'password.required_without' => 'Enter your password to login',
                'password.required_if' => 'Enter your password to login',
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Get the Login via USSD (If provided)
            $login_via_ussd = $request->input('login_via_ussd');

            //  Get the Mobile Number
            $mobile_number = $request->input('mobile_number');

            //  Get the Password (If provided)
            $password = $request->input('password');

            //  Get the Password Confirmation (If provided)
            $password_confirmation = $request->input('password_confirmation');

            //  If the confirmation password was provided but does not match the given password
            if (!empty($password_confirmation) && $password != $password_confirmation) {

                //  The passwords do not match
                throw ValidationException::withMessages(['password' => 'The given passwords do not match']);

            }

            //  Get the user that owns the given mobile number
            $user = \App\User::searchMobile($mobile_number)->first();

            //  If the user does not exist
            if( !$user ){

                //  The account with the given mobile number does not exist. Throw a validation error
                throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist.']);

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
                return $this->loginUser($user);

            //  If we want to login via Mobile App / WEB
            } else {

                //  Handle the account verification process (If required)
                $handle_account_verification = $this->handleAccountVerification($request, true);

                $login_requires_update = $handle_account_verification['requires_update'];

                $login_request_data = $handle_account_verification['request_data'];

                $login_user = $handle_account_verification['user'];

                /**
                 *  Its possible that the user could already have an existing account,
                 *  but the mobile number was not verified or the password was not
                 *  set. In this case we can still update the account details and
                 *  ensure that we mark it as verified and set the new password
                 *  if required. Usually this can happen if the account was
                 *  created via USSD.
                 */
                if ($login_requires_update) {

                    //  Update the existing user account
                    $login_user->update($login_request_data);

                    //  Get a fresh instance of the user
                    $user = $login_user->fresh();

                }

                //  Verify if the provided password matches the user's password
                if( Hash::check($password, $user->password) ){

                    return $this->loginUser($user);

                }else{

                    //  Since the mobile number exists, this means that the password is incorrent. Throw a validation error
                    throw ValidationException::withMessages(['password' => 'Your password is incorrect']);

                }
            }
        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function accountExists(Request $request, $complete_user = false, $return_response = true)
    {
        try {

            $mobile_number = $request->input('mobile_number');

            $validator = Validator::make($request->all(), [
                'mobile_number' => 'required|regex:/^[0-9]+$/i',
            ], [
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            //  Get the first user that matches the given mobile number
            $user = \App\User::searchMobile($mobile_number)->first();

            $response = [
                'user' => (!empty($user) ? ($complete_user ? $user : [
                    'last_name' => $user->last_name,
                    'first_name' => $user->first_name,
                    'requires_password' => $user->requires_password,
                    'requires_mobile_number_verification' => $user->requires_mobile_number_verification,
                ]) : null),
                'account_exists' => (isset($user) && !empty($user)) ? true : false
            ];

            return $return_response ? response()->json($response, 200) : $response;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function resetPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed',
                'verification_code' => 'required|size:6',
                'mobile_number' => 'required|regex:/^[0-9]+$/i',
            ], [
                'verification_code.size' => 'Enter a valid verification 6 digit verification code',
                'verification_code.required' => 'Enter a valid verification 6 digit verification code',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
            ]);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw a validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

            $mobile_number = trim($request->input('mobile_number'));
            $verification_code = trim($request->input('verification_code'));

            $accountExistsInfo = $this->accountExists($request, true, false);
            $account_exists = $accountExistsInfo['account_exists'];
            $user = $accountExistsInfo['user'];

            //  If the user account exists
            if( $account_exists == true ){

                $request_data = $request->all();

                $request_data['mobile_number_verified_at'] = (new \App\MobileVerification())->verifyMobileVerificationCode($mobile_number, $verification_code, 'password_reset')['mobile_number_verified_at'];

                $request_data['password'] = bcrypt($request_data['password']);

                $updated = $user->update($request_data);

                if( $updated ){

                    //  Login using the given user
                    auth()->loginUsingId($user->id);

                    //  Create new access token
                    return $this->createNewAccessToken();

                }

            }else{

                //  The account with the given mobile number does not exist. Throw a validation error
                throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist']);

            }

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

            }else{

                return help_unauthenticated();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function generateMobileVerificationCode(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [

                /* Validation Rules
                 *
                 *  # Make sure that the user provided a valid mobile number
                 */
                'type' => 'required',
                'mobile_number' => 'required|regex:/^[0-9]+$/i',

            ], [
                //  Mobile Number Validation Error Messages
                'type.required' => 'Enter the mobile verification type e.g account_ownership, account_ownership, order_delivery_confirmation',
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

            //  Get the metadata
            $metadata = $request->input('metadata');

            //  Get the mobile verification type
            $type = $request->input('type');

            //  Create new user
            $mobileVerification = MobileVerification::create([
                'mobile_number' => (new MobileVerification)->convertMobileToMsisdn($mobile_number),
                'type' => (new MobileVerification)->getMobileVerificationType($type),
                'code' => $six_digit_random_number,
                'metadata' => $metadata
            ]);

            if( $mobileVerification ){

                return response([
                    'message' => 'Mobile verification created successfully'
                ], 200);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function checkMobileVerificationCodeValidity(Request $request)
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
                'type.required' => 'Enter the mobile verification type e.g account_ownership, account_ownership, order_delivery_confirmation',
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

            //  Get the mobile verification type
            $type = $request->input('type');

            //  Get the mobile verification code
            $code = $request->input('code');

            //  Find matching mobile verification
            $mobileVerification = MobileVerification::where([
                'mobile_number' => (new MobileVerification)->convertMobileToMsisdn($mobile_number),
                'code' => $code,
                'type' => $type,
            ]);

            return response([
                'is_valid' => $mobileVerification->exists()
            ], 200);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function showMobileVerificationCode(Request $request)
    {
        try {

            //  Get the mobile number
            $mobile_number = $request->input('mobile_number');

            //  Find matching mobile verification
            $mobileVerification = MobileVerification::where([
                'mobile_number' => (new MobileVerification)->convertMobileToMsisdn($mobile_number),
            ])->latest()->first();

            return response([
                'mobile_verification' => $mobileVerification ? $mobileVerification : null
            ], 200);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function loginUser($user){

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

    public function handleAccountVerification($request, $must_have_account = false){

        $request_data = $request->all();

        $mobile_number = trim($request->input('mobile_number'));

        $verification_code = trim($request->input('verification_code'));

        //  Convert the mobile number to MSISDN format
        $request_data['mobile_number'] = (new MobileVerification)->convertMobileToMsisdn($request_data['mobile_number']);

        //  If the verificaiton code was provided
        if( !empty($verification_code) ){

            $accountExistsInfo = $this->accountExists($request, true, false);
            $account_exists = $accountExistsInfo['account_exists'];
            $user = $accountExistsInfo['user'];

            //  If the user account exists
            if( $account_exists == true ){

                $requires_mobile_number_verification = $user['requires_mobile_number_verification'];
                $requires_password = $user['requires_password'];

                //  If this account has not been verified before
                if( $requires_mobile_number_verification == true ){

                    /**
                     *  If the user account exists and the user did not verify this account, then
                     *  we need to ensure that the provided verification code matches that of an
                     *  account ownership check.
                     */
                    $request_data['mobile_number_verified_at'] = (new \App\MobileVerification())->verifyMobileVerificationCode($mobile_number, $verification_code, 'account_ownership')['mobile_number_verified_at'];

                }

                //  If this account required a new password
                if( $requires_password == true ){

                    //  If this account has been verified before
                    if( $requires_mobile_number_verification == false ){

                        /**
                         *  If the user account exists and the user did verify this account but does
                         *  require a password reset, then we need to ensure that the provided
                         *  verification code matches that of a password reset check.
                         */
                        $request_data['mobile_number_verified_at'] = (new \App\MobileVerification())->verifyMobileVerificationCode($mobile_number, $verification_code, 'password_reset')['mobile_number_verified_at'];

                    }

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

            /**
             *  If the user account does not exist and its not required, then proceed to
             *  verify the account mobile number of the user. This is step is usually
             *  necessacy for moments when the user is creating a user account for
             *  the first time and would like to register and verify the account
             *  ownership at the same time.
             */
            }elseif( $must_have_account == false ){

                $request_data['mobile_number_verified_at'] = (new \App\MobileVerification())->verifyMobileVerificationCode($mobile_number, $verification_code, 'account_ownership')['mobile_number_verified_at'];

                $request_data['password'] = bcrypt($request_data['password']);

            }

            //  If we must have a user account but we don't have one
            if($must_have_account == true && $account_exists == false){

                if( !empty($mobile_number) ){

                    //  The account with the given mobile number does not exist. Throw a validation error
                    throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number "'.$mobile_number.'" does not exist']);

                }

            }

        }

        return [
            'user' => null,
            'requires_update' => false,
            'request_data' => $request_data
        ];
    }

}
