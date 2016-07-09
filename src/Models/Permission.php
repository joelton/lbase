<?php
namespace Lfalmeida\Lbase\Models;

use Sofa\Eloquence\Contracts\Validable as ValidableContract;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Validable;
use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission implements ValidableContract
{
    use Eloquence, Validable;

    /**
     * Regras de validação deste model
     *
     * @see https://github.com/jarektkaczyk/eloquence/wiki/Validable
     * @var array
     */
    protected static $businessRules = [
        'name' => ['required', 'unique:permissions']
    ];
    /**
     * @var array
     */
    protected $hidden = ['pivot'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'displayName'
    ];

    /**
     * Converte a saída de snake_case para loweCamelCase
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $renamed = [];
        foreach ($array as $key => $value) {
            $renamed[camel_case($key)] = $value;
        }
        return $renamed;
    }

    /**
     * Mantém a compatibilidade com snake_case ao acessar atributos
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        $key = snake_case($key);
        return parent::getAttribute($key);
    }

    /**
     * Mantém a compatibilidade com snake_case ao definir atributos
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this|void
     */
    public function setAttribute($key, $value)
    {
        $key = snake_case($key);
        return parent::setAttribute($key, $value);
    }
}