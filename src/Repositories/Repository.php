<?php

namespace Lfalmeida\Lbase\Repositories;

use App\Exceptions\ApiException;
use App\Exceptions\ValidationException;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Lfalmeida\Lbase\Contracts\RepositoryInterface;
use Lfalmeida\Lbase\Exceptions\RepositoryException;

/**
 * Class Repository
 *
 * @package Lfalmeida\Lbase\Repositories
 */
abstract class Repository implements RepositoryInterface
{

    /**
     *
     * @var Model $model
     */
    protected $model;
    /**
     * @var
     */
    protected $relationships;
    /**
     * @var string
     */
    protected $defaultOrderColumn = '';
    /**
     * @var string
     */
    protected $defaultOrderDirection = 'asc';
    /**
     * @var App
     */
    private $app;

    /**
     * Repository constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->model = $model->with($this->withRelationShips());

        return $model;
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * Método de acesso utilizado para obter a lista de relações
     * que deve ser construida ao instanciar um model
     *
     * @return mixed
     */
    protected function withRelationShips()
    {
        return $this->relationships;
    }

    /**
     * @param mixed $relationships
     *
     * @return Repository
     */
    public function setRelationships(array $relationships = [])
    {
        $this->relationships = $relationships;
        return $this;
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        if (!empty($this->defaultOrderColumn)) {
            return $this->model->orderBy($this->defaultOrderColumn, $this->defaultOrderDirection)->get($columns);
        }
        return $this->model->get($columns);
    }

    /**
     * @return mixed
     */
    public function countAll()
    {
        return $this->model->count();
    }


    /**
     * @param int    $perPage
     * @param array  $columns
     * @param string $sort
     * @param string $order
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*'], $sort = '', $order = 'asc')
    {
        $m = $this->model;

        if($sort) {
            $m->orderBy($sort, $order);
        } else if($this->defaultOrderColumn) {
            $m->orderBy($this->defaultOrderColumn, $order);
        }
        return $m->paginate($perPage, $columns);
    }


    /**
     * @param array $data
     *
     * @return mixed
     * @throws ApiException
     * @throws ValidationException
     */
    public function create(array $data)
    {
        $model = $this->app->make($this->model());
        $model->fill($data);

        $wasSaved = $model->save();

        if ($wasSaved) {
            return $this->find($model->id);
        }

        $errorMessage = "Não foi possível salvar.";
        
        if (method_exists($model, 'isValid')) {
            $exception = new ValidationException();
            $exception->setMessages($model->getValidationErrors()->all());

            throw $exception;
        }
        throw new ApiException($errorMessage);

    }

    /**
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param        $id
     * @param array  $data
     * @param string $attribute
     *
     * @return mixed
     * @throws RepositoryException
     */
    public function update($id, array $data, $attribute = "id")
    {
        $model = $this->find($id);

        if (!$model) {
            throw new RepositoryException("O item não solicitado não existe.");
        }

        $model->fill($data);

        $model->update();

        return $this->find($id);

    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $model = $this->find($id);

        if (!$model) {
            throw new RepositoryException("O item não solicitado não existe.");
        }

        return $model->delete();
    }

    /**
     * @param       $attribute
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * @param array $params
     *
     * @return mixed
     * @internal param int $perPage
     */
    public function search(array $params)
    {
        $perPage = isset($params['pageSize']) ? $params['pageSize'] : 15;
        $m = $this->model->search($params['search']);
        $m->paginate($perPage);
        return $m;
    }

}