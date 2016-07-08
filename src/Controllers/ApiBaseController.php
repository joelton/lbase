<?php
namespace Lfalmeida\Lbase\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lfalmeida\Lbase\Contracts\RepositoryInterface as Repository;
use Response;

/**
 * Class ApiBaseController
 *
 * @package Lfalmeida\Lbase\Controllers
 */
abstract class ApiBaseController extends Controller
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * ApiBaseController constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
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
     * Display all resources without pagination
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
     * Returns the total of items
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
     * Display paginated api resources
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     *
     * @param  int    $id
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
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
     * Remove the specified resource from storage.
     *
     * @param  int $id
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