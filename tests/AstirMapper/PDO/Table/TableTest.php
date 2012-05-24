<?php
namespace itbz\AstirMapper\PDO\Table;
use itbz\AstirMapper\PDO\Search;
use itbz\AstirMapper\PDO\Expression;
use itbz\AstirMapper\PDO\ExpressionSet;
use PDO;


class TableTest extends \PHPUnit_Framework_TestCase
{

    function getPdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE foo(id INTEGER, foo1, foobar, PRIMARY KEY(id ASC));');
        $pdo->query('CREATE TABLE bar(foobar, bar1, barx, PRIMARY KEY(foobar ASC));');
        $pdo->query('CREATE TABLE x(barx, x1, PRIMARY KEY(barx ASC));');
        return  $pdo;
    }


    function testGetName()
    {
        $table = new Table('foo', $this->getPdo());
        $this->assertEquals('foo', $table->getName());
    }


    function testGetTableIdentifier()
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
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testSetPrimaryKeyException()
    {
        $table = new Table('foo', $this->getPdo());
        $table->setPrimaryKey('nonvalid');
    }


    function testGetPrimaryKey()
    {
        $table = new Table('foo', $this->getPdo());
        $this->assertEquals('', $table->getPrimaryKey());

        $table->setColumns(array('id'));
        $table->setPrimaryKey('id');
        $this->assertEquals('id', $table->getPrimaryKey());
    }


    function testGetNativeColumns()
    {
        $cols = array('id', 'foobar');

        $table = new Table('foo', $this->getPdo());
        $table->setColumns($cols);
    
        $this->assertEquals($cols, $table->getNativeColumns());
    }


    function testGetColumns()
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


    function testIsNativeColumn()
    {
        $table = new Table('foo', $this->getPdo());
        $table->setColumns(array('id', 'foobar'));

        $this->assertFalse($table->isNativeColumn('invalid'));
        $this->assertTrue($table->isNativeColumn('id'));
    }


    function testIsColumn()
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
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testGetColumnIdentifierException()
    {
        $table = new Table('foo', $this->getPdo());
        $table->getColumnIdentifier('invalid');
    }


    function testGetColumnIdentifier()
    {
        $table = new Table('foo', $this->getPdo());
        $table->setColumns(array('id', 'foobar'));
        $this->assertEquals('`foo`.`id`', $table->getColumnIdentifier('id'));
    }


    function testInsert()
    {
        $foo = new Table('foo', $this->getPdo());
        
        $foo->insert(new ExpressionSet(
            new Expression('id', '1'),
            new Expression('foo1', 'data'),
            new Expression('foobar', 'a')
        ));
        
        $foo->insert(new ExpressionSet(
            new Expression('id', '2'),
            new Expression('foo1', 'data'),
            new Expression('foobar', 'a')
        ));
        
        $this->assertEquals(2, $foo->lastInsertId());
        
        $stmt = $foo->select(new Search());  
        $rowCount = 0;
        while ( $row = $stmt->fetch() ) {
            $this->assertEquals($row['foobar'], 'a');
            $rowCount++;
        }
        $this->assertEquals(2, $rowCount);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testDeleteException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->delete(new ExpressionSet());
    }


    function testDelete()
    {
        $foo = new Table('foo', $this->getPdo());
        
        $foo->insert(new ExpressionSet(
            new Expression('id', '1'),
            new Expression('foo1', 'data'),
            new Expression('foobar', 'a')
        ));

        $stmt = $foo->select(new Search());  
        $rowCount = 0;
        while ( $row = $stmt->fetch() ) $rowCount++;
        $this->assertEquals(1, $rowCount);

        $stmt = $foo->delete(new ExpressionSet(
            new Expression('id', 1)
        ));

        $stmt = $foo->select(new Search());
        $rowCount = 0;
        while ( $row = $stmt->fetch() ) $rowCount++;
        $this->assertEquals(0, $rowCount);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testUpdateEmptyDataException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->update(new ExpressionSet(), new ExpressionSet());
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testUpdateEmptyWhereException()
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
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testInsertEmptyValuesException()
    {
        $foo = new Table('foo', $this->getPdo());
        $foo->insert(new ExpressionSet());
    }


    function testUpdate()
    {
        $foo = new Table('foo', $this->getPdo());
        
        $foo->insert(new ExpressionSet(
            new Expression('id', '1'),
            new Expression('foo1', 'data'),
            new Expression('foobar', 'a')
        ));

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


    function getTables()
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

        $foo->insert(new ExpressionSet(
            new Expression('id', '1'),
            new Expression('foo1', 'foo-data'),
            new Expression('foobar', 'a')
        ));

        $foo->insert(new ExpressionSet(
            new Expression('id', '2'),
            new Expression('foo1', 'foo-data'),
            new Expression('foobar', 'b')
        ));

        $bar->insert(new ExpressionSet(
            new Expression('foobar', 'a'),
            new Expression('bar1', 'bar-data'),
            new Expression('barx', 'A')
        ));

        $bar->insert(new ExpressionSet(
            new Expression('foobar', 'b'),
            new Expression('bar1', 'bar-data'),
            new Expression('barx', 'B')
        ));

        $x->insert(new ExpressionSet(
            new Expression('barx', 'A'),
            new Expression('x1', 'x-data')
        ));

        return array($foo, $bar, $x);
    }


    function testSelectJoinedData()
    {
        list($foo, $bar, $x) = $this->getTables();

        $stmt = $foo->select(new Search(), new ExpressionSet(
            new Expression('id', 1)
        ));
        $row = $stmt->fetch();
        $this->assertEquals('1', $row['id']);
        $this->assertEquals('foo-data', $row['foo1']);
        $this->assertEquals('a', $row['foobar']);
        $this->assertEquals('bar-data', $row['bar1']);
        $this->assertEquals('A', $row['barx']);
        $this->assertEquals('x-data', $row['x1']);
    }
    
    function testSelectLimit()
    {
        list($foo, $bar, $x) = $this->getTables();

        // No limit clause
        $stmt = $foo->select(new Search());
        $rowCount = 0;
        while ( $row = $stmt->fetch() ) $rowCount++;
        $this->assertEquals(2, $rowCount);

        // Limit clause
        $s = new Search();
        $s->setLimit(1);
        $stmt = $foo->select($s);
        $rowCount = 0;
        while ( $row = $stmt->fetch() ) $rowCount++;
        $this->assertEquals(1, $rowCount);

        $stmt = $foo->select(new Search(), new ExpressionSet(
            new Expression('id', 2)
        ));
        $rowCount = 0;
        while ( $row = $stmt->fetch() ) $rowCount++;
        $this->assertEquals(1, $rowCount);
    }
    
    function testSelectColumn()
    {
        list($foo, $bar, $x) = $this->getTables();

        // Assert selecting only some columns
        $search = new Search();
        $search->addColumn('foo1');
        $stmt = $foo->select($search, new ExpressionSet(
            new Expression('id', 2)
        ));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = array('foo1'=>'foo-data');
        $this->assertEquals($data, $stmt->fetch());
    }

    
    function testSelectOrderBy()
    {
        list($foo, $bar, $x) = $this->getTables();

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
