<?php
namespace Lfalmeida\Lbase\Models;

use Sofa\Eloquence\Contracts\CleansAttributes;
use Sofa\Eloquence\Contracts\Validable as ValidableContract;
use Sofa\Eloquence\Model;
use Sofa\Eloquence\Validable;

/**
 * Class BaseModel
 *
 * O objetivo desta classe é servir como ponto de partida para os models do sistema.
 *
 * Aqui, implementamos as interfaces [ValidableContract](https://github.com/jarektkaczyk/eloquence/wiki/Validable),
 * e [CleansAttributes](https://github.com/jarektkaczyk/eloquence)
 *
 * Para o nome das propriedades e métodos, será utilizado **lowerCamelCase**
 *
 * [CODING STYLES](http://www.php-fig.org)
 *
 * @package Lfalmeida\Lbase\Models
 */
abstract class BaseModel extends Model implements ValidableContract, CleansAttributes
{

    /**
     * @var bool
     */
    public static $snakeAttributes = false;

    /**
     * Columas pesquisáveis via Eloquence trait
     *
     * @var array
     */
    protected $searchableColumns = ['name'];

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
        return parent::setAttribute($key, trim($value));
    }
}
