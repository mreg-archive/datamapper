<?php
namespace datamapper\pdo\access;

use datamapper\pdo\Search;
use datamapper\pdo\ExpressionSet;

class AcTableTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAllowedRead()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);

        $table->setUser('u', array('g'));
        $this->assertFalse($table->isAllowedRead());

        $table->setUser('user', array('g'));
        $this->assertTrue($table->isAllowedRead());

        $table->setUser('u', array('grp'));
        $this->assertTrue($table->isAllowedRead());

        $table->setUser('root', array('g'));
        $this->assertTrue($table->isAllowedRead());

        $table->setUser('u', array('root'));
        $this->assertTrue($table->isAllowedRead());
    }

    public function testIsAllowedWrite()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);

        $table->setUser('u', array('g'));
        $this->assertFalse($table->isAllowedWrite());

        $table->setUser('user', array('g'));
        $this->assertTrue($table->isAllowedWrite());

        $table->setUser('u', array('grp'));
        $this->assertTrue($table->isAllowedWrite());

        $table->setUser('root', array('g'));
        $this->assertTrue($table->isAllowedWrite());

        $table->setUser('u', array('root'));
        $this->assertTrue($table->isAllowedWrite());
    }

    public function testIsAllowedExecute()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);

        $table->setUser('u', array('g'));
        $this->assertFalse($table->isAllowedExecute());

        $table->setUser('user', array('g'));
        $this->assertTrue($table->isAllowedExecute());

        $table->setUser('u', array('grp'));
        $this->assertTrue($table->isAllowedExecute());

        $table->setUser('root', array('g'));
        $this->assertTrue($table->isAllowedExecute());

        $table->setUser('u', array('root'));
        $this->assertTrue($table->isAllowedExecute());
    }

    public function testRecursiveIsAllowed()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $basetable = new AcTable('base', $pdo, 'user', 'grp', 0770);
        $restrictedtable = new AcTable('restrict', $pdo, '', '', 0);

        // User is allowed to work on base table
        $basetable->setUser('user', array());
        $this->assertTrue($basetable->isAllowedExecute());

        // But not on restricted table
        $basetable->addNaturalJoin($restrictedtable);
        $basetable->setUser('user', array());
        $this->assertFalse($basetable->isAllowedExecute());
    }

    /**
     * @expectedException datamapper\pdo\access\AccessDeniedException
     */
    public function testSelectTableAccessException()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->select(new Search());
    }

    /**
     * @expectedException datamapper\pdo\access\AccessDeniedException
     */
    public function testDeleteTableAccessException()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->delete(new ExpressionSet());
    }

    /**
     * @expectedException datamapper\pdo\access\AccessDeniedException
     */
    public function testInsertTableAccessException()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->insert(new ExpressionSet());
    }

    /**
     * @expectedException datamapper\pdo\access\AccessDeniedException
     */
    public function testUpdateTableAccessException()
    {
        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->update(new ExpressionSet(), new ExpressionSet());
    }

    public function testSelect()
    {
        $stmt = $this->getMock(
            "\pdoStatement",
            array('execute')
        );

        $stmt->expects($this->atLeastOnce())
             ->method('execute');

        $pdo = $this->getMock(
            '\datamapper\tests\MockPDO',
            array('prepare')
        );

        $pdo->expects($this->atLeastOnce())
            ->method('prepare')
            ->will($this->returnValue($stmt));

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->setUser('user');

        $table->select(new Search());
    }
}
