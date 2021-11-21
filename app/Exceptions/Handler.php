<?php

namespace App\Exceptions;


use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    public function render($request, Throwable $e)
    {
        $code = ((int)$e->getStatusCode() > 599 || (int)$e->getStatusCode() < 100) ? 400 : (int)$e->getStatusCode();

        Log::error('Exception : ' . $e->getMessage() . ' | Code => ' . $e->getStatusCode() . ' | Uri => ' . $request->getRequestUri());

        if ($e instanceof ValidationException)
            return api()->validation(trans('responses.validation-error'), $e->errors());

        if ($this->isHttpException($e)) {
            switch ($code) {
                case '401':
                    return api()->error(null, null, 401, [
                        'subject' => $e->getMessage() ?? trans('responses.login-again')
                    ]);
                    break;
                case '404':
                    return api()->notFound();
                    break;
                case '500':
                    return api()->error(null, null, 500, [
                        'subject' => trans('responses.something-went-wrong')
                    ]);
                    break;

                default:
                    return api()->error(null, null, $code, [
                        'subject' => $e->getMessage()
                    ]);
                    break;

            }
        }

        return api()->error(null, null, $code, [
            'subject' => $e->getMessage()
        ]);

    }
}
