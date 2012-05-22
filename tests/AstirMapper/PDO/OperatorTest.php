<?php
namespace itbz\AstirMapper\PDO;


class OperatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    public function testInvalidOperator()
    {
        $op = new Operator('column', 'val', 'sdfsdf');
    }


    /**
     * @dataProvider operatorProvider
     */ 
    public function testOperators($operator, $inverted)
    {
        $op = new Operator('column', 'value', $operator);
        $this->assertEquals($operator, $op->getOperator());
    }


    /**
     * @dataProvider operatorProvider
     */
    public function testInversion($operator, $inverted)
    {
        $op = new Operator('column', 'value', $operator);
        $op->invert();
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
