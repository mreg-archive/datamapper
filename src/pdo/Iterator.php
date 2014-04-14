<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper\pdo;

use datamapper\ModelInterface;
use pdo;
use pdoStatement;

/**
 * Iterates over rows in a pdoStatement returning Model instances
 *
 * pdoStatements are iterable as is. This class adds support for rewinds and
 * key retrieval. Also each result row is returned as a Model and not an array.
 *
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
 */
class Iterator implements \Iterator
{
    /**
     * @var pdoStatement PDO statement to iterate
     */
    private $stmt;

    /**
     * @var bool True if pdoStatement points to the first row in result set
     */
    private $firstRow;

    /**
     * @var array Current row
     */
    private $row;

    /**
     * @var string Name of row key
     */
    private $key;

    /**
     * @var ModelInterface Prototype model that will be cloned on current
     */
    private $prototype;

    /**
     * Construct and inject pdoStatement instance
     *
     * @param pdoStatement $stmt
     * @param string $key Name of column to use as key
     * @param ModelInterface $proto Prototype model to clone on current
     */
    public function __construct(pdoStatement $stmt, $key, ModelInterface $proto)
    {
        assert('is_string($key)');
        $this->stmt = $stmt;
        $this->key = $key;
        $this->prototype = $proto;

        $this->stmt->setFetchMode(pdo::FETCH_ASSOC);
        $this->next();
        $this->firstRow = true;
    }

    /**
     * Re-execute pdoStatement to enable a new execution
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
     * Load next row from pdoStatement
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
