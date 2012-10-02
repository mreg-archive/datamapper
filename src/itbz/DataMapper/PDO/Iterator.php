<?php
/**
 * This file is part of the DataMapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package DataMapper\PDO
 */

namespace itbz\DataMapper\PDO;

use itbz\DataMapper\ModelInterface;
use PDO;
use PDOStatement;

/**
 * Iterates over rows in a PDOStatement returning Model instances
 *
 * PDOStatements are iterable as is. This class adds support for rewinds and
 * key retrieval. Also each result row is returned as a Model and not an array.
 *
 * @package DataMapper\PDO
 */
class Iterator implements \Iterator
{
    /**
     * PDO statement to iterate
     *
     * @var PDOStatement
     */
    private $stmt;

    /**
     * True if PDOStatement points to the first row in result set
     *
     * @var bool
     */
    private $firstRow;

    /**
     * Current row
     *
     * @var array
     */
    private $row;

    /**
     * Name of row key
     *
     * @var string
     */
    private $key;

    /**
     * Prototype model that will be cloned on current
     *
     * @var ModelInterface
     */
    private $prototype;

    /**
     * Construct and inject PDOStatement instance
     *
     * @param PDOStatement $stmt
     * @param string $key Name of column to use as key
     *
     * @param ModelInterface $proto Prototype model to clone on current
     */
    public function __construct(PDOStatement $stmt, $key, ModelInterface $proto)
    {
        assert('is_string($key)');
        $this->stmt = $stmt;
        $this->key = $key;
        $this->prototype = $proto;

        $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        $this->next();
        $this->firstRow = true;
    }

    /**
     * Re-execute PDOStatement to enable a new execution
     *
     * @return void
     */
    public function rewind()
    {
        if (!$this->firstRow) {
            $this->stmt->execute();
            $this->next();
        }
    }

    /**
     * Load next row from PDOStatement
     *
     * @return void
     */
    public function next()
    {
        $this->firstRow = false;
        $this->row = $this->stmt->fetch();
    }

    /**
     * Return current row
     *
     * @return ModelInterface
     */
    public function current()
    {
        $model = clone $this->prototype;
        $model->load($this->row);

        return $model;
    }

    /**
     * Return current id
     *
     * @return scalar
     */
    public function key()
    {
        $key = '';
        if (isset($this->row[$this->key])) {
            $key = $this->row[$this->key];
        }

        return $key;
    }

    /**
     * Check if current row is valid
     *
     * @return bool
     */
    public function valid()
    {
        return (boolean)$this->row;
    }
}
