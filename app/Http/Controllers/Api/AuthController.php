<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use App\Mail\SendPasswordResetLink;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
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

            if ($login_via_ussd === true || $login_via_ussd === 'true') {
                /* If the user provided a mobile number via USSD then
                *  we can get the user using this mobile number.
                */
                if ($mobile_number = $request->input('mobile_number')) {
                    //  Get the user that owns the given mobile number
                    $user = \App\User::where('mobile_number', $mobile_number)->first();

                    //  Login the given user
                    auth()->login($user);

                    //  If we are logged in
                    if (auth()->user()) {
                        //  Create new access token
                        return $this->createNewAccessToken();
                    } else {
                        //  Failed to login. Throw a validation error
                        throw ValidationException::withMessages(['general' => 'Failed to login user via USSD']);
                    }
                } else {
                    //  The mobile number is not provided. Throw a validation error
                    throw ValidationException::withMessages(['mobile_number' => 'Mobile number required to login via USSD']);
                }

                //  Create new access token
                return $this->createNewAccessToken();
            } else {

                //  Validate the login inputs
                $loginData = $request->validate([
                    'email' => 'email|required',
                    'password' => 'required',
                ]);

                //  Get the user's email
                $email = $loginData['email'];

                //  Attempt to login
                if (auth()->attempt($loginData)) {
                    //  Create new access token
                    return $this->createNewAccessToken();

                //  If attempt to login failed
                } else {
                    //  Check if a user with the given email address exists
                    if (\App\User::where('email', $email)->exists()) {
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
            $accessToken = auth()->user()->createToken('authToken');

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
                'name' => 'required|max:55',
                'email' => 'sometimes|required|email|unique:users,email',
                'mobile_number' => 'sometimes|required|unique:users,mobile_number',
                'password' => 'sometimes|required|confirmed',
            ]);
                
            //  If the validation failed
            if ($validator->fails()) {

                //  Throw a validation errors
                throw ValidationException::withMessages( collect($validator->errors())->toArray() );

            }else{

                // Retrieve the validated input data
                $registration_data = $request->all();

            }
            
            //  If the password was provided
            if( isset($registration_data['password']) ){
                
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

            //  Check if a user with the given email address exists
            if (\App\User::where('email', $email)->exists()) {
                //  Get the user
                $user = \App\User::where('email', $email)->first();

                //  Delete any old password reset tokens
                DB::table('password_resets')->where('email', $email)->delete();

                //  Create a new password reset token
                DB::table('password_resets')->insert([
                    'email' => $email,
                    'token' => Str::random(60),
                ]);

                //  Get the new password reset data
                $password_reset = DB::table('password_resets')->where('email', $email)->first();

                //  Get the new password reset token
                $token = $password_reset->token;

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
