<?php
namespace itbz\AstirMapper\Attribute;


class DateTimeTest extends \PHPUnit_Framework_TestCase
{

    function testConstruct()
    {
        $zone = new \DateTimeZone('UTC');

        $d = new DateTime(2147483647, $zone);
        $this->assertEquals($d->format(DateTime::ISO8601), '2038-01-19T03:14:07+0000');

        $d = new DateTime('2147483647', $zone);
        $this->assertEquals($d->format(DateTime::ISO8601), '2038-01-19T03:14:07+0000');

        $this->assertEquals($d->getTimestamp(), 2147483647);
    }


    function testToString()
    {
        $d = new DateTime('2147483647');
        $this->assertEquals((string)$d, '2147483647');
    }


    /**
     * @expectedException itbz\AstirMapper\Exception
     */
    function testTimestampNotNumeric()
    {
        $d = new DateTime('sdf');
    }


    /**
     * Test timestamp overflow. NOTE: 32bit dependent test
     * @expectedException itbz\AstirMapper\Exception
     */
    function testTimestampOverflow()
    {
        $d = new DateTime('2147483648');
    }


    /**
     * Test timestamp underflow. NOTE: 32bit dependent test
     * @expectedException itbz\AstirMapper\Exception
     */
    function testTimestampUnderflow()
    {
        $d = new DateTime('-2147483649');
    }


    function testToSearchSql()
    {
        $d = new DateTime('2147483647');
        $sql = $d->toSearchSql($context);
        $this->assertEquals($sql, '2147483647');
    }


    function testToInsertSql()
    {
        $d = new DateTime('2147483647');
        $sql = $d->toInsertSql($use);
        $this->assertEquals($sql, '2147483647');
        $this->assertTrue($use);
    }


    /**
     * @expectedException itbz\AstirMapper\Exception
     */
    function testCreateFromFormatError()
    {
        $d = DateTime::createFromFormat('dferfwrvf', '15-Feb-2009');
    }
    

    function testCreateFromFormat()
    {
        $d = DateTime::createFromFormat('j-M-Y', '15-Feb-2009');
        $this->assertEquals('090215', $d->format('ymd'));

        $d = DateTime::createFromFormat(
            'j-M-Y',
            '15-Feb-2009',
            new \DateTimeZone('Pacific/Nauru')
        );
        $this->assertEquals('090215', $d->format('ymd'));

        $this->assertInstanceOf('itbz\AstirMapper\Attribute\DateTime', $d);
    }
}
