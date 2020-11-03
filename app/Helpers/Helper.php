<?php

    use Illuminate\Auth\AuthenticationException;
    use Illuminate\Database\Eloquent\ModelNotFoundException;
    use Illuminate\Database\Eloquent\RelationNotFoundException;
    use Illuminate\Validation\ValidationException;
    use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    
    //  Handle failed login request
    function help_login_failed()
    {
        return response()->json(['message' => 'Login failed'], 401);
    }

    //  Handle unauthenticated requests
    function help_unauthenticated($e)
    {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    //  Handle unauthorized requests
    function help_not_authorized($e)
    {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    //  Handle model not found requests
    function help_model_not_fonud($e)
    {
        /* Example Output:

         *  {
         *      "error": "Model User not found",
         *      "message": "No query results for model [App\\User] 1000000",
         *      "details": {}
         *  }
         */
        return exception_response('Model '.str_replace('App\\', '', $e->getModel()).' not found', $e, 404);
    }

    //  Handle model relationships not found requests
    function help_model_relationship_not_fonud($e)
    {
        /* Example Output:
         *
         *  {
         *      "error": "Model relationship not found",
         *      "message": "Call to undefined relationship [coupons] on model [App\\User].",
         *      "details": {
         *          "model": "App\\User",
         *          "relation": "coupons"
         *      }
         *  }
         */
        return exception_response('Model relationship not found', $e, 404);
    }

    //  Handle resource not found requests
    function help_resource_not_fonud($e)
    {
        return exception_response('Resource not found', $e, 404);
    }

    //  Handle route not found requests
    function help_route_not_fonud($e)
    {
        return exception_response('Route not found. Make sure you are using the correct url', $e, 404);
    }

    //  Handle too many login attempts requests
    function help_to_many_login_attempts($e)
    {
        return exception_response('Too many login attempts. Please wait some time and try again', $e, 404);
    }

    //  Handle method not allowed requests
    function help_method_not_allowed($e)
    {
        /* Example Output:
         *
         *  {
         *      "error": "Method not allowed",
         *      "message": "The POST method is not supported for this route. Supported methods: GET, HEAD.",
         *      "details": {}
         *   }
         *
         */
        return exception_response('Method not allowed', $e, 405);
    }

    //  Handle 422 errors (validation errors)
    function help_validation_error($e)
    {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->getMessage(),
            'errors' => $e->errors() ?? [],
        ], 422);
    }

    //  Handle general errors
    function help_general_error($e)
    {
        return exception_response('Server error', $e, 500);
    }

    //  Handle 500 errors
    function help_bad_method_call($e)
    {
        return exception_response('Bad method call', $e, 400);
    }

    function exception_response($error_message, $e, $status)
    {
        return response()->json([
            'error' => $error_message,
            'message' => $e->getMessage(),
            'details' => $e,
        ], $status);
    }

    //  Handle the given exception
    function help_handle_exception($e)
    {
        switch (true) {
            
            /*  Error handle if the user is unauthenticated while trying to access routes that  
             *  require the user to be logged in to proceed.
             */
            case $e instanceof AuthenticationException:
                return help_unauthenticated($e);
                break;

            /*  Error handle if the model data does not exist. Helpful for error handling especially for
             *  Route-Model binding scenerios e.g create(Company $company){} but the resource is not found
             *  or when we implement findOrFail e.g User::findOrFail(1) but an error is thrown if the
             *  database does not have a record of user id = 1.
             */
            case $e instanceof ModelNotFoundException:
                return help_model_not_fonud($e);
                break;

            //  Error handle if the route does not exist when using the GET method
            case $e instanceof NotFoundHttpException:
                return help_route_not_fonud($e);
                break;

            //  Error handle if the request method is not supported e.g POST, PUT or DELETE
            case $e instanceof MethodNotAllowedHttpException:
                return help_method_not_allowed($e);
                break;

            /*  Error handle if the resource relationship is not found
             *  e.g when we use Model::with($relationship) and its not found
             */
            case $e instanceof RelationNotFoundException:
                return help_model_relationship_not_fonud($e);
                break;

            /*  Error handle too many requests have been made by a client on the api
             */
            case $e instanceof ThrottleRequestsException:
                return help_to_many_login_attempts($e);
                break;

            /*  Error handle for status 400
             */
            case $e instanceof BadMethodCallException:
                return help_bad_method_call($e);
                break;

            /*  Error handle for failed validation
             */
            case $e instanceof ValidationException:
                return help_validation_error($e);
                break;

            default:
                return help_general_error($e);
            break;
        }
    }
