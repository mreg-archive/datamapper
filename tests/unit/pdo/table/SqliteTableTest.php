<?php
namespace iio\datamapper\pdo\table;

use pdo;

class SqliteTableTest extends \PHPUnit_Framework_TestCase
{
    private function getPdo()
    {
        $pdo = new pdo('sqlite::memory:');
        $pdo->setAttribute(pdo::ATTR_ERRMODE, pdo::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE data(id INTEGER, name, foobar, PRIMARY KEY(id ASC));');

        return  $pdo;
    }

    public function testReverseEngineerColumns()
    {
        $table = new SqliteTable('data', $this->getPdo());
        $expected = array('id', 'name', 'foobar');
        $this->assertEquals($expected, $table->reverseEngineerColumns());
    }

    public function testReverseEngineerPK()
    {
        $table = new SqliteTable('data', $this->getPdo());
        $this->assertEquals('id', $table->reverseEngineerPK());
    }
}
