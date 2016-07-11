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
 * O objetivo desta classe é servir como base para os repositórios dos sistema utilizando Eloquent.
 *
 * @package Lfalmeida\Lbase\Repositories
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * Armazena a instância do Model que será gerenciado por este repositório
     *
     * @var Model $model
     */
    protected $model;

    /**
     * Array com lista de relacionamentos deste model.
     *
     * @var array $relationships ;
     */
    protected $relationships;

    /**
     * Define qual coluna será usado para padrão de ordenação.
     *
     * @var string $defaultOrderColumn
     */
    protected $defaultOrderColumn = '';

    /**
     * Define a ordenação padrão para exibição resultados.
     *
     * @var string $defaultOrderDirection
     */
    protected $defaultOrderDirection = 'asc';

    /**
     * Instância do Container do Laravel
     *
     * @var App $app
     */
    private $app;

    /**
     * Repository constructor.
     *
     * No construtor, recebemos uma instância do Container e atrelamos a este objeto para utilização nos
     * demais métodos.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Retorna uma instância do Model gerenciado por este repositório
     *
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
     * Este é um método de configuração, que retorna uma string com o nome incluindo o namespace do Model que este
     * repositório irá gerenciar.
     *
     * Como é um método abstrato, forçamos todas as classes que derivarem desta classe, implementarem este método,
     * onde cada classe definirá poderá indicar o seu próprio Model.
     *
     * @return string
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
     * Método setter para a propriedade $relationships
     *
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
     * Retorna todos os registros deste Model.
     *
     * @param array $columns Colunas desejadas no retorno.
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
     * Retorna o total de Models cadastrados.
     *
     * @return mixed
     */
    public function countAll()
    {
        return $this->model->count();
    }


    /**
     * Retorna resultados paginados.
     *
     * @param int    $perPage Quantidade de registros por página.
     * @param array  $columns Colunas desejadas no retorno.
     * @param string $sort    Coluna para ordenação.
     * @param string $order   Direção da ordenação.
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*'], $sort = '', $order = 'asc')
    {
        $m = $this->model;

        if ($sort) {
            $m->orderBy($sort, $order);
        } else {
            if ($this->defaultOrderColumn) {
                $m->orderBy($this->defaultOrderColumn, $order);
            }
        }
        return $m->paginate($perPage, $columns);
    }


    /**
     * Cria um registro
     *
     * @param array $data Colunas e valores para serem salvos
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
     * Encontra um Model através do Id
     *
     * @param integer $id      Id do Model
     * @param array   $columns Colunas desejadas no retorno
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Atualiza um model de acordo com id fornecido e as propriedades informadas
     *
     * @param integer $id
     * @param array   $data Array mapeando as colunas e valores a serem atualizados
     *
     * @return mixed
     * @throws RepositoryException
     */
    public function update($id, array $data)
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
     * Remove um registro através do id fornecido
     *
     * @param integer $id Id do Model a ser removido
     *
     * @return boolean
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
     * Encontra registros através dos dados fornecidos.
     *
     * @param string         $attribute Coluna para a busca
     * @param string|integer $value     Valor a ser buscado
     * @param array          $columns   Colunas desejadas no retorno
     *
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * Realiza uma busca utilizando os parâmetros fornecidos.
     *
     * Os parâmetros analisados no array $params são **search e pageSize**.
     *
     * - **search**: string com o termo da busca
     * - **pageSize**: integer Resutados por página
     *
     * @param array $params Array com parâmetros para realização da busca
     *
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