<?php
namespace itbz\AstirMapper\PDO\Access;


class ModeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testActionException()
    {
        $mode = new Mode('p', 'table', 'user', array());
    }


    function testExpression()
    {
        $mode = new Mode('r', 'table', 'user', array('foo','bar'));
        $expected = "isAllowed('r',`table`.`owner`,`table`.`group`,`table`.`mode`,'user','foo,bar')";
        $this->assertEquals($expected, $mode->getValue());
    }

}
