<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use App\Mail\SendPasswordResetLink;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
            *  and password.
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

            //  Validate "Login Via USSD"
            $validator = Validator::make($request->all(), [
                /* Validation Rules
                 *
                 *  # If we want to login via USSD then make sure that the user provided their mobile number.
                 *  # If we want to login via WEB then make sure that the user provided their email and password
                 *
                 */
                'login_via_ussd' => 'sometimes|required|accepted',
                'mobile_number' => 'required_if:login_via_ussd,true|required_if:login_via_ussd,1|regex:/^[0-9]+$/i',
                'password' => 'required_without:login_via_ussd|required_if:login_via_ussd,false|required_if:login_via_ussd,0',
                'email' => 'required_without:login_via_ussd|required_if:login_via_ussd,false|required_if:login_via_ussd,0|email',
            ], [
                //  Login Via USSD Validation Error Messages
                'login_via_ussd.required' => 'The login_via_ussd attribute must be set equal to "true" or "1" to implement login via USSD',
                'login_via_ussd.accepted' => 'The login_via_ussd attribute must be set equal to "true" or "1" to implement login via USSD',

                //  Mobile Number Validation Error Messages
                'mobile_number.required_if' => 'The mobile_number attribute is required since you are logging in via USSD',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567',

                //  Email Validation Error Messages
                'email.email' => 'Enter a valid email address to login e.g email@example.com',
                'email.required_without' => 'Enter your email to login e.g email@example.com',
                'email.required_if' => 'Enter your email to login e.g email@example.com',
                'email.required' => 'Enter your email to login e.g email@example.com',

                //  Password Validation Error Messages
                'password.required_without' => 'Enter your password to login',
                'password.required_if' => 'Enter your password to login',
                'password.required' => 'Enter your password to login',
            ]);

            //  If the validation failed
            if ($validator->fails()) {
                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());
            }

            //  If we want to login via USSD
            if (in_array($login_via_ussd, [true, 1, 'true', '1'])) {
                //  Get the Mobile Number
                $mobile_number = $request->input('mobile_number');

                /** IMPORTANT SECURITY NOTICE
                 *
                 *  This is where we can check if the API GRANT TOKEN has been provided
                 *  and whether or not the GRANT TOKEN is valid. If it is, we can then
                 *  attempt to login the user that matches the given mobile number
                 *  otherwise we must return a failed login indicating that the
                 *  GRANT TOKEN provided is invalid.
                 */

                //  Get the user that owns the given mobile number
                $user = DB::table('users')->where('mobile_number', $mobile_number)->first();

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
                //  Get the login credentials
                $credentials = $request->only('email', 'password');

                //  Attempt to login
                if (auth()->attempt($credentials)) {
                    //  Create new access token
                    return $this->createNewAccessToken();

                //  If our attempt to login failed
                } else {
                    //  Get the user's email address
                    $email = $request->input('email');

                    //  Check if a user with the given email address exists
                    if (DB::table('users')->where('email', $email)->exists()) {
                        //  Since the email exists, this means that the password is incorrent. Throw a validation error
                        throw ValidationException::withMessages(['password' => 'Your password is incorrect']);
                    } else {
                        //  The account with the given email does not exist. Throw a validation error
                        throw ValidationException::withMessages(['email' => 'The account using the email "'.$email.'" does not exist.']);
                    }
                }
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

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:25',
                'last_name' => 'required|max:25',
                'email' => 'sometimes|required|email|unique:users,email',
                'mobile_number' => 'sometimes|required|unique:users,mobile_number',
                'password' => 'sometimes|required|confirmed',
            ]);

            //  If the validation failed
            if ($validator->fails()) {
                //  Throw a validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());
            } else {
                // Retrieve the validated input data
                $registration_data = $request->all();
            }

            //  If the password was provided
            if (isset($registration_data['password'])) {
                //  Hash the password using bcrypt
                $registration_data['password'] = bcrypt($registration_data['password']);
            }

            //  Create new user
            $user = \App\User::create($registration_data);

            //  Create new access token
            $accessToken = $user->createToken('authToken');

            //  Return response
            return response([
                'user' => $user,
                'access_token' => $accessToken,
            ]);
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
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
            $user = \App\User::where('email', $email)->first();

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
            if (\App\User::where('email', $email)->exists()) {
                //  Check if a user has a valid token
                if (DB::table('password_resets')->where('email', $email)->where('token', $token)->exists()) {
                    //  Get the user
                    $user = \App\User::where('email', $email)->first();

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
