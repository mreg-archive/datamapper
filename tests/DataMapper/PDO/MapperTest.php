<?php
namespace itbz\DataMapper\PDO;
use itbz\DataMapper\ModelInterface;


class MapperTest extends \PHPUnit_Framework_TestCase
{

    /*
     * Create a Table mock that returns native columns and primary key
     * parsed from $dbStructure
     */
    function getSelectOnceTableMock($dbStructure, $expectNativeColumns = FALSE)
    {
        if (!$expectNativeColumns) $expectNativeColumns = $this->atLeastOnce();

        $table = $this->getMockBuilder('itbz\DataMapper\PDO\Table\Table')
                      ->disableOriginalConstructor()
                      ->setMethods(array('getNativeColumns','getPrimaryKey', 'select', 'insert', 'update', 'delete'))
                      ->getMock();

        $table->expects($expectNativeColumns)
              ->method('getNativeColumns')
              ->will($this->returnValue($dbStructure));

        $pk = $dbStructure[0];
        if (is_null($pk)) $pk = '';
        $table->expects($this->any())
              ->method('getPrimaryKey')
              ->will($this->returnValue($pk));
    
        return $table;
    }


    /*
     * Create a PDOStatement mock that return $data on fetch
     * and returns $data on fetch
     */
    function getSelectOnceStmtMock($data)
    {
        $stmt = $this->getMock(
            "\PDOStatement",
            array('setFetchMode', 'fetch', 'execute', 'rowCount')
        );

        $stmt->expects($this->once())
             ->method('fetch')
             ->will($this->returnValue($data));

        $stmt->expects($this->any())
             ->method('rowCount')
             ->will($this->returnValue(1));

        return $stmt;
    }


    function testFind()
    {
        // Data we will "read" from the database
        $data = array(
            'id' => 1,
            'name' => 'foobar'
        );
        
        $table = $this->getSelectOnceTableMock(array_keys($data), $this->any());

        $search = new Search();
        $search->setLimit(1);

        $where = new ExpressionSet(
            new Expression('id', 1)
        );

        // Assert that select is called with correct params
        $table->expects($this->once())
              ->method('select')
              ->with($search, $where)
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\DataMapper\ModelInterface')
                      ->getMock();

        $model->expects($this->once())
              ->method('load')
              ->with($data);

        $mapper = new Mapper($table, clone $model);
        $returnModel = $mapper->find(array('id'=>1));
        
        // Must return a clone of the prototype model
        $this->assertInstanceOf('itbz\DataMapper\ModelInterface', $returnModel);
    }


    /*
     * Same testing strategy as in testFind
     * Asserts that the first call to model->load only contains primary key
     * as loaded to model in Mapper->findByPk
     */
    function testFindByPk()
    {
        $searchId = '1';
        
        $data = array(
            'id' => $searchId,
            'name' => 'foobar'
        );
        
        $table = $this->getSelectOnceTableMock(array_keys($data), $this->any());

        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\DataMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, $model);

        $mapper->findByPk($searchId);
    }


    /**
     * @expectedException itbz\DataMapper\Exception\NotFoundException
     */
    function testNotFoundException()
    {
        $table = $this->getSelectOnceTableMock(array('id'), $this->any());

        // Return statement with no data
        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock(FALSE)));

        $model = $this->getMockBuilder('itbz\DataMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);

        $mapper->find(array());
    }


    /*
     * Create a PDOStatement mock that returns $rowCount on rowCount
     */
    function getUpdateStmtMock($rowCount = 1)
    {
        $stmt = $this->getMock(
            "\PDOStatement",
            array('setFetchMode', 'fetch', 'execute', 'rowCount')
        );

        $stmt->expects($this->atLeastOnce())
             ->method('rowCount')
             ->will($this->returnValue($rowCount));

        return $stmt;
    }


    function testInsert()
    {
        $table = $this->getSelectOnceTableMock(array('id', 'name'));

        $table->expects($this->any())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock(FALSE)));

        // Assert that insert is called on table
        $table->expects($this->once())
              ->method('insert')
              ->with(new ExpressionSet(
                  new Expression('id', 1),
                  new Expression('name', 'foobar')
              ))
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMock(
            'itbz\DataMapper\ModelInterface',
            array('extract', 'load')
        );

        $model->expects($this->atLeastOnce())
              ->method('extract')
              ->will($this->returnValue(array('id' => 1, 'name' => 'foobar')));

        $mapper = new Mapper($table, clone $model);
        $mapper->save($model);
    }


    function testInsertWithNoPk()
    {
        $table = $this->getSelectOnceTableMock(array('id', 'name'));

        // Assert that insert is called on table
        $table->expects($this->once())
              ->method('insert')
              ->with(new ExpressionSet(
                  new Expression('name', 'foobar')
              ))
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMock(
            'itbz\DataMapper\ModelInterface',
            array('extract', 'load')
        );

        $model->expects($this->atLeastOnce())
              ->method('extract')
              ->will($this->returnValue(array('name' => 'foobar')));

        $mapper = new Mapper($table, clone $model);
        $mapper->save($model);
    }


    function testLastInsertId()
    {
       $table = $this->getMockBuilder('itbz\DataMapper\PDO\Table\Table')
                      ->disableOriginalConstructor()
                      ->setMethods(array('lastInsertId'))
                      ->getMock();

        // Assert that lastInsertId is called on table
        $table->expects($this->once())
              ->method('lastInsertId')
              ->will($this->returnValue(1));

        $model = $this->getMockBuilder('itbz\DataMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $mapper->getLastInsertId();
    }


    function testUpdate()
    {
        $dataInDb = array(
            'id' => 1,
            'name' => 'foobar'
        );

        $table = $this->getSelectOnceTableMock(array_keys($dataInDb));

        $table->expects($this->any())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($dataInDb)));

        // Assert that update is called on table
        $table->expects($this->once())
              ->method('update')
              ->with(new ExpressionSet(
                  new Expression('id', 1),
                  new Expression('name', 'new name')
              ))
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMock(
            'itbz\DataMapper\ModelInterface',
            array('extract', 'load')
        );

        $model->expects($this->atLeastOnce())
              ->method('extract')
              ->will($this->returnValue(array('id' => 1, 'name' => 'new name')));

        $mapper = new Mapper($table, clone $model);
        $mapper->save($model);
    }


    function testDelete()
    {
        $table = $this->getSelectOnceTableMock(array('id'), $this->any());

        // Assert that delete is called on table
        $table->expects($this->once())
              ->method('delete')
              ->with(
                  new ExpressionSet(
                     new Expression('id', 1)
                  )
              )
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMock(
            'itbz\DataMapper\ModelInterface',
            array('extract', 'load')
        );

        $model->expects($this->once())
              ->method('extract')
              ->with(ModelInterface::CONTEXT_DELETE, array('id'))
              ->will($this->returnValue(array('id' => 1)));

        $mapper = new Mapper($table, clone $model);
        $mapper->delete($model);
    }


    /**
     * @expectedException itbz\DataMapper\Exception
     */
    function testModelExtractReturnError()
    {
        $table = $this->getMock(
            '\itbz\DataMapper\PDO\Table\Table',
            array(),
            array(),
            '',
            FALSE
        );

        $model = $this->getMock(
            'itbz\DataMapper\ModelInterface',
            array('extract', 'load')
        );

        $model->expects($this->once())
              ->method('extract')
              ->will($this->returnValue('no array!'));

        $mapper = new Mapper($table, $model);
        $mapper->save($model);
    }


    function testGetNewModel()
    {
        $table = $this->getMock(
            '\itbz\DataMapper\PDO\Table\Table',
            array(),
            array(),
            '',
            FALSE
        );

        $model = $this->getMock(
            '\itbz\DataMapper\ModelInterface',
            array('extract', 'load')
        );

        $mapper = new Mapper($table, $model);
        $newModel = $mapper->getNewModel();
        
        // Assert that new model is a clone
        $this->assertEquals($model, $newModel);
        $this->assertFalse($model === $newModel);
    }

}
