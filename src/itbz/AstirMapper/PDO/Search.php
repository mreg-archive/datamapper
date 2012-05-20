<?php
/**
 *
 * This file is part of the AstirMapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
namespace itbz\AstirMapper\PDO;
use itbz\AstirMapper\SearchInterface;


/**
 *
 * PDO search object
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class Search implements SearchInterface
{

    /**
     *
     * Order by direction
     *
     * @var string $_dir
     *
     */
    private $_dir = 'ASC';


    /**
     *
     * Order by column
     *
     * @var string $_orderBy
     *
     */
    private $_orderBy = '';


    /**
     *
     * Return set start index
     *
     * @var int $_startIndex
     *
     */
    private $_startIndex;
    

    /**
     *
     * Return set limit
     *
     * @var int $_limit
     *
     */    
    private $_limit;


    /**
     *
     * List of columns to select
     *
     * @var array $_columns
     *
     */
    private $_columns = array();

    
    /**
     *
     * Set order by direction to ascending
     *
     */
    public function setAsc()
    {
        $this->_dir = 'ASC';
    }


    /**
     *
     * Set order by direction to descending
     *
     */
    public function setDesc()
    {
        $this->_dir = 'DESC';
    }
    
    
    /**
     *
     * Get order by direction
     *
     * @return string
     *
     */
    public function getDirection()
    {
        return $this->_dir;
    }


    /**
     *
     * Set order by column
     *
     * @param string $orderBy
     *
     */
    public function setOrderBy($orderBy)
    {
        assert('is_string($orderBy)');
        $this->_orderBy = $orderBy;
    }
    
    
    /**
     *
     * Get order by column
     *
     * @return string
     *
     */
    public function getOrderBy()
    {
        return $this->_orderBy;
    }
    

    /**
     *
     * Set result set start index
     *
     * @param int $startIndex
     *
     */
    public function setStartIndex($startIndex)
    {
        assert('is_int($startIndex)');
        assert('$startIndex >= 0');
        $this->_startIndex = $startIndex;
    }
    

    /**
     *
     * Set result set limit
     *
     * @param int $limit
     *
     */    
    public function setLimit($limit)
    {
        assert('is_int($limit)');
        assert('$limit >= 0');
        $this->_limit = $limit;
    }
    

    /**
     *
     * Get sql limit clause
     *
     * @return string
     *
     */    
    public function getLimit()
    {
        if (!isset($this->_limit)) {
            return '';
        } elseif (!isset($this->_startIndex)) {
            return "LIMIT {$this->_limit}";
        } else {
            return "LIMIT {$this->_startIndex},{$this->_limit}";
        }
    }


    /**
     *
     * Add select column
     *
     * @param string $column
     *
     */
    public function addColumn($column)
    {
        assert('is_string($column)');
        $this->_columns[] = $column;
    }


    /**
     *
     * Get select columns
     *
     * @return array
     *
     */
    public function getColumns()
    {
        return $this->_columns;
    }

}
