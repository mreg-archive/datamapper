<?php
namespace datamapper\pdo;

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $a = new Expression('name', 'foo');
        $this->assertEquals('name', $a->getName());
    }

    public function testGetOperator()
    {
        $a = new Expression('name', 'foo');
        $this->assertEquals('=', $a->getOperator());

        $a = new Expression('name', 'foo', '<');
        $this->assertEquals('<', $a->getOperator());
    }

    public function testString()
    {
        $a = new Expression('name', 'foo');
        $this->assertEquals('foo', $a->getValue());
        $this->assertTrue($a->escapeValue());
    }

    public function testBool()
    {
        $a = new Expression('name', true);
        $this->assertEquals('1', $a->getValue());
        $this->assertTrue($a->escapeValue());

        $a = new Expression('name', false);
        $this->assertEquals('0', $a->getValue());
    }

    public function testArray()
    {
        $a = new Expression('name', array('foo','bar'));
        $this->assertEquals('foo,bar', $a->getValue());
        $this->assertTrue($a->escapeValue());
    }

    public function testNull()
    {
        $a = new Expression('name', null);
        $this->assertEquals('null', $a->getValue());
        $this->assertFalse($a->escapeValue());
    }

    /**
     * @expectedException datamapper\exception\PdoException
     */
    public function testObjectToStringException()
    {
        // stdClass is not convertable to string
        new Expression('name', new \stdClass());
    }

    public function testObject()
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
     * @expectedException datamapper\exception\PdoException
     */
    public function testInvalidOperator()
    {
        new Expression('column', 'val', 'sdfsdf');
    }

    /**
     * @dataProvider operatorProvider
     */
    public function testOperators($operator)
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
