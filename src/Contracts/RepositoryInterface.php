<?php

namespace Lfalmeida\Lbase\Contracts;

/**
 * Interface RepositoryInterface
 *
 * Aqui definimos os métodos comuns que serão utilizados nos repositórios.
 *
 * @package Lfalmeida\Lbase\Contracts
 */
interface RepositoryInterface
{
    /**
     * Retorna todos os registros da entidade
     *
     * @param array $columns Array com as colunas desejadas no retorno, por padrão, são retornadas todas
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * Retorna os registros paginados
     *
     * @param int   $perPage Define a quantidade de registros por página
     * @param array $columns Array com as colunas desejadas no retorno, por padrão, são retornadas todas
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*']);

    /**
     * Persiste um registro
     *
     * @param array $data Array associativo com as colunas e valores a serem salvos
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * Atualiza um registro
     *
     * @param integer $id   Id da entidade
     * @param array   $data Array associativo com as colunas e valores a serem atualizados
     *
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Remove um registro
     *
     * @param integer $id Id da entidade
     *
     * @return mixed
     */
    public function delete($id);

    /**
     * Busca uma entidade utilizando o ID
     *
     * @param integer $id      Id da entidade
     * @param array   $columns $data Array associativo com as colunas e valores a serem atualizados
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * Busca entidades utilizando uma coluna determinada
     *
     * @param string         $field   Nome da coluna onde será realizada a busca
     * @param string|integer $value   Valor a ser buscadp
     * @param array          $columns Array com as colunas desejadas no retorno, por padrão, são retornadas todas
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*']);
}