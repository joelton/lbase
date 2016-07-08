<?php


namespace Lfalmeida\Lbase\Tests;

use Lfalmeida\Lbase\Utils\ReportsBase;

/**
 * Class BaseExampleTest
 * @package App\Lfalmeida\Lbase\Tests
 */
class BaseExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teste de exemplo
     *
     */
    public function testInstanceOfRepostsBase()
    {

        $repostsBase = new ReportsBase();

        $repostsBase->getPdfReport('http://guardamirim.app/reports?type=RecruitsReport&name=fullList&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0NjI3MjAzNDQsImluZiI6eyJ1aWQiOjEsImFjbCI6IjAiLCJlbnQiOiJFbXBsb3llZSJ9fQ.r0M86L0dXrl6h9dmnArTqDVvN1vJo1TSLWlhQOjHXRk');
    }
}