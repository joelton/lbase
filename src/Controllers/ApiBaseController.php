<?php
namespace Lfalmeida\Lbase\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Lfalmeida\Lbase\Contracts\RepositoryInterface as Repository;


/**
 * Class ApiBaseController
 *
 * Esta Classe tem o objetivo de ser o ponto de partida para criação de controllers de api.
 *
 * @package Lfalmeida\Lbase\Controllers
 */
abstract class ApiBaseController extends Controller
{
    /**
     * Instância do repositório que será utilizado para a entidade gerenciada por este
     * controller
     *
     * @var \Lfalmeida\Lbase\Contracts\RepositoryInterface Repository
     */
    protected $repository;

    /**
     * ApiBaseController constructor.
     *
     * Neste método atribuímos a instância concreta do repositório atrelado ao controller
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Recebe o request e retorna uma lista das entidades com paginação, por padrão.
     *
     * Este método é o ponto de entrada para alguns outros métodos deste controller, que são direcionados
     * de acordo com parâmetros presentes ou não no request.
     *
     * As chaves possíveis no request são: **disablePagination, search e count**.
     *
     * Caso estas chaves sejam encontradas no request, apresentarão os seguintes comportamentos:
     *
     * - **disablePagination**: Mostra uma lista completa, sem paginação, útil no caso de solicitações para
     * preenchimento de dropdowns.
     *
     * - **search**: Indica a execução de uma busca, sendo assim direcionamos para o método de busca neste mesmo
     * controller.
     *
     * - **count**: Retorna apenas o total de resultados encontrados.
     *
     * - **fields**: String com as colunas desejadas no retorno, separadas por vírgula.
     *
     * Caso esteja presente o parâmetro **search**, os seguintes parâmetros também serão verificados:
     *
     * - **pageSise**: Inteiro indicando a quantidade de resultados por página.
     *
     * - **sort**: Coluna para ordenação
     *
     * - **order**: Direção da ordenação
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->input('disablePagination')) {
            if ($request->input('search')) {
                return $this->search($request);
            }
            return $this->listAll($request);
        }

        if ($request->input('search')) {
            return $this->search($request);
        }

        if ($request->input('count')) {
            return $this->countAll();
        }

        return $this->paginate($request);
    }

    /**
     * Realiza uma busca utilizando o repositório, repassando os parâmetros do request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function search(Request $request)
    {
        $allResults = $this->repository->search($request->all());
        return Response::apiResponse([
            'data' => $allResults
        ]);
    }

    /**
     * Lista todos os registros, sem paginação.
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function listAll(Request $request)
    {
        $fields = $request->input('fields') ? explode(',', $request->input('fields')) : ['*'];

        $allResults = $this->repository->all($fields);

        return Response::apiResponse([
            'data' => $allResults
        ]);
    }

    /**
     * Retorna o total de itens cadastrados para esta entidade
     *
     * @return mixed
     */
    protected function countAll()
    {
        $allResults = $this->repository->countAll();
        return Response::apiResponse([
            'data' => $allResults
        ]);
    }

    /**
     * Retorna os resultados paginados.
     *
     * Este método aceita os seguintes parâmetros fornecidos via Request: **fields, pageSize, sort, order**.
     *
     * - **fields**: String com as colunas desejadas no retorno, separadas por vírgula.
     * - **pageSise**: Inteiro indicando a quantidade de resultados por página.
     * - **sort**: Coluna para ordenação
     * - **order**: Direção da ordenação
     *
     * @internal Este método não é exposto via url, sendo acessado via método index
     *
     * @param Request $request
     */
    protected function paginate(Request $request)
    {
        $fields = $request->input('fields') ? explode(',', $request->input('fields')) : ['*'];
        $pageSize = $request->input('pageSize') ? (int)$request->input('pageSize') : null;
        $sort = $request->input('sort') ? $request->input('sort') : null;
        $order = $request->input('order') ? $request->input('order') : 'asc';

        $pagination = $this->repository->paginate($pageSize, $fields, $sort, $order);

        $pagination->appends($request->except(['page']));

        return Response::apiResponse([
            'data' => $pagination
        ]);
    }

    /**
     * Salva uma entidade utilizando o repositório.
     *
     * Os parâmentros recebidos no request são repassados para o repositório.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = $this->repository->create($request->all());
        return Response::apiResponse([
            'data' => $response
        ]);
    }

    /**
     * Retorna uma entidade recuperada através do seu id.
     *
     * Este método aceita os seguintes parâmetros fornecidos via Request: **fields**.
     *
     * - **fields**: String com as colunas desejadas no retorno, separadas por vírgula.
     *
     * @param  int    $id Id da entidade.
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $fields = $request->input('fields') ? explode(',', $request->input('fields')) : ['*'];

        $resource = $this->repository->find($id, $fields);

        if (empty($resource)) {
            return Response::apiResponse([
                'httpCode' => 404,
                'message' => 'Item não encontrado.'
            ]);
        }

        return Response::apiResponse([
            'data' => $resource
        ]);
    }


    /**
     * Atualiza os dados de uma entidade com base nos dados obtidos no Request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id Id da entidade a ser atualizada
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $response = $this->repository->update($id, $request->all());
        return Response::apiResponse([
            'data' => $response
        ]);

    }

    /**
     * Remove uma entidade específica.
     *
     * @param  int $id Id da entidade que deve ser removida
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $wasSuccessful = $this->repository->delete($id);
            return Response::apiResponse([
                'data' => $wasSuccessful
            ]);
        } catch (\Exception $e) {
            return Response::apiResponse([
                'httpCode' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

}