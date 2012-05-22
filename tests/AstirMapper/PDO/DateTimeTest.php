<?php
namespace itbz\AstirMapper\PDO;


class DateTimeTest extends \PHPUnit_Framework_TestCase
{

    function testToString()
    {
        $d = new DateTime('@2147483647');
        $this->assertEquals((string)$d, '2147483647');
    }

}
