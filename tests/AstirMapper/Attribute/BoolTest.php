<?php
namespace itbz\AstirMapper\Attribute;

/*
class BoolTest extends \PHPUnit_Framework_TestCase
{

    public function testIsTrue()
    {
        $bool = new Bool(TRUE);
        $this->assertTrue($bool->isTrue());
        $bool = new Bool(FALSE);
        $this->assertTrue(!$bool->isTrue());
    }


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
 

    public function testToString()
    {
        $bool = new Bool(TRUE);
        $this->assertEquals((string)$bool, '1');
        $bool = new Bool(FALSE);
        $this->assertEquals((string)$bool, '0');
    }


    public function testToInt()
    {
        $bool = new Bool(TRUE);
        $this->assertEquals($bool->toInt(), 1);
        $bool = new Bool(FALSE);
        $this->assertEquals($bool->toInt(), 0);
    }


    public function testToSearchSql()
    {
        $bool = new Bool(TRUE);
        $sql = $bool->toSearchSql($context);
        $this->assertEquals($sql, '1');
    }


    public function testToInsertSql()
    {
        $bool = new Bool(TRUE);
        $sql = $bool->toInsertSql($use);
        $this->assertEquals($sql, '1');
        $this->assertTrue($use);
    }

}
*/
