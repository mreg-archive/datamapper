<?php
namespace itbz\AstirMapper\PDO\Table;


class MysqlTableTest extends \itbz\AstirMapper\MysqlTestCase
{

    function testReverseEngineerColumns()
    {
        $table = new MysqlTable('data', $this->getPdo());
        $expected = array('name', 'data', 'owner', 'group', 'mode');
        $this->assertEquals($expected, $table->reverseEngineerColumns());
    }


    function testReverseEngineerPK()
    {
        $table = new MysqlTable('data', $this->getPdo());
        $this->assertEquals('name', $table->reverseEngineerPK());
    }

}
