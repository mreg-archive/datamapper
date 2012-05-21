<?php
namespace itbz\AstirMapper\PDO;
use PDO;
use itbz\AstirMapper\tests\DataModel;


class IteratorTest extends \PHPUnit_Framework_TestCase
{

    function getStmt()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE data(id INTEGER, name, PRIMARY KEY(id ASC));');
        $pdo->query("INSERT INTO data VALUES (1, 'foo')");
        $pdo->query("INSERT INTO data VALUES (2, 'bar')");
        
        $stmt = $pdo->query('SELECT * FROM data');

        return  $stmt;
    }


    function testIterator()
    {
        $iterator = new Iterator($this->getStmt(), 'id', new DataModel());

        $key = '';
        $model = '';
        foreach ($iterator as $key => $model) {
        }

        $this->assertEquals('2', $key);
        $this->assertInstanceOf('itbz\AstirMapper\ModelInterface', $model);
        $this->assertEquals('bar', $model->name);

        foreach ($iterator as $key => $model) {
            break;
        }

        $this->assertEquals('1', $key);
        $this->assertEquals('foo', $model->name);
    }

}
