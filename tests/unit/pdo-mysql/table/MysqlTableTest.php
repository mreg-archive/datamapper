<?php
namespace iio\datamapper\pdo\table;

class MysqlTableTest extends \iio\datamapper\MysqlTestCase
{
    public function testReverseEngineerColumns()
    {
        $table = new MysqlTable('data', $this->getPdo());
        $expected = array('name', 'data', 'owner', 'group', 'mode');
        $this->assertEquals($expected, $table->reverseEngineerColumns());
    }

    public function testReverseEngineerPK()
    {
        $table = new MysqlTable('data', $this->getPdo());
        $this->assertEquals('name', $table->reverseEngineerPK());
    }
}
