<?php
namespace Lfalmeida\Lbase\Utils;

use ReflectionClass;

/**
 * Class EnumBase
 *
 * Classe utilitária para base de objetos no estilo "Enum", que aceitam apenas determinados valores.
 *
 * @package Lfalmeida\Lbase\Utils
 */
abstract class EnumBase
{
    /**
     * @var null
     */
    private static $constCacheArray = null;

    /**
     * EnumBase constructor.
     * Private contructor to prevent instantiation
     */
    private function __construct()
    {
    }

    /**
     * @param      $name
     * @param bool $strict
     *
     * @return bool
     */
    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    /**
     * @return mixed
     */
    private static function getConstants()
    {

        if (self::$constCacheArray == null) {
            self::$constCacheArray = [];
        }

        $calledClass = get_called_class();

        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$constCacheArray[$calledClass];
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isValidValue($value)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict = true);
    }
}