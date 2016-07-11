<?php

namespace Lfalmeida\Lbase\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Middleware\BaseMiddleware;

/**
 * Class TokenEntrustRole
 *
 * @package Lfalmeida\Lbase\Middleware
 */
class TokenEntrustRole extends BaseMiddleware
{
    /**
     * @param         $request
     * @param Closure $next
     * @param         $role
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
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
                'message' => 'O token expirou.'
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

        if (!$user->hasRole(explode('|', $role))) {
            return Response::apiResponse(['httpCode' => 401, 'message' => 'Acesso não Autorizado.']);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }
}
