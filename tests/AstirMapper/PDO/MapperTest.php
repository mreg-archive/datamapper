<?php
namespace itbz\AstirMapper\PDO;


class MapperTest extends \PHPUnit_Framework_TestCase
{

    /*
     * Create a Table mock that returns native columns and primary key
     * parsed from $data keys
     */
    function getSelectOnceTableMock($data, $expectNativeColumns = FALSE)
    {
        if (!$expectNativeColumns) $expectNativeColumns = $this->atLeastOnce();

        $table = $this->getMockBuilder('itbz\AstirMapper\PDO\Table\Table')
                      ->disableOriginalConstructor()
                      ->setMethods(array('getNativeColumns','getPrimaryKey', 'select', 'insert', 'update', 'delete'))
                      ->getMock();

        $keys = array_keys($data);
        $table->expects($expectNativeColumns)
              ->method('getNativeColumns')
              ->will($this->returnValue($keys));

        $pk = array_shift($keys);
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
        $searchId = '1';
        
        // Data we will "read" from the database
        $data = array(
            'id' => $searchId,
            'name' => 'foobar'
        );
        
        // Mock Table
        $table = $this->getSelectOnceTableMock($data);

        $search = new Search();
        $search->setLimit(1);

        $where = array(
            '`id`=?' => $searchId
        );

        $table->expects($this->once())
              ->method('select')
              ->with($search, $where)
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        // Mock the Model interface
        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $model->expects($this->once())
              ->method('load')
              ->with($data);

        $mapper = new Mapper($table, clone $model);

        $model->id = $searchId;

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
        
        $table = $this->getSelectOnceTableMock($data);

        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        // When searching by pk the first call to load must include only PK!!
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
        
        $table = $this->getSelectOnceTableMock($data);

        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->setMethods(array('getName','load'))
                      ->getMock();

        // model->getName should be invoked to read name!!
        $model->expects($this->once())
              ->method('getName')
              ->will($this->returnValue($searchName));

        $mapper = new Mapper($table, clone $model);

        $mapper->find($model);
    }


    /*
     * Same testing strategy as in testFind
     * Asserts that attribute->toSearchSql is called when parsing model
     */
    function testAttributeToSearch()
    {
        $nameAttribute = $this->getMockBuilder('itbz\AstirMapper\Attribute\AttributeInterface')
                              ->getMock();

        // attribute->toSearchSql must be called when creating serch data!! 
        $nameAttribute->expects($this->atLeastOnce())
              ->method('toSearchSql');

        $data = array(
            'name' => $nameAttribute
        );
        
        $table = $this->getSelectOnceTableMock($data);

        $table->expects($this->once())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($data)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);

        $model->name = $nameAttribute;

        $mapper->find($model);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\NotFoundException
     */
    function testNotFoundException()
    {
        $table = $this->getSelectOnceTableMock(array());

        // Return statement with no data!!
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
        $data = array(
            'id' => 1,
            'name' => 'foobar'
        );

        $table = $this->getSelectOnceTableMock($data);

        $table->expects($this->any())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock(FALSE)));

        // Assert that insert is called on table with $data!!
        $table->expects($this->once())
              ->method('insert')
              ->with($data)
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->id = $data['id'];
        $model->name = $data['name'];
        
        $mapper->save($model);
    }


    function testInsertWithNoPk()
    {
        // id is primary key in database
        $dbStructure = array(
            'id' => TRUE,
            'name' => TRUE
        );

        // saveing data with no primary key, should trigger insert()
        $insertData = array(
            'name' => 'foobar'
        );

        $table = $this->getSelectOnceTableMock($dbStructure);

        // Assert that insert is called on table with $insertData!!
        $table->expects($this->once())
              ->method('insert')
              ->with($insertData)
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->name = $insertData['name'];
        
        $mapper->save($model);
    }


    function testLastInsertId()
    {
       $table = $this->getMockBuilder('itbz\AstirMapper\PDO\Table\Table')
                      ->disableOriginalConstructor()
                      ->setMethods(array('lastInsertId'))
                      ->getMock();

        // Assert that lastInsertId is called on table!!
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

        $dataToUpdate = array(
            'id' => 1,
            'name' => 'new name'
        );

        $table = $this->getSelectOnceTableMock($dataInDb);

        $table->expects($this->any())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock($dataInDb)));

        // Assert that update is called on table with $dataToUpdate!!
        $table->expects($this->once())
              ->method('update')
              ->with($dataToUpdate)
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->id = $dataToUpdate['id'];
        $model->name = $dataToUpdate['name'];
        
        $mapper->save($model);
    }


    /*
     * Same testing strategy as in testInsert
     * Asserts that attribute->toInsertSql is called when parsing model
     */
    function testInsertAttribute()
    {
        $nameAttribute = $this->getMockBuilder('itbz\AstirMapper\Attribute\AttributeInterface')
                              ->getMock();

        // attribute->toInsertSql must be called when inserting!! 
        $nameAttribute->expects($this->atLeastOnce())
              ->method('toInsertSql');


        $data = array(
            'id' => 1,
            'name' => $nameAttribute
        );

        $table = $this->getSelectOnceTableMock($data);

        $table->expects($this->any())
              ->method('select')
              ->will($this->returnValue($this->getSelectOnceStmtMock(FALSE)));

        $table->expects($this->once())
              ->method('insert')
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->id = $data['id'];
        $model->name = $data['name'];
        
        $mapper->save($model);
    }


    function testDelete()
    {
        $dbStructure = array(
            'id' => TRUE,
        );

        $where = array(
            '`id`=?' => '1'
        );

        $table = $this->getSelectOnceTableMock($dbStructure, $this->any());

        // Assert that delete is called on table with $where!!
        $table->expects($this->once())
              ->method('delete')
              ->with($where)
              ->will($this->returnValue($this->getUpdateStmtMock(1)));

        $model = $this->getMockBuilder('itbz\AstirMapper\ModelInterface')
                      ->getMock();

        $mapper = new Mapper($table, clone $model);
        
        $model->id = 1;
        
        $mapper->delete($model);
    }

}
