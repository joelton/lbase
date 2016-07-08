<?php

namespace Lfalmeida\Lbase\Contracts;

/**
 * Interface RepositoryInterface
 *
 * Padronizados aqui os métodos que serão utilizados nos repositórios.
 *
 * @package Lfalmeida\Lbase\Contracts
 */
interface RepositoryInterface
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'));

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = array('*'));

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'));

    /**
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($field, $value, $columns = array('*'));
}