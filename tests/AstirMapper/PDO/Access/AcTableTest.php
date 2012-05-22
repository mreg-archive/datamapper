<?php
namespace itbz\AstirMapper\PDO\Access;
use PDO;


class MockPDO extends PDO
{
    public function __construct ()
    {}
}


/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class AcTableTest extends \PHPUnit_Framework_TestCase
{
    function testIsAllowedRead()
    {
        $pdo = $this->getMock(
            'itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );
        
        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);

        $this->assertFalse($table->isAllowedRead('u', array('g')));
        $this->assertTrue($table->isAllowedRead('user', array('g')));
        $this->assertTrue($table->isAllowedRead('u', array('grp')));
        $this->assertTrue($table->isAllowedRead('root', array('g')));
        $this->assertTrue($table->isAllowedRead('u', array('root')));
    }


    function testIsAllowedWrite()
    {
        $pdo = $this->getMock(
            'itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );
        
        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);

        $this->assertFalse($table->isAllowedWrite('u', array('g')));
        $this->assertTrue($table->isAllowedWrite('user', array('g')));
        $this->assertTrue($table->isAllowedWrite('u', array('grp')));
        $this->assertTrue($table->isAllowedWrite('root', array('g')));
        $this->assertTrue($table->isAllowedWrite('u', array('root')));
    }


    function testIsAllowedExecute()
    {
        $pdo = $this->getMock(
            'itbz\AstirMapper\PDO\Access\MockPDO',
            array()
        );
        
        $table = new AcTable('table', $pdo, 'user', 'grp', 0770);

        $this->assertFalse($table->isAllowedExecute('u', array('g')));
        $this->assertTrue($table->isAllowedExecute('user', array('g')));
        $this->assertTrue($table->isAllowedExecute('u', array('grp')));
        $this->assertTrue($table->isAllowedExecute('root', array('g')));
        $this->assertTrue($table->isAllowedExecute('u', array('root')));
    }

}
