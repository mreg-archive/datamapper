<?php
namespace itbz\AstirMapper\PDO\Access;
use itbz\AstirMapper\PDO\Search;
use itbz\AstirMapper\PDO\ExpressionSet;


class AcTableTest extends \PHPUnit_Framework_TestCase
{

    function testIsAllowedRead()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
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


    function testIsAllowedWrite()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
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


    function testIsAllowedExecute()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
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


    function testRecursiveIsAllowed()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
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
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testSelectTableAccessException()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->select(new Search());
    }


    /**
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testDeleteTableAccessException()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->delete(new ExpressionSet());
    }


    /**
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testInsertTableAccessException()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->insert(new ExpressionSet());
    }


    /**
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testUpdateTableAccessException()
    {
        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );

        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);
        $table->update(new ExpressionSet(), new ExpressionSet());
   }


    function testSelect()
    {
        $stmt = $this->getMock(
            "\PDOStatement",
            array('execute')
        );

        $stmt->expects($this->atLeastOnce())
             ->method('execute');

        $pdo = $this->getMock(
            '\itbz\AstirMapper\PDO\Access\MockPDO',
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

class MockPDO extends \PDO
{
    public function __construct (){}
}