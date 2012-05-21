<?php
namespace itbz\AstirMapper\PDO;
use itbz\AstirMapper\ModelInterface;
use PDO;
use itbz\AstirMapper\tests\DataModel;
use itbz\AstirMapper\PDO\Table\SqliteTable;


/*
 * Some random test on the complete PDO stack
 * if test fails start looking at the concrete test cases
 */
class PdoStackTest extends \PHPUnit_Framework_TestCase
{

    function getPdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE data(id INTEGER, name, PRIMARY KEY(id ASC));');
        return  $pdo;
    }


    function getTable()
    {
        $table = new SqliteTable('data', $this->getPdo());
        return $table;
    }

    
    function getMapper()
    {
        return new Mapper($this->getTable(), new DataModel());
    }


    function testCRUD()
    {
        $mapper = $this->getMapper();
        
        // Insert two rows
        $model1 = new DataModel();
        $model1->name = "foobar";
        $mapper->save($model1);
        $mapper->save($model1);
        
        // Find by primary key
        $model2 = $mapper->findByPk(1);
        $this->assertEquals('foobar', $model2->name);

        // Find by name
        $model3 = $mapper->find($model1);
        $this->assertEquals('1', $model3->id);

        // Delete primary key == 1
        $model4 = new DataModel();
        $model4->id = 1;
        $mapper->delete($model4);

        // Find by name yields primary key == 2
        $model5 = $mapper->find($model1);
        $this->assertEquals('2', $model5->id);
    }


    function testFindMany()
    {
        $mapper = $this->getMapper();
        $model = new DataModel();
        $model->name = "foobar";
        $mapper->save($model);
        $mapper->save($model);
        $mapper->save($model);

        $iterator = $mapper->findMany($model, new Search());
        $key = '';
        foreach ($iterator as $key => $mod) {}
        
        $this->assertEquals('3', $key);
    }

}
