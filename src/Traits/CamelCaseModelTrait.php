<?php
namespace Lfalmeida\Lbase\Traits;

/**
 * Traduz nomes das propriedades de snake case para camel case
 *
 *  Mantem a consistência e convenção de nomes de propriedades
 * e colunas do banco de dados
 *
 *
 */
trait CamelCaseModelTrait
{
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