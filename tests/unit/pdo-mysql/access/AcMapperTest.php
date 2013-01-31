<?php
namespace iio\datamapper\pdo\access;

use iio\datamapper\pdo\ExpressionSet;
use iio\datamapper\pdo\Expression;
use iio\datamapper\tests\Model;

class AcMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testSetUser()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('setUser'),
            array(),
            '',
            false
        );

        $table->expects($this->once())
              ->method('setUser')
              ->with('foo', array('bar'));

        $mapper = new AcMapper($table, new Model());
        $mapper->setUser('foo', array('bar'));
    }

    /**
     * @expectedException iio\datamapper\pdo\access\AccessDeniedException
     */
    public function testChownNoRootException()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('userIsRoot'),
            array(),
            '',
            false
        );

        $table->expects($this->once())
              ->method('userIsRoot')
              ->will($this->returnValue(false));

        $mapper = new AcMapper($table, new Model());
        $mapper->chown(new Model(), 'foobar');
    }

    /**
     * @expectedException iio\datamapper\pdo\access\Exception
     */
    public function testChownNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('userIsRoot'),
            array(),
            '',
            false
        );

        $table->expects($this->once())
              ->method('userIsRoot')
              ->will($this->returnValue(true));

        $table->setColumns(array('id', 'data'));
        $table->setPrimaryKey('id');

        $mapper = new AcMapper($table, new Model());
        $mapper->chown(new Model(), 'foobar');
    }

    public function testChown()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('userIsRoot', 'update'),
            array(),
            '',
            false
        );

        $table->expects($this->atLeastOnce())
              ->method('userIsRoot')
              ->will($this->returnValue(true));

        $stmt = $this->getMock(
            "\pdoStatement",
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

        $mapper = new AcMapper($table, new Model());

        $model = new Model();
        $model->id = 'yo';
        $model->owner = 'oldOwner';
        $nRows = $mapper->chown($model, 'foobar');

        $this->assertEquals(1, $nRows);
    }

    /**
     * @expectedException iio\datamapper\pdo\access\Exception
     */
    public function testChmodNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            false
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new Model());
        $mapper->chmod(new Model(), 0700);
    }

    public function testChmod()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('userIsRoot', 'update', 'getUser'),
            array(),
            '',
            false
        );

        $table->expects($this->atLeastOnce())
              ->method('userIsRoot')
              ->will($this->returnValue(false));

        $table->expects($this->atLeastOnce())
              ->method('getUser')
              ->will($this->returnValue('foobar'));

        $stmt = $this->getMock(
            "\pdoStatement",
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

        $mapper = new AcMapper($table, new Model());

        $model = new Model();
        $model->id = 'yo';
        $model->mode = 0777;
        $nRows = $mapper->chmod($model, 0700);

        $this->assertEquals(1, $nRows);
    }

    /**
     * @expectedException iio\datamapper\pdo\access\Exception
     */
    public function testChgrpNoPrimaryKeyException()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            false
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new Model());
        $mapper->chgrp(new Model(), 'foobar');
    }

    /**
     * @expectedException iio\datamapper\pdo\access\AccessDeniedException
     */
    public function testChgrpNotInGroupException()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('getPrimaryKey'),
            array(),
            '',
            false
        );

        $table->expects($this->atLeastOnce())
              ->method('getPrimaryKey')
              ->will($this->returnValue('id'));

        $mapper = new AcMapper($table, new Model());

        $model = new Model();
        $model->id = 'foo';

        $mapper->chgrp($model, 'foobar');
    }

    public function testChgrp()
    {
        $table = $this->getMock(
            'iio\datamapper\pdo\access\AcTable',
            array('userIsRoot', 'update', 'getUser', 'getUserGroups'),
            array(),
            '',
            false
        );

        $table->expects($this->atLeastOnce())
              ->method('userIsRoot')
              ->will($this->returnValue(false));

        $table->expects($this->atLeastOnce())
              ->method('getUser')
              ->will($this->returnValue('uname'));

        $table->expects($this->atLeastOnce())
              ->method('getUserGroups')
              ->will($this->returnValue(array('newgroup')));

        $stmt = $this->getMock(
            "\pdoStatement",
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

        $mapper = new AcMapper($table, new Model());

        $model = new Model();
        $model->id = 'yo';
        $model->group = 'foobar';
        $nRows = $mapper->chgrp($model, 'newgroup');

        $this->assertEquals(1, $nRows);
    }
}
