<?php
namespace itbz\DataMapper\PDO;


class ExpressionTest extends \PHPUnit_Framework_TestCase
{

    function testGetName()
    {
        $a = new Expression('name', 'foo');
        $this->assertEquals('name', $a->getName());
    }


    function testGetOperator()
    {
        $a = new Expression('name', 'foo');
        $this->assertEquals('=', $a->getOperator());

        $a = new Expression('name', 'foo', '<');
        $this->assertEquals('<', $a->getOperator());
    }

    
    function testString()
    {
        $a = new Expression('name', 'foo');
        $this->assertEquals('foo', $a->getValue());
        $this->assertTrue($a->escapeValue());
    }

    
    function testBool()
    {
        $a = new Expression('name', TRUE);
        $this->assertEquals('1', $a->getValue());
        $this->assertTrue($a->escapeValue());

        $a = new Expression('name', FALSE);
        $this->assertEquals('0', $a->getValue());
    }


    function testArray()
    {
        $a = new Expression('name', array('foo','bar'));
        $this->assertEquals('foo,bar', $a->getValue());
        $this->assertTrue($a->escapeValue());
    }


    function testNull()
    {
        $a = new Expression('name', NULL);
        $this->assertEquals('null', $a->getValue());
        $this->assertFalse($a->escapeValue());
    }


    /**
     * @expectedException itbz\DataMapper\Exception\PdoException
     */
    function testObjectToStringException()
    {
        // stdClass is not convertable to string
        $a = new Expression('name', new \stdClass());
    }


    function testObject()
    {
        $objmock = $this->getMock(
            '\stdClass',
            array('__tostring')
        );
        
        $objmock->expects($this->once())
                ->method('__tostring')
                ->will($this->returnValue('foobar'));

        $a = new Expression('name', $objmock);
        $this->assertEquals('foobar', $a->getValue());
        $this->assertTrue($a->escapeValue());
    }


    /**
     * @expectedException itbz\DataMapper\Exception\PdoException
     */
    public function testInvalidOperator()
    {
        $op = new Expression('column', 'val', 'sdfsdf');
    }


    /**
     * @dataProvider operatorProvider
     */ 
    public function testOperators($operator, $inverted)
    {
        $op = new Expression('column', 'value', $operator);
        $this->assertEquals($operator, $op->getOperator());
    }


    /**
     * @dataProvider operatorProvider
     */
    public function testInversion($operator, $inverted)
    {
        $op = new Expression('column', 'value', $operator);
        $op->invertOperator();
        $this->assertEquals($inverted, $op->getOperator());
    }


    public function operatorProvider()
    {
        return array(
            array('<=>', '!='),
            array('=', '!='),
            array('>=', '<'),
            array('>', '<='),
            array('IS NOT', 'IS'),
            array('IS', 'IS NOT'),
            array('<=', '>'),
            array('<', '>='),
            array('LIKE', 'NOT LIKE'),
            array('!=', '='),
            array('<>', '='),
            array('NOT LIKE', 'LIKE'),
            array('NOT REGEXP', 'REGEXP'),
            array('REGEXP', 'NOT REGEXP'),
            array('RLIKE', 'NOT REGEXP'),
            array('SOUNDS LIKE', 'SOUNDS LIKE'),
        );
    }

}
