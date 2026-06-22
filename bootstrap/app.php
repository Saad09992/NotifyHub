<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        
        $exceptions->render(function (NotFoundHttpException $e, Request $request){
            if(!$request->is('api/*')){
                return null;
            }
            if ($e->getPrevious() instanceof ModelNotFoundException){
                return response()->json([
                    'error'=>'not_found',
                    'message'=>'resource not found',
                ],404);
            }

            return response()->json([
                'error'=>'route_not_found',
                'message'=>'Endpoint not found',
            ],404);
        });
        
        $exceptions->render(function (AuthenticationException $e, Request $request){
            if(!$request->is('api/*')){
                return null;
            }
            return response()->json([
                'error'=>'unauthenticated',
                'message'=>'Authentication required'
            ],401);
        });
    })->create();
