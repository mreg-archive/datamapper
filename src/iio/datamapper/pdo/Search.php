<?php
/**
 * This file is part of the datamapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package datamapper\pdo
 */

namespace iio\datamapper\pdo;

use iio\datamapper\SearchInterface;

/**
 * pdo search object
 *
 * @package datamapper\pdo
 */
class Search implements SearchInterface
{
    /**
     * Order by direction
     *
     * @var string
     */
    private $dir = 'ASC';

    /**
     * Order by column
     *
     * @var string
     */
    private $orderBy = '';

    /**
     * Return set start index
     *
     * @var int
     */
    private $startIndex;

    /**
     * Return set limit
     *
     * @var int
     */
    private $limit;

    /**
     * List of columns to select
     *
     * @var array
     */
    private $columns = array();

    /**
     * Set order by direction to ascending
     *
     * @return void
     */
    public function setAsc()
    {
        $this->dir = 'ASC';
    }

    /**
     * Set order by direction to descending
     *
     * @return void
     */
    public function setDesc()
    {
        $this->dir = 'DESC';
    }

    /**
     * Get order by direction
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->dir;
    }

    /**
     * Set order by column
     *
     * @param string $orderBy
     *
     * @return void
     */
    public function setOrderBy($orderBy)
    {
        assert('is_string($orderBy)');
        $this->orderBy = $orderBy;
    }

    /**
     * Get order by column
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set result set start index
     *
     * @param int $startIndex
     *
     * @return void
     */
    public function setStartIndex($startIndex)
    {
        assert('is_int($startIndex)');
        assert('$startIndex >= 0');
        $this->startIndex = $startIndex;
    }

    /**
     * Get start index
     *
     * @return int
     */
    public function getStartIndex()
    {
        return $this->startIndex;
    }

    /**
     * Set result set limit
     *
     * @param int $limit
     *
     * @return void
     */
    public function setLimit($limit)
    {
        assert('is_int($limit)');
        assert('$limit >= 0');
        $this->limit = $limit;
    }

    /**
     * Get limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get sql limit clause
     *
     * @return string
     *
     * @return void
     */
    public function getLimitClause()
    {
        $limit = $this->getLimit();
        $startIndex = $this->getStartIndex();
        if (!isset($limit)) {
            return '';
        } elseif (!isset($startIndex)) {
            return "LIMIT $limit";
        } else {
            return "LIMIT $startIndex,$limit";
        }
    }

    /**
     * Add select column
     *
     * @param string $column
     *
     * @return void
     */
    public function addColumn($column)
    {
        assert('is_string($column)');
        $this->columns[] = $column;
    }

    /**
     * Get select columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
