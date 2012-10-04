<?php
namespace itbz\datamapper\pdo\access;

class ModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException itbz\datamapper\pdo\access\Exception
     */
    public function testActionException()
    {
        new Mode('p', 'table', 'user', array());
    }

    public function testExpression()
    {
        $mode = new Mode('r', 'table', 'user', array('foo','bar'));
        $expected = "isAllowed('r',`table`.`owner`,`table`.`group`,`table`.`mode`,'user','foo,bar')";
        $this->assertEquals($expected, $mode->getValue());
    }
}
