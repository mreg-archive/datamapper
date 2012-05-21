<?php
namespace itbz\Astir;


class DateTimeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test construct method and timestamp argument
     */
    public function testConstruct()
    {
        $zone = new \DateTimeZone('UTC');

        $d = new DateTime(2147483647, $zone);
        $this->assertEquals($d->format(DateTime::ISO8601), '2038-01-19T03:14:07+0000');

        $d = new DateTime('2147483647', $zone);
        $this->assertEquals($d->format(DateTime::ISO8601), '2038-01-19T03:14:07+0000');

        $this->assertEquals($d->getTimestamp(), 2147483647);
    }


    /**
     * Test that toString delivers timestamp
     */
    public function testToString()
    {
        $d = new DateTime('2147483647');
        $this->assertEquals((string)$d, '2147483647');
    }


    /**
     * Test timestamp overflow. NOTE: 32bit dependent test
     * @expectedException InvalidArgumentException
     */
    public function testTimestampOverflow()
    {
        $d = new DateTime('2147483648');
    }


    /**
     * Test timestamp underflow. NOTE: 32bit dependent test
     * @expectedException InvalidArgumentException
     */
    public function testTimestampUnderflow()
    {
        $d = new DateTime('-2147483649');
    }


    /**
     * Test toSearchSql()
     */
    public function testToSearchSql()
    {
        $d = new DateTime('2147483647');
        $sql = $d->toSearchSql($context);
        $this->assertEquals($sql, '2147483647');
    }


    /**
     * Test toInsertSql()
     */
    public function testToInsertSql()
    {
        $d = new DateTime('2147483647');
        $sql = $d->toInsertSql($use);
        $this->assertEquals($sql, '2147483647');
        $this->assertTrue($use);
    }

}
