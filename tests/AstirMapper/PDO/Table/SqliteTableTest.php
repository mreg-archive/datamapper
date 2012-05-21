<?php
namespace itbz\AstirMapper\PDO\Table;
use PDO;


class SqliteTableTest extends \PHPUnit_Framework_TestCase
{

    function getPdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE data(id INTEGER, name, foobar, PRIMARY KEY(id ASC));');
        return  $pdo;
    }


    function testReverseEngineerColumns()
    {
        $table = new SqliteTable('data', $this->getPdo());
        $expected = array('id', 'name', 'foobar');
        $this->assertEquals($expected, $table->reverseEngineerColumns());
    }


    function testReverseEngineerPK()
    {
        $table = new SqliteTable('data', $this->getPdo());
        $this->assertEquals('id', $table->reverseEngineerPK());
    }

}
