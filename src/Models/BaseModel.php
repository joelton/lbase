<?php
namespace Lfalmeida\Lbase\Models;

use Sofa\Eloquence\Contracts\CleansAttributes;
use Sofa\Eloquence\Contracts\Validable as ValidableContract;
use Sofa\Eloquence\Model;
use Sofa\Eloquence\Validable;

/**
 * Class BaseModel
 *
 * Base para todos os models da aplicação
 *
 * @package App\Models
 *
 */
class BaseModel extends Model
    implements ValidableContract, CleansAttributes
{


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
