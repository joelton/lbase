<?php
namespace Lfalmeida\Lbase\Utils;

use App\Exceptions\ApiException;
use Firebase\JWT\JWT;

/**
 * Class TokenHandler
 * @package Lfalmeida\Lbase\Utils
 */
class TokenHandler
{
    const ENTITIES_PREFIX = 'Modules\GuardaMirim\Models';

    /**
     * Verifica se um token é valido no sistema
     * @param $request
     * @return mixed
     * @throws ApiException
     *
     */
    public static function check($request)
    {
        $token = self::getRequestToken($request);
        $tokenDecoded = self::decode($token);

        $entityName = sprintf('%s\%s', self::ENTITIES_PREFIX, $tokenDecoded->inf->ent);

        $entity = new $entityName();

        $user = $entity::where(['id' => $tokenDecoded->inf->uid])->first();
        return $user ? true : false;
    }

    /**
     * @param $token
     * @return object
     * @throws ApiException
     */
    public static function decode($token)
    {
        try {
            $key = env('APP_KEY');
            return JWT::decode($token, $key, array('HS256'));
        } catch (\Exception $e) {
            throw new ApiException("Token inválido: " . $e->getMessage());
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public static function getRequestToken($request)
    {
        // return access token
        if ($request->header('Authorization')) {

            if (strpos($request->header('Authorization'), 'Bearer') !== false) {
                list($accessToken) = sscanf($request->header('Authorization'), 'Bearer %s');
                return $accessToken;
            }

            return $request->header('Authorization');

        } else if ($request->input('token')) {
            return $accessToken = $request->input('token');
        }

        return false;
    }


    /**
     * @param array $data
     * @return string
     */
    public static function create(array $data)
    {
        /*
         * Create the token as an array
         */
        $tokenData = [
            'iat' => time(),
            'inf' => $data
        ];

        return JWT::encode($tokenData, env('APP_KEY'));
    }

    /**
     * @param $token
     * @return bool
     * @throws ApiException
     */
    public static function extractUID($token)
    {
        $decodedToken = self::decode($token);

        if ($decodedToken) {
            return $decodedToken->inf->uid;
        }
        return false;
    }

}