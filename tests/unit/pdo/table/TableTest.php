<?php
namespace iio\datamapper\pdo\table;

use iio\datamapper\pdo\Search;
use iio\datamapper\pdo\Expression;
use iio\datamapper\pdo\ExpressionSet;
use pdo;

class TableTest extends \PHPUnit_Framework_TestCase
{
    private function getPdo()
    {
        $pdo = new pdo('sqlite::memory:');
        $pdo->setAttribute(pdo::ATTR_ERRMODE, pdo::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE foo(id INTEGER, foo1, foobar, PRIMARY KEY(id ASC));');
        $pdo->query('CREATE TABLE bar(foobar, bar1, barx, PRIMARY KEY(foobar ASC));');
        $pdo->query('CREATE TABLE x(barx, x1, PRIMARY KEY(barx ASC));');

        return  $pdo;
    }

    public function testGetName()
    {
        $table = new Table('foo', $this->getPdo());
        $this->assertEquals('foo', $table->getName());
    }

    public function testGetTableIdentifier()
    {
        $tableA = new Table('foo', $this->getPdo());
        $tableB = new Table('bar', $this->getPdo());
        $tableA->addNaturalJoin($tableB);
        $this->assertEquals(
            '`foo` NATURAL LEFT JOIN `bar`',
            $tableA->getTableIdentifier()
        );
    }

    /**
     * @expectedException iio\datamapper\exception\PdoException
     */
    public function testSetPrimaryKeyException()
    {
        $table = new Table('foo', $this->getPdo());
        $table->setPrimaryKey('nonvalid');
    }

    public function testGetPrimaryKey()
    {
        $table = new Table('foo', $this->getPdo());
        $this->assertEquals('', $table->getPrimaryKey());

        $table->setColumns(array('id'));
        $table->setPrimaryKey('id');
        $this->assertEquals('id', $table->getPrimaryKey());
    }

    public function testGetNativeColumns()
    {
        $cols = array('id', 'foobar');

        $table = new Table('foo', $this->getPdo());
        $table->setColumns($cols);

        $this->assertEquals($cols, $table->getNativeColumns());
    }

    public function testGetColumns()
    {
        $tableA = new Table('foo', $this->getPdo());
        $tableA->setColumns(array('id', 'foobar'));

        $tableB = new Table('bar', $this->getPdo());
        $tableB->setColumns(array('foobar'));

        $tableA->addNaturalJoin($tableB);

        $expected = array(
            'id' => '`foo`.`id`',
            'foobar' => '`bar`.`foobar`'
        );

        $this->assertEquals($expected, $tableA->getColumns());
    }

    public function testIsNativeColumn()
    {
        $table = new Table('foo', $this->getPdo());
        $table->setColumns(array('id', 'foobar'));

        $this->assertFalse($table->isNativeColumn('invalid'));
        $this->assertTrue($table->isNativeColumn('id'));
    }

    public function testIsColumn()
    {
        $tableA = new Table('foo', $this->getPdo());
        $tableA->setColumns(array('id'));

        $tableB = new Table('bar', $this->getPdo());
        $tableB->setColumns(array('foobar'));

        $tableA->addNaturalJoin($tableB);

        $this->assertFalse($tableA->isColumn('invalid'));
        $this->assertTrue($tableA->isColumn('foobar'));
    }

    /**
     * @expectedException iio\datamapper\exception\PdoException
     */
    public function testGetColumnIdentifierException()
    {
        $table = new Table('foo', $this->getPdo());
        $table->getColumnIdentifier('invalid');
    }

    public function testGetColumnIdentifier()
    {
        $table = new Table('foo', $this->getPdo());
        $table->setColumns(array('id', 'foobar'));
        $this->assertEquals('`foo`.`id`', $table->getColumnIdentifier('id'));
    }

    public function testInsert()
    {
        $foo = new Table('foo', $this->getPdo());

        $foo->insert(
            new ExpressionSet(
                new Expression('id', '1'),
                new Expression('foo1', 'data'),
                new Expression('foobar', 'a')
            )
        );

        $foo->insert(
            new ExpressionSet(
                new Expression('id', '2'),
                new Expression('foo1', 'data'),
                new Expression('foobar', 'a')
            )
        );

        $this->assertEquals(2, $foo->lastInsertId());

        $stmt = $foo->select(new Search());
        $rowCount = 0;
        while ($row = $stmt->fetch()) {
            $this->assertEquals($row['foobar'], 'a');
            $rowCount++;
        }
        $this->assertEquals(2, $rowCount);
    }

    /**
     * @expectedException iio\datamapper\exception\PdoException
     */
    public function testDeleteException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->delete(new ExpressionSet());
    }

    public function testDelete()
    {
        $foo = new Table('foo', $this->getPdo());

        $foo->insert(
            new ExpressionSet(
                new Expression('id', '1'),
                new Expression('foo1', 'data'),
                new Expression('foobar', 'a')
            )
        );

        $stmt = $foo->select(new Search());
        $rowCount = 0;
        while ($row = $stmt->fetch()) {
            $rowCount++;
        }
        $this->assertEquals(1, $rowCount);

        $stmt = $foo->delete(
            new ExpressionSet(
                new Expression('id', 1)
            )
        );

        $stmt = $foo->select(new Search());
        $rowCount = 0;
        while ($row = $stmt->fetch()) {
            $rowCount++;
        }
        $this->assertEquals(0, $rowCount);
    }

    /**
     * @expectedException iio\datamapper\exception\PdoException
     */
    public function testUpdateEmptyDataException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->update(new ExpressionSet(), new ExpressionSet());
    }

    /**
     * @expectedException iio\datamapper\exception\PdoException
     */
    public function testUpdateEmptyWhereException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->update(
            new ExpressionSet(
                new Expression('foo', 'bar')
            ),
            new ExpressionSet()
        );
    }

    /**
     * @expectedException iio\datamapper\exception\PdoException
     */
    public function testInsertEmptyValuesException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->insert(new ExpressionSet());
    }

    public function testUpdate()
    {
        $foo = new Table('foo', $this->getPdo());

        $foo->insert(
            new ExpressionSet(
                new Expression('id', '1'),
                new Expression('foo1', 'data'),
                new Expression('foobar', 'a')
            )
        );

        $foo->update(
            new ExpressionSet(
                new Expression('foo1', 'new'),
                new Expression('foobar', 'b')
            ),
            new ExpressionSet(
                new Expression('id', 1)
            )
        );

        $stmt = $foo->select(new Search());
        $row = $stmt->fetch();
        $this->assertEquals('new', $row['foo1']);
        $this->assertEquals('b', $row['foobar']);
    }

    public function getTables()
    {
        $pdo = $this->getPdo();

        $foo = new Table('foo', $pdo);
        $foo->setColumns(array('id', 'foo1', 'foobar'));
        $bar = new Table('bar', $pdo);
        $bar->setColumns(array('foobar', 'bar1', 'barx'));
        $x = new Table('x', $pdo);
        $x->setColumns(array('barx', 'x1'));
        $bar->addNaturalJoin($x);
        $foo->addNaturalJoin($bar);

        $foo->insert(
            new ExpressionSet(
                new Expression('id', '1'),
                new Expression('foo1', 'foo-data'),
                new Expression('foobar', 'a')
            )
        );

        $foo->insert(
            new ExpressionSet(
                new Expression('id', '2'),
                new Expression('foo1', 'foo-data'),
                new Expression('foobar', 'b')
            )
        );

        $bar->insert(
            new ExpressionSet(
                new Expression('foobar', 'a'),
                new Expression('bar1', 'bar-data'),
                new Expression('barx', 'A')
            )
        );

        $bar->insert(
            new ExpressionSet(
                new Expression('foobar', 'b'),
                new Expression('bar1', 'bar-data'),
                new Expression('barx', 'B')
            )
        );

        $x->insert(
            new ExpressionSet(
                new Expression('barx', 'A'),
                new Expression('x1', 'x-data')
            )
        );

        return array($foo, $bar, $x);
    }

    public function testSelectJoinedData()
    {
        list($foo) = $this->getTables();

        $stmt = $foo->select(
            new Search(),
            new ExpressionSet(
                new Expression('id', 1)
            )
        );
        $row = $stmt->fetch();
        $this->assertEquals('1', $row['id']);
        $this->assertEquals('foo-data', $row['foo1']);
        $this->assertEquals('a', $row['foobar']);
        $this->assertEquals('bar-data', $row['bar1']);
        $this->assertEquals('A', $row['barx']);
        $this->assertEquals('x-data', $row['x1']);
    }

    public function testSelectLimit()
    {
        list($foo) = $this->getTables();

        // No limit clause
        $stmt = $foo->select(new Search());
        $rowCount = 0;
        while ($row = $stmt->fetch()) {
            $rowCount++;
        }
        $this->assertEquals(2, $rowCount);

        // Limit clause
        $s = new Search();
        $s->setLimit(1);
        $stmt = $foo->select($s);
        $rowCount = 0;
        while ($row = $stmt->fetch()) {
            $rowCount++;
        }
        $this->assertEquals(1, $rowCount);

        $stmt = $foo->select(
            new Search(),
            new ExpressionSet(
                new Expression('id', 2)
            )
        );
        $rowCount = 0;
        while ($row = $stmt->fetch()) {
            $rowCount++;
        }
        $this->assertEquals(1, $rowCount);
    }

    public function testSelectColumn()
    {
        list($foo) = $this->getTables();

        // Assert selecting only some columns
        $search = new Search();
        $search->addColumn('foo1');
        $stmt = $foo->select(
            $search,
            new ExpressionSet(
                new Expression('id', 2)
            )
        );
        $stmt->setFetchMode(pdo::FETCH_ASSOC);
        $data = array('foo1'=>'foo-data');
        $this->assertEquals($data, $stmt->fetch());
    }

    public function testSelectOrderBy()
    {
        list($foo) = $this->getTables();

        $stmt = $foo->select(new Search());

        $row = $stmt->fetch();
        $this->assertEquals('1', $row['id']);

        $search = new Search();
        $search->setOrderBy('id');
        $search->setDesc();
        $stmt = $foo->select($search);
        $row = $stmt->fetch();
        $this->assertEquals('2', $row['id']);
    }
}
