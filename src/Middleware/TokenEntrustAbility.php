<?php

namespace Lfalmeida\Lbase\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Middleware\BaseMiddleware;

/**
 * Class TokenEntrustAbility
 *
 * Filta os requests permitindo o acesso apenas para usuários autenticados
 * via token que possuam uma combinação de Role e Permission.
 *
 * @package Lfalmeida\Lbase\Middleware
 */
class TokenEntrustAbility extends BaseMiddleware
{
    /**
     * @param         $request
     * @param Closure $next
     * @param         $roles
     * @param         $permissions
     * @param bool    $validateAll
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $permissions, $validateAll = false)
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

        if (!$request->user()->ability(explode('|', $roles), explode('|', $permissions),
            ['validate_all' => $validateAll])
        ) {
            return Response::apiResponse([
                'httpCode' => 401,
                'message' => 'Acesso não autorizado.'
            ]);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }
}