<?php
namespace itbz\AstirMapper\PDO;


class MapperTest extends \PHPUnit_Framework_TestCase
{

    /*
     * Create a Table mock that returns native columns and primary key
     * parsed from $dbStructure
     */
    function getSelectOnceTableMock($dbStructure, $expectNativeColumns = FALSE)
    {
        if (!$expectNativeColumns) $expectNativeColumns = $this->atLeastOnce();

        $table = $this->getMockBuilder('itbz\AstirMapper\PDO\Table\Table')
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
            array('setFetchMode', 'fetch', 'execute')
        );

        $stmt->expects($this->once())
             ->method('fetch')
             ->will($this->returnValue($data));

        return $stmt;
    }


    function testFind()
    {
        // Data we will "read" from the database
        $data = array(
            'id' => 1,
            'name' => 'foobar'
        );
        
        $table = $this->getSelectOnceTableMock(array_keys($data));

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

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $model->expects($this->once())
              ->method('load')
              ->with($data);

        $mapper = new Mapper($table, clone $model);
        $model->id = 1;
        $returnModel = $mapper->find($model);
        
        // Must return a clone of the prototype model
        $this->assertInstanceOf('itbz\AstirMapper\ModelInterface', $returnModel);
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
        
        $table = $this->getSelectOnceTableMock(array_keys($data));

        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        // When searching by pk the first call to load must include only PK
        $model->expects($this->at(0))
              ->method('load')
              ->with(array('id'=>'1'));

        $mapper = new Mapper($table, $model);

        $mapper->findByPk($searchId);
    }


    /*
     * Same testing strategy as in testFind
     * Asserts that model->getName is called to read name from model
     */
    function testReadingGetMethod()
    {
        $searchName = 'foobar';
        
        $data = array(
            'name' => $searchName
        );
        
        $table = $this->getSelectOnceTableMock(array_keys($data));

        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->setMethods(array('getName','load'))
                      ->getMock();

        // model->getName should be invoked to read name
        $model->expects($this->once())
              ->method('getName')
              ->will($this->returnValue($searchName));

        $mapper = new Mapper($table, clone $model);

        $mapper->find($model);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\NotFoundException
     */
    function testNotFoundException()
    {
        $table = $this->getSelectOnceTableMock(array('id'));

        // Return statement with no data
        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock(FALSE)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);

        $mapper->find($model);
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

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->id = 1;
        $model->name = 'foobar';
        
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

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->name = 'foobar';
        
        $mapper->save($model);
    }


    function testLastInsertId()
    {
       $table = $this->getMockBuilder('itbz\AstirMapper\PDO\Table\Table')
                      ->disableOriginalConstructor()
                      ->setMethods(array('lastInsertId'))
                      ->getMock();

        // Assert that lastInsertId is called on table
        $table->expects($this->once())
              ->method('lastInsertId')
              ->will($this->returnValue(1));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
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

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->id = 1;
        $model->name = 'new name';
        
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

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        $model->id = 1;
        $mapper->delete($model);
    }

}
