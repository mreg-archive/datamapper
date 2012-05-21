<?php
namespace itbz\Astir;
use PHPUnit_Framework_TestCase;


class BoolTest extends PHPUnit_Framework_TestCase
{

   /**
     * Test the isTrue() method
     */
    public function testIsTrue()
    {
        $bool = new Bool(TRUE);
        $this->assertTrue($bool->isTrue());
        $bool = new Bool(FALSE);
        $this->assertTrue(!$bool->isTrue());
    }


    /**
     * Test construct method and various valid arguments
     */
    public function testConstruct()
    {
        $bool = new Bool(0);
        $this->assertTrue(!$bool->isTrue());

        $bool = new Bool(1);
        $this->assertTrue($bool->isTrue());

        $bool = new Bool(100);
        $this->assertTrue($bool->isTrue());

        $bool = new Bool('0');
        $this->assertTrue(!$bool->isTrue());

        $bool = new Bool('1');
        $this->assertTrue($bool->isTrue());

        $bool = new Bool('true');
        $this->assertTrue($bool->isTrue());

        $bool = new Bool('TRUE');
        $this->assertTrue($bool->isTrue());

        $bool = new Bool('checked');
        $this->assertTrue($bool->isTrue());

        $bool = new Bool('CHECKED');
        $this->assertTrue($bool->isTrue());

        $bool = new Bool('false');
        $this->assertTrue(!$bool->isTrue());

        $bool = new Bool('FALSE');
        $this->assertTrue(!$bool->isTrue());

        $bool = new Bool('');
        $this->assertTrue(!$bool->isTrue());
    }
 

    /**
     * Test that toString delivers '1' and '0' strings
     */
    public function testToString()
    {
        $bool = new Bool(TRUE);
        $this->assertEquals((string)$bool, '1');
        $bool = new Bool(FALSE);
        $this->assertEquals((string)$bool, '0');
    }


    /**
     * Test that toInt delivers 1 and 0
     */
    public function testToInt()
    {
        $bool = new Bool(TRUE);
        $this->assertEquals($bool->toInt(), 1);
        $bool = new Bool(FALSE);
        $this->assertEquals($bool->toInt(), 0);
    }


    /**
     * Test toSearchSql()
     */
    public function testToSearchSql()
    {
        $bool = new Bool(TRUE);
        $sql = $bool->toSearchSql($context);
        $this->assertEquals($sql, '1');
    }


    /**
     * Test toInsertSql()
     */
    public function testToInsertSql()
    {
        $bool = new Bool(TRUE);
        $sql = $bool->toInsertSql($use);
        $this->assertEquals($sql, '1');
        $this->assertTrue($use);
    }

}
