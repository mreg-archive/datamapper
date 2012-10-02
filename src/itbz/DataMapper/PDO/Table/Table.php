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
 * @package DataMapper\PDO\Table
 */

namespace itbz\DataMapper\PDO\Table;

use itbz\DataMapper\Exception\PdoException;
use itbz\DataMapper\PDO\Search;
use itbz\DataMapper\PDO\ExpressionSet;
use PDO;
use PDOStatement;

/**
 * PDO table for use by PDO models
 *
 * NOTE: Expects all database tables to have primary keys definied over only
 * one column. In other words PDO\Table can not handle tables with composite
 * primary keys.
 *
 * @package DataMapper\PDO\Table
 */
class Table
{
    /**
     * PDO instance used
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Name of database table
     *
     * @var string
     */
    private $name;

    /**
     * Name of primary key
     *
     * @var string
     */
    private $primaryKey;

    /**
     * Array of columns in table
     *
     * @var string
     */
    private $nativeColumns;

    /**
     * Associative array of column names
     *
     * Includes columns in both native and joined tables. Names as keys,
     * identfiers (`table`.`column`) as values.
     *
     * @var string
     */
    private $columns;

    /**
     * Array of naturally joined Tables
     *
     * @var array
     */
    private $naturalJoins = array();

    /**
     * PDO table for use by PDO models 
     *
     * @param string $name Name of database table
     * @param PDO $pdo PDO object for interacting with database
     */
    public function __construct($name, PDO $pdo)
    {
        assert('is_string($name)');
        $this->name = $name;
        $this->pdo = $pdo;
    }

    /**
     * Get name of table
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get identifier of base and joined tables
     *
     * @return string
     */
    public function getTableIdentifier()
    {
        $name = "`{$this->getName()}`";
        foreach ($this->naturalJoins as $joinTable) {
            $name .= " NATURAL LEFT JOIN {$joinTable->getTableIdentifier()}";
        }

        return $name;
    }

    /**
     * Set primary key of table
     *
     * If this method is not called primary key will be reverse engineered
     * as necessary.
     *
     * @param string $key
     *
     * @return void
     *
     * @throws PdoException if key is not a native column in table
     *
     */
    public function setPrimaryKey($key)
    {
        assert('is_string($key)');
        if (!$this->isNativeColumn($key)) {
            $name = $this->getName();
            $msg = "Unable to set non-native primary key '$key' to '$name'";
            throw new PdoException($msg);
        }
        $this->primaryKey = $key;
    }

    /**
     * Get primary key for this table
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        if (!isset($this->primaryKey)) {
            $this->primaryKey = $this->reverseEngineerPK();
        }

        return $this->primaryKey;
    }

    /**
     * Set native column names of table.
     *
     * If this method is not called native columns will be reverse engineered
     * as necessary.
     *
     * @param array $columns
     *
     * @return void
     */
    public function setColumns(array $columns)
    {
        $this->nativeColumns = $columns;
    }

    /**
     * Get array of columns native to this table
     *
     * @return array
     */
    public function getNativeColumns()
    {
        if (!isset($this->nativeColumns)) {
            $this->nativeColumns = $this->reverseEngineerColumns();
        }

        return $this->nativeColumns;
    }

    /**
     * Get associative array of columns in native and joined tables
     *
     * Column names as keys. Column identfiers (`table`.`column`) as values.
     *
     * @return array
     */
    public function getColumns()
    {
        if (!isset($this->columns)) {
            // Add native columns
            $this->columns = array();
            $nativeCols = $this->getNativeColumns();
            $nativeName = $this->getName();
            foreach ($nativeCols as $colname) {
                $this->columns[$colname] = "`$nativeName`.`$colname`";
            }
            // Add joined columns
            foreach ($this->naturalJoins as $joinTable) {
                $joinColumns = $joinTable->getColumns();
                $this->columns = array_merge($this->columns, $joinColumns);
            }
        }

        return $this->columns;
    }

    /**
     * Check if column is native to table
     *
     * @param string $colname
     *
     * @return bool
     */
    public function isNativeColumn($colname)
    {
        assert('is_string($colname)');
        $columns = $this->getNativeColumns();

        return in_array($colname, $columns);
    }

    /**
     * Check if column exist in native or joined table
     *
     * @param string $colname
     *
     * @return bool
     */
    public function isColumn($colname)
    {
        assert('is_string($colname)');
        $columns = $this->getColumns();

        return array_key_exists($colname, $columns);
    }

    /**
     * Get full column identifier for column name
     *
     * @param string $colname Use regular column name with no backticks
     *
     * @return string
     *
     * @throws PdoException if column does not exist
     */
    public function getColumnIdentifier($colname)
    {
        if (!$this->isColumn($colname)) {
            $msg = "Column '$colname' does not exist in '{$this->getName()}'";
            throw new PdoException($msg);
        }
        $columns = $this->getColumns();

        return $columns[$colname];
    }

    /**
     * Add a naturally joined table
     *
     * @param Table $table
     *
     * @return void
     */
    public function addNaturalJoin(Table $table)
    {
        $this->naturalJoins[] = $table;
        unset($this->columns);
    }

    /**
     * Get array of joined Table objects
     *
     * @return array
     */
    public function getJoins()
    {
        return $this->naturalJoins;
    }

    /**
     * Reverse engineer structure of database table
     *
     * Defaults to an empty array. Override to implement real DB reverse
     * enginerring.
     *
     * @return array Array of column names native to table
     */
    public function reverseEngineerColumns()
    {
        return array();
    }

    /**
     * Reverse engineer primary key of database table
     *
     * Defaults to the first item in the native columns array. Override
     * to implement real DB reverse engineering
     *
     * @return string
     */
    public function reverseEngineerPK()
    {
        $columns = $this->getNativeColumns();

        return isset($columns[0]) ? $columns[0] : '';
    }

    /**
     * Select rows from db
     *
     * @param Search $search
     * @param ExpressionSet $conditions
     *
     * @return PDOStatement
     */
    public function select(Search $search, ExpressionSet $conditions = null)
    {
        $columns = $search->getColumns();

        // Set select columns
        if (empty($columns)) {
            $select = '*';
        } else {
            foreach ($columns as &$col) {
                $col = $this->getColumnIdentifier($col);
            }
            $select = implode(', ', $columns);
        }

        // Set base select query
        $base = "SELECT $select FROM " . $this->getTableIdentifier();

        // Set where
        if ($conditions) {
            list($where, $whereValues) = $conditions->buildWhereClause();
        } else {
            $where = '';
            $whereValues = array();
        }

        // Set order by
        $orderBy = $search->getOrderBy();
        if (!empty($orderBy)) {
            $orderBy = " ORDER BY {$this->getColumnIdentifier($orderBy)}";
            $orderBy .= " {$search->getDirection()}";
        }

        $query = sprintf(
            '%s %s %s %s',
            $base,
            $where,
            $orderBy,
            $search->getLimitClause()
        );

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($whereValues);

        return $stmt;
    }

    /**
     * Insert values into db
     *
     * @param ExpressionSet $data
     *
     * @return PDOStatement
     *
     * @throws PdoException if data is empty
     */
    public function insert(ExpressionSet $data)
    {
        if ($data->isEmpty()) {
            $msg = "Unable to insert with no values";
            throw new PdoException($msg);
        }
        list($columns, $exprs, $values) = $data->buildDataList();
        $query = "INSERT INTO `{$this->getName()}` ($columns) VALUES ($exprs)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return $stmt;
    }

    /**
     * Get the ID of the last inserted row.
     *
     * The return value will only be meaningful on tables with an auto-increment
     * field and with a PDO driver that supports auto-increment. NOTE: must be
     * called directly after a database insert.
     *
     * @return int
     */
    public function lastInsertId()
    {
        return intval($this->pdo->lastInsertId());
    }

    /**
     * Update db based on conditions
     *
     * @param ExpressionSet $data
     * @param ExpressionSet $conditions
     *
     * @return PDOStatement
     *
     * @throws PdoException if conditions or data are empty
     */
    public function update(ExpressionSet $data, ExpressionSet $conditions)
    {
        if ($data->isEmpty()) {
            $msg = "Unable to update with no values";
            throw new PdoException($msg);
        }
        if ($conditions->isEmpty()) {
            $msg = "Unable to update from empty where clause";
            throw new PdoException($msg);
        }

        list($set, $setValues) = $data->buildSetStatement();
        list($where, $whereValues) = $conditions->buildWhereClause();

        $values = array_merge($setValues, $whereValues);

        $query = "UPDATE `{$this->getName()}` $set $where";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return $stmt;
    }

    /**
     * Delete records from db that matches conditions
     *
     * @param ExpressionSet $conditions
     *
     * @return PDOStatement
     *
     * @throws PdoException if conditions are empty
     */
    public function delete(ExpressionSet $conditions)
    {
        if ($conditions->isEmpty()) {
            $msg = "Unable to delete from empty where clause";
            throw new PdoException($msg);
        }
        list($where, $values) = $conditions->buildWhereClause();
        $query = "DELETE FROM `{$this->getName()}` $where";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return $stmt;
    }
}
