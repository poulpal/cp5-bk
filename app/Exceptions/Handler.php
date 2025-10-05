<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'otp'
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // $this->renderable(function (Throwable $e, $request) {
        //     return response()->json([
        //         'success' => false,
        //         // 'message' => $e->getMessage()
        //     ], 500);
        // });
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated.'
            ], 401);
        }

        return redirect()->guest('login');
    }

    public function render($request, Throwable $exception)
    {

        if ($exception instanceof ValidationException && $request->expectsJson()){
            return response()->json([
                'success' => false,
                'errors' => [
                    'otp' => 'تعداد درخواست های شما بیش از حد مجاز است'
                ]
            ], 400);
        }

        if ($exception instanceof ModelNotFoundException && $request->getPathInfo() != '/404')
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found'
                ], 404);
            }
            return redirect('/');
        }

        if ($exception instanceof NotFoundHttpException && $request->getPathInfo() != '/404')
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found'
                ], 404);
            }
            return redirect('/');
        }


        return parent::render($request, $exception);
    }
}
