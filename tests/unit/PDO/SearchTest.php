<?php
namespace itbz\DataMapper\PDO;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    public function testAscDesc()
    {
        $s = new Search();
        $this->assertEquals('ASC', $s->getDirection());
        $s->setDesc();
        $this->assertEquals('DESC', $s->getDirection());
        $s->setAsc();
        $this->assertEquals('ASC', $s->getDirection());
    }

    public function testOrderBy()
    {
        $s = new Search();
        $this->assertEquals('', $s->getOrderBy());
        $s->setOrderBy('id');
        $this->assertEquals('id', $s->getOrderBy());
    }

    public function testLimit()
    {
        $s = new Search();
        $this->assertEquals('', $s->getLimitClause());
        $s->setLimit(10);
        $this->assertEquals('LIMIT 10', $s->getLimitClause());
        $s->setStartIndex(1);
        $this->assertEquals('LIMIT 1,10', $s->getLimitClause());
    }

    public function testColumns()
    {
        $s = new Search();
        $this->assertEquals(array(), $s->getColumns());
        $s->addColumn('id');
        $this->assertEquals(array('id'), $s->getColumns());
        $s->addColumn('name');
        $this->assertEquals(array('id','name'), $s->getColumns());
    }
}
