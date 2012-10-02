<?php
namespace itbz\DataMapper\PDO\Access;
use itbz\DataMapper\PDO\ExpressionSet;
use itbz\DataMapper\PDO\Expression;


class AcMapperTest extends \PHPUnit_Framework_TestCase
{

    function testSetUser()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
            array('setUser'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->once())
              ->method('setUser')
              ->with('foo', array('bar'));
        
        $mapper = new AcMapper($table, new \Model());
        $mapper->setUser('foo', array('bar'));
    }


    /**
     * @expectedException itbz\DataMapper\PDO\Access\AccessDeniedException
     */
    function testChownNoRootException()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
            array('userIsRoot'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->once())
              ->method('userIsRoot')
              ->will($this->returnValue(FALSE));

        $mapper = new AcMapper($table, new \Model());
        $mapper->chown(new \Model(), 'foobar');
    }


    /**
     * @expectedException itbz\DataMapper\PDO\Access\Exception
     */
    function testChownNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
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

        $mapper = new AcMapper($table, new \Model());
        $mapper->chown(new \Model(), 'foobar');
    }


    function testChown()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
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

        $data = new ExpressionSet(
            new Expression('id', 'yo'),
            new Expression('owner', 'foobar')
        );

        $where = new ExpressionSet(
            new Expression('id', 'yo')
        );

        $table->expects($this->once())
              ->method('update')
              ->with($data, $where)
              ->will($this->returnValue($stmt));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new \Model());
        
        $model = new \Model();
        $model->id = 'yo';
        $model->owner = 'oldOwner';
        $nRows = $mapper->chown($model, 'foobar');
        
        $this->assertEquals(1, $nRows);
    }


    /**
     * @expectedException itbz\DataMapper\PDO\Access\Exception
     */
    function testChmodNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new \Model());
        $mapper->chmod(new \Model(), 0700);
    }


    function testChmod()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
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

        $data = new ExpressionSet(
            new Expression('id', 'yo'),
            new Expression('mode', 0700)
        );

        $where = new ExpressionSet(
            new Expression('id', 'yo'),
            new Expression('owner', 'foobar')
        );

        $table->expects($this->once())
              ->method('update')
              ->with($data, $where)
              ->will($this->returnValue($stmt));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new \Model());
        
        $model = new \Model();
        $model->id = 'yo';
        $model->mode = 0777;
        $nRows = $mapper->chmod($model, 0700);
        
        $this->assertEquals(1, $nRows);
    }


    /**
     * @expectedException itbz\DataMapper\PDO\Access\Exception
     */
    function testChgrpNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new \Model());
        $mapper->chgrp(new \Model(), 'foobar');
    }


    /**
     * @expectedException itbz\DataMapper\PDO\Access\AccessDeniedException
     */
    function testChgrpNotInGroupException()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            FALSE
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new \Model());

        $model = new \Model();
        $model->id = 'foo';
        
        $mapper->chgrp($model, 'foobar');
    }


    function testChgrp()
    {
        $table = $this->getMock(
            'itbz\DataMapper\PDO\Access\AcTable',
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

        $data = new ExpressionSet(
            new Expression('id', 'yo'),
            new Expression('group', 'newgroup')
        );

        $where = new ExpressionSet(
            new Expression('id', 'yo'),
            new Expression('owner', 'uname')
        );

        $table->expects($this->once())
              ->method('update')
              ->with($data, $where)
              ->will($this->returnValue($stmt));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new \Model());
        
        $model = new \Model();
        $model->id = 'yo';
        $model->group = 'foobar';
        $nRows = $mapper->chgrp($model, 'newgroup');
        
        $this->assertEquals(1, $nRows);
    }

}
