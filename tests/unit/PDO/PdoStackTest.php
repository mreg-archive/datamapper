<?php
namespace itbz\DataMapper\PDO;
use itbz\DataMapper\ModelInterface;
use PDO;
use itbz\DataMapper\PDO\Table\SqliteTable;


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
        return new Mapper($this->getTable(), new \Model());
    }


    function testCRUD()
    {
        $mapper = $this->getMapper();
        
        // Insert two rows
        $model1 = new \Model();
        $model1->name = "foobar";
        $mapper->save($model1);
        $mapper->save($model1);

        // Find by primary key
        $model2 = $mapper->findByPk(1);
        $this->assertEquals('foobar', $model2->name);

        // Find by name
        $model3 = $mapper->find(array('name'=>'foobar'));
        $this->assertEquals('1', $model3->id);

        // Delete primary key == 1
        $model4 = new \Model();
        $model4->id = 1;
        $mapper->delete($model4);

        // Find by name yields primary key == 2
        $model5 = $mapper->find(array('name'=>'foobar'));
        $this->assertEquals('2', $model5->id);
    }


    function testFindMany()
    {
        $mapper = $this->getMapper();
        $model = new \Model();
        $model->name = "foobar";
        $mapper->save($model);
        $mapper->save($model);
        $mapper->save($model);

        $iterator = $mapper->findMany(array('name'=>'foobar'), new Search());
        $key = '';
        foreach ($iterator as $key => $mod) {}
        
        $this->assertEquals('3', $key);
    }


    function testInsertBool()
    {
        $mapper = $this->getMapper();
        $model = new \Model();

        $model->name = FALSE;
        $mapper->save($model);

        $model->name = TRUE;
        $mapper->save($model);

        $model2 = $mapper->findByPk(1);
        $this->assertSame('0', $model2->name);
        $this->assertFalse((bool)$model2->name);

        $model3 = $mapper->findByPk(2);
        $this->assertSame('1', $model3->name);
        $this->assertTrue((bool)$model3->name);
    }


    function testInsertNull()
    {
        $mapper = $this->getMapper();
        $model = new \Model();
        $model->name = NULL;
        $mapper->save($model);

        $model2 = $mapper->findByPk(1);
        $this->assertNull($model2->name);
    }

}
