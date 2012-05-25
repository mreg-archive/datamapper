<?php
/**
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
 */
namespace itbz\AstirMapper\PDO;
use itbz\AstirMapper\ModelInterface;
use PDO;
use PDOStatement;


/**
 * Iterates over rows in a PDOStatement returning Model instances
 *
 * PDOStatements are iterable as is. This class adds support for rewinds and
 * key retrieval. Also each result row is returned as a Model and not an array.
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 */
class Iterator implements \Iterator
{

    /**
     * PDO statement to iterate
     *
     * @var PDOStatement
     */
    private $_stmt;


    /**
     * True if PDOStatement points to the first row in result set
     *
     * @var bool
     */
    private $_firstRow;


    /**
     * Current row
     *
     * @var array
     */
    private $_row;


    /**
     * Name of row key
     *
     * @var string
     */
    private $_key;


    /**
     * Prototype model that will be cloned on current
     *
     * @var ModelInterface
     */
    private $_prototype;


    /**
     * Construct and inject PDOStatement instance
     *
     * @param PDOStatement $stmt
     *
     * @param string $key Name of column to use as key
     *
     * @param ModelInterface $proto Prototype model to clone on current
     */
    public function __construct(PDOStatement $stmt, $key, ModelInterface $proto)
    {
        assert('is_string($key)');
        $this->_stmt = $stmt;
        $this->_key = $key;
        $this->_prototype = $proto;

        $this->_stmt->setFetchMode(PDO::FETCH_ASSOC);
        $this->next();
        $this->_firstRow = TRUE;
    } 


    /**
     * Re-execute PDOStatement to enable a new execution
     *
     * @return void
     */
    public function rewind()
    {
        if (!$this->_firstRow) {
            $this->_stmt->execute();
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
        $this->_firstRow = FALSE;
        $this->_row = $this->_stmt->fetch();
    }


    /**
     * Return current row
     *
     * @return ModelInterface
     */
    public function current()
    {
        $model = clone $this->_prototype;
        $model->load($this->_row);

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
        if (isset($this->_row[$this->_key])) {
            $key = $this->_row[$this->_key];
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
        return (boolean)$this->_row;
    }

}
