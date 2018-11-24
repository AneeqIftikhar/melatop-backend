<?php

namespace Melatop\Exceptions;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

use Illuminate\Database\QueryException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $debugEnabled = config('app.debug');
        if($exception instanceof AuthenticationException)
        {
            return response()->token_error('Please Login Again To Get New Token: '.$exception->getMessage());
        }
        else if ($exception instanceof QueryException) {
            if ($debugEnabled) {
                $message = $exception->getMessage();
            } else {
                $message = 'Internal Server Error';
            }

            return response()->fail($message);
        }
        else if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return response()->fail( $exception->errors());

        }
        else if ($exception instanceof FatalThrowableError) {
            if ($debugEnabled) {
                $message = "FatalThrowableError: ".$exception->getMessage();
            } else {
                $message = 'Internal Server Error';
            }
            return response()->fail($message);

        }
        return parent::render($request, $exception);
    }
}
