<?php

    //  Handle unauthenticated requests
    function help_unauthenticated()
    {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    //  Handle unauthorized requests
    function help_not_authorized()
    {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    //  Handle model not found requests
    function help_model_not_fonud($e)
    {
        return response()->json(['message' => 'Model '.str_replace('App\\', '', $e->getModel()).' not found'], 404);
    }

    //  Handle model relationships not found requests
    function help_model_relationship_not_fonud()
    {
        return response()->json(['message' => 'Model relationship not found'], 404);
    }

    //  Handle resource not found requests
    function help_resource_not_fonud()
    {
        return response()->json(['message' => 'Resource not found'], 404);
    }

    //  Handle route not found requests
    function help_route_not_fonud()
    {
        return response()->json(['message' => 'Route not found. Make sure you are using the correct url'], 404);
    }

    //  Handle too many login attempts requests
    function help_to_many_login_attempts()
    {
        return response()->json(['message' => 'Too many login attempts. Please wait some time and try again'], 404);
    }

    //  Handle method not allowed requests
    function help_method_not_allowed()
    {
        return response()->json(['message' => 'Method not allowed'], 405);
    }

    //  Handle 422 errors (validation errors)
    function help_validation_error($e)
    {
        return response()->json([
            'message' => $e->getMessage() ?? null,
            'errors' => $e->errors() ?? null,
        ], 422);
    }

    //  Handle general errors
    function help_general_error($e)
    {
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }

    //  Handle 500 errors
    function help_bad_method_call($e)
    {
        return response()->json(['error' => $e->getMessage()], 500);
    }

    //  Handle the given exception
    function help_handle_exception($e)
    {
        switch (true) {
            /*  Error handle if the model data does not exist. Helpful for error handling especially for
             *  Route-Model binding scenerios e.g create(Company $company){} but the resource is not found
             */
            case $e instanceof ModelNotFoundException:
                return help_model_not_fonud($e);
                break;

            /*  Error handle if the route does not exist
             */
            case $e instanceof NotFoundHttpException:
                return help_route_not_fonud();
                break;

            /*  Error handle if the request method is not supported
             */
            case $e instanceof MethodNotAllowedHttpException:
                return help_method_not_allowed();
                break;

            /*  Error handle if the resource relationship is not found
             *  e.g) when we use Model::with($relationship) and its not found
             */
            case $e instanceof RelationNotFoundException:
                return help_model_relationship_not_fonud();
                break;

            /*  Error handle too many requests have been made by a client on the api
             */
            case $e instanceof ThrottleRequestsException:
                return help_to_many_login_attempts();
                break;

            /*  Error handle for status 500
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
