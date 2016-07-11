<?php
namespace Lfalmeida\Lbase\Models;

use Illuminate\Support\Facades\Config;
use Sofa\Eloquence\Contracts\Validable as ValidableContract;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Validable;
use Zizaco\Entrust\EntrustRole;

/**
 * Class Role
 *
 * Model de roles
 *
 * @package Lfalmeida\Lbase\Models
 */
class Role extends EntrustRole implements ValidableContract
{

    use Eloquence, Validable;

    /**
     * Regras de validação deste model
     *
     * @see https://github.com/jarektkaczyk/eloquence/wiki/Validable
     * @var array
     */
    protected static $businessRules = [
        'name' => ['required', 'unique:roles']
    ];
    protected $searchableColumns = ['name', 'display_name', 'description'];
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
        'displayName',
        'description',
    ];

    /**
     * Este método sobrescreve o da superclasse adaptando para o Laravel 5.2
     *
     * O problema que este método resolve é a sintaxe do acesso a propriedade da configuração que obtem o model de
     * usuários, caso este problema seja corrigido pelo vendor, podemos excluir este método.
     *
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('auth.providers.users.model'), Config::get('entrust.role_user_table'));
    }

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