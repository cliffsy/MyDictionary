<?php

namespace App\Traits;

use Exception;
use Throwable;

use Illuminate\Http\Response;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


trait TranslatableResponse
{
    /** @brief function for success response with data
     * 
     * 
     * 
     * @param Mixed. $data that needed in a response
     * @return json. response in json format
     */
    public function translateSuccessResponse($message = "", $data = null, $pagination = null)
    {
        $status = "success";
        $code = Response::HTTP_OK;

        if ($message === null || $message === '') {
            if (!$data && is_countable($data) && count($data) <= 0) {
                $message = "No records found";
            } elseif (is_countable($data)) {
                $message = count($data) > 1 ? "records found." : "record found.";
                $message = count($data) . " $message";
            } else {
                $message = "1 record found";
            }
        } else {
            $message = $message;
        }

        $response_array = [
            'status' => $status,
            'statusCode' => $code,
            'statusText' => 'HTTP_OK',
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination
        ];

        if ($pagination) {
            $response_array['pagination'] = $pagination;
        }

        return response()->json(
            $response_array,
            $code
        );
    }

    /** @brief function for success response that shows message only
     * 
     * 
     * 
     * @param String. $message, dispolayed data
     * @return json. response in json format
     */
    public function translateSuccessResponseMessage(string $message)
    {
        $status = "success";
        $code = Response::HTTP_OK;

        return response()->json(
            [
                'status' => $status,
                'statusCode' => $code,
                'statusText' => 'HTTP_OK',
                'message' => $message === "" ? "Success" : $message
            ],
            $code
        );
    }

    /** @brief function that validates exception error and translate it to a common exception response
     * 
     * 
     * 
     * @param Throwable. $e the actual exception object
     * @param Mixed. $data The actual data for the exception, useful in debuging
     * @return Json. response in json format
     */
    public function translateExceptionResponse(Throwable $e, $data = null)
    {

        $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = $e->getMessage();

        if ($e instanceof HttpResponseException) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $message === "" ? "Internal Server Error" : $message;
        } else if ($e instanceof MethodNotAllowedHttpException) {
            $code = Response::HTTP_METHOD_NOT_ALLOWED;
            $message = $message === "" ? "Method Not Allowed" : $message;
        } else if ($e instanceof ModelNotFoundException) {
            $code = Response::HTTP_NOT_FOUND;
            $message = $message === "" ? "Model not found" : $message;
        } else if ($e instanceof NotFoundHttpException) {
            $code = Response::HTTP_NOT_FOUND;
            $message = $message === "" ? "Resource Not Found" : $message;
        } else if ($e instanceof AuthorizationException) {
            $code = Response::HTTP_FORBIDDEN;
            $message = $message === "" ? "Forbidden" : $message;
        } else if ($e instanceof ValidationException && $e->getResponse()) {

            // Get the exact errors of validation and output it as array of errors     
            if ($data == null) {
                $data = $e->getResponse();
                $data = $data->original;
            }

            $code = Response::HTTP_BAD_REQUEST;
            $message = $message === "" ? "Bad Request" : $message;
        } else if ($e) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $message === "" ? $e->getMessage() : $message;
        }

        return $this->translateFailedResponse($code, $message, $data, $e->getLine());
    }

    /** @brief function that converts the failed response to a common response
     * 
     * 
     * 
     * @param Mixed. $code status code of the exception
     * @param String. $message this is the custom meessage for the response
     * @param Mixed.  $data The actual data for the exception, useful in debuging
     * @return Json.  response in json format
     */
    public function translateFailedResponse($code, $message, $data = null, $trace = null)
    {
        $status = "failed";
        return response()->json(
            [
                'status' => $status,
                'statusCode' => $code,
                'statusText' => $code,
                'message' => $message,
                'data' => $data,
                'trace' => $trace
            ],
            $code
        );
    }

    /** @brief function converts status code to text
     * 
     * 
     * 
     * @param Mixed. status code error
     * @return String. status text
     */
    public static function getStatusText($code)
    {

        $statusText = "";
        switch ($code) {
            case Response::HTTP_UNAUTHORIZED:
                $statusText = "HTTP_UNAUTHORIZED";
                break;
            case Response::HTTP_NOT_FOUND:
                $statusText = "HTTP_NOT_FOUND";
                break;
            case Response::HTTP_FORBIDDEN:
                $statusText = "HTTP_FORBIDDEN";
                break;
            case Response::HTTP_METHOD_NOT_ALLOWED:
                $statusText = "HTTP_METHOD_NOT_ALLOWED";
                break;
            case Response::HTTP_BAD_REQUEST:
                $statusText = "HTTP_BAD_REQUEST";
                break;
            case Response::HTTP_INTERNAL_SERVER_ERROR:
                $statusText = "HTTP_INTERNAL_SERVER_ERROR";
                break;
            default:
                $statusText = "HTTP_INTERNAL_SERVER_ERROR";
                break;
        }

        return $statusText;
    }
}
