<?php
namespace itbz\AstirMapper\PDO;


class AttributeTest extends \PHPUnit_Framework_TestCase
{

    function testGetName()
    {
        $a = new Attribute('name', 'foo');
        $this->assertEquals('name', $a->getName());
    }


    function testGetOperator()
    {
        $a = new Attribute('name', 'foo');
        $this->assertEquals('=', $a->getOperator());
    }

    
    function testString()
    {
        $a = new Attribute('name', 'foo');
        $this->assertEquals('foo', $a->getValue());
        $this->assertTrue($a->escape());
    }

    
    function testBool()
    {
        $a = new Attribute('name', TRUE);
        $this->assertEquals('1', $a->getValue());
        $this->assertTrue($a->escape());

        $a = new Attribute('name', FALSE);
        $this->assertEquals('0', $a->getValue());
    }


    function testArray()
    {
        $a = new Attribute('name', array('foo','bar'));
        $this->assertEquals('foo,bar', $a->getValue());
        $this->assertTrue($a->escape());
    }


    function testNull()
    {
        $a = new Attribute('name', NULL);
        $this->assertEquals('null', $a->getValue());
        $this->assertFalse($a->escape());
    }


    /**
     * @expectedException itbz\AstirMapper\Exception\PdoException
     */
    function testObjectToStringException()
    {
        // stdClass is not convertable to string
        $a = new Attribute('name', new \stdClass());
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

        $a = new Attribute('name', $objmock);
        $this->assertEquals('foobar', $a->getValue());
        $this->assertTrue($a->escape());
    }

}
