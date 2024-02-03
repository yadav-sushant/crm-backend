<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use BadMethodCallException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(
            function (NotFoundHttpException $e) {
                return response()->json(['status'=>false,'statusCode'=>404,'error'=>['code'=>404,'title'=>'NOT_FOUND','description'=>'Page not found.'],'response'=>[]],404);
            }
        );
        $this->renderable(
            function (RouteNotFoundException $e) {
                return response()->json(['status'=>false,'statusCode'=>401,'error'=>['code'=>401,'title'=>'UNAUTHORISED','description'=>'You are not authorized to access the page requested.'],'response'=>[]],401);
            }
        );
        $this->renderable(
            function (BadMethodCallException $e) {
                return response()->json(['status'=>false,'statusCode'=>502,'error'=>['code'=>502,'title'=>'BAD_METHOD_CALL','description'=>'Method calling not found.'],'response'=>[]],502);
            }
        );
        $this->renderable(
            function (MethodNotAllowedHttpException $e) {
                return response()->json(['status'=>false,'statusCode'=>405,'error'=>['code'=>405,'title'=>'METHOD_NOT_ALLOWED','description'=>'The method you\'re trying to use is not supported for this route.'],'response'=>[]],405);
            }
        );
        $this->renderable(
            function (RuntimeException $e) {
                return response()->json(['status'=>false,'statusCode'=>500,'error'=>['code'=>500,'title'=>'RUNTIME_EXCEPTION','description'=>$e->getMessage()],'response'=>[]],500);
            }
        );
        $this->renderable(
            function (BindingResolutionException $e) {
                return response()->json(['status'=>false,'statusCode'=>500,'error'=>['code'=>500,'title'=>'RUNTIME_EXCEPTION','description'=>'Target class does not exist.'],'response'=>[]],500);
            }
        );

    }
}
