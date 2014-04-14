<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper\pdo;

use datamapper\SearchInterface;

/**
 * PDO search object
 *
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
 */
class Search implements SearchInterface
{
    /**
     * @var string Order by direction
     */
    private $dir = 'ASC';

    /**
     * @var string Order by column
     */
    private $orderBy = '';

    /**
     * @var int Return set start index
     */
    private $startIndex;

    /**
     * @var int Return set limit
     */
    private $limit;

    /**
     * @var array List of columns to select
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
