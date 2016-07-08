<?php

namespace Lfalmeida\Lbase\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Middleware\BaseMiddleware;

class TokenEntrustPermission extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {

        $token = JWTAuth::getToken();

        if (!$token) {
            return Response::apiResponse([
                'httpCode' => 400,
                'message' => 'Token não encontrado ou inválido.'
            ]);
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return Response::apiResponse([
                'httpCode' => 400,
                'message' => 'O token de acesso expirou.'
            ]);
        } catch (JWTException $e) {
            return Response::apiResponse([
                'httpCode' => 400,
                'message' => 'Token inválido.'
            ]);
        }

        if (!$user) {
            return Response::apiResponse([
                'httpCode' => 404,
                'message' => 'Usuário não encontrado.'
            ]);
        }

        if (!$user->hasRole(explode('|', $permission))) {
            return Response::apiResponse([
                'httpCode' => 401,
                'message' => 'Acesso não autorizado.'
            ]);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }
}
