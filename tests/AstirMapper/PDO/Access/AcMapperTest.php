<?php
namespace itbz\AstirMapper\PDO\Access;
use itbz\AstirMapper\tests\DataModel;
use itbz\AstirMapper\PDO\AttributeContainer;
use itbz\AstirMapper\PDO\Attribute;


class AcMapperTest extends \PHPUnit_Framework_TestCase
{

    function testSetUser()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('setUser'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->once())
              ->method('setUser')
              ->with('foo', array('bar'));
        
        $mapper = new AcMapper($table, new DataModel());
        $mapper->setUser('foo', array('bar'));
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\AccessDeniedException
     */
    function testChownNoRootException()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('userIsRoot'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->once())
              ->method('userIsRoot')
              ->will($this->returnValue(FALSE));

        $mapper = new AcMapper($table, new DataModel());
        $mapper->chown(new DataModel(), 'foobar');
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testChownNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('userIsRoot'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->once())
              ->method('userIsRoot')
              ->will($this->returnValue(TRUE));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new DataModel());
        $mapper->chown(new DataModel(), 'foobar');
    }


    function testChown()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('userIsRoot', 'update'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('userIsRoot')
              ->will($this->returnValue(TRUE));

        $stmt = $this->getMock(
            "\PDOStatement",
            array('rowCount')
        );

        $stmt->expects($this->once())
             ->method('rowCount')
             ->will($this->returnValue(1));

        $data = new AttributeContainer(
            new Attribute('id', 'yo'),
            new Attribute('owner', 'foobar')
        );

        $where = new AttributeContainer(
            new Attribute('id', 'yo')
        );

        $table->expects($this->once())
              ->method('update')
              ->with($data, $where)
              ->will($this->returnValue($stmt));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new DataModel());
        
        $model = new DataModel();
        $model->id = 'yo';
        $model->owner = 'oldOwner';
        $nRows = $mapper->chown($model, 'foobar');
        
        $this->assertEquals(1, $nRows);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testChmodNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array(),
            array(),
            '',
            FALSE
        );

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new DataModel());
        $mapper->chmod(new DataModel(), 0700);
    }


    function testChmod()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('userIsRoot', 'update', 'getUser'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('userIsRoot')
              ->will($this->returnValue(FALSE));

        $table->expects($this->atLeastOnce())
              ->method('getUser')
              ->will($this->returnValue('foobar'));

        $stmt = $this->getMock(
            "\PDOStatement",
            array('rowCount')
        );

        $stmt->expects($this->once())
             ->method('rowCount')
             ->will($this->returnValue(1));

        $data = new AttributeContainer(
            new Attribute('id', 'yo'),
            new Attribute('mode', 0700)
        );

        $where = new AttributeContainer(
            new Attribute('id', 'yo'),
            new Attribute('owner', 'foobar')
        );

        $table->expects($this->once())
              ->method('update')
              ->with($data, $where)
              ->will($this->returnValue($stmt));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new DataModel());
        
        $model = new DataModel();
        $model->id = 'yo';
        $model->mode = 0777;
        $nRows = $mapper->chmod($model, 0700);
        
        $this->assertEquals(1, $nRows);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testChgrpNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new DataModel());
        $mapper->chgrp(new DataModel(), 'foobar');
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\AccessDeniedException
     */
    function testChgrpNotInGroupException()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new DataModel());

        $model = new DataModel();
        $model->id = 'foo';
        
        $mapper->chgrp($model, 'foobar');
    }


    /*
        vad är det här för skit
            varför klarar jag inte det här testet?????????
            
            också ett test i AccessStack som inte verkar fungera riktigt
            
            klurigt att få till det här med chgrp....
    */


    /**
     * Not possible to set root unless you are root (in group is not valid)
     * @ expectedException itbz\AstirMapper\Exception\AccessDeniedException
     */
    function testChgrpRootGroupException()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('getPrimaryKey', 'getNativeColumns'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $table->expects($this->atLeastOnce())
              ->method('getNativeColumns')
              ->will($this->returnValue(array('id')));

        $mapper = new AcMapper($table, new DataModel());
        $mapper->setUser('random', array('root'));

        $model = new DataModel();
        $model->id = 'foo';
        
        $mapper->chgrp($model, 'root');
    }


    function testChgrp()
    {
        $table = $this->getMock(
            'itbz\AstirMapper\PDO\Access\AcTable',
            array('userIsRoot', 'update', 'getUser', 'getUserGroups'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('userIsRoot')
              ->will($this->returnValue(FALSE));

        $table->expects($this->atLeastOnce())
              ->method('getUser')
              ->will($this->returnValue('uname'));

        $table->expects($this->atLeastOnce())
              ->method('getUserGroups')
              ->will($this->returnValue(array('newgroup')));

        $stmt = $this->getMock(
            "\PDOStatement",
            array('rowCount')
        );

        $stmt->expects($this->once())
             ->method('rowCount')
             ->will($this->returnValue(1));

        $data = new AttributeContainer(
            new Attribute('id', 'yo'),
            new Attribute('group', 'newgroup')
        );

        $where = new AttributeContainer(
            new Attribute('id', 'yo'),
            new Attribute('owner', 'uname')
        );

        $table->expects($this->once())
              ->method('update')
              ->with($data, $where)
              ->will($this->returnValue($stmt));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new DataModel());
        
        $model = new DataModel();
        $model->id = 'yo';
        $model->group = 'foobar';
        $nRows = $mapper->chgrp($model, 'newgroup');
        
        $this->assertEquals(1, $nRows);
    }

}
