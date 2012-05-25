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
 * @subpackage PDO\Table
 */
namespace itbz\AstirMapper\PDO\Table;
use itbz\AstirMapper\Exception\PdoException;
use itbz\AstirMapper\PDO\Search;
use itbz\AstirMapper\PDO\ExpressionSet;
use PDO;
use PDOStatement;


/**
 * PDO table for use by PDO models
 *
 * NOTE: Expects all database tables to have primary keys definied over only
 * one column. In other words PDO\Table can not handle tables with composite
 * primary keys.
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Table
 */
class Table
{

    /**
     * PDO instance used
     *
     * @var PDO
     */
    protected $_pdo;


    /**
     * Name of database table
     *
     * @var string
     */
    private $_name;
    

    /**
     * Name of primary key
     *
     * @var string
     */
    private $_primaryKey;
    

    /**
     * Array of columns in table
     *
     * @var string
     */
    private $_nativeColumns;


    /**
     * Associative array of column names
     *
     * Includes columns in both native and joined tables. Names as keys,
     * identfiers (`table`.`column`) as values.
     *
     * @var string
     */
    private $_columns;


    /**
     * Array of naturally joined Tables
     *
     * @var array
     */
    private $_naturalJoins = array();


    /**
     * PDO table for use by PDO models 
     *
     * @param string $name Name of database table
     *
     * @param PDO $pdo PDO object for interacting with database
     */
    public function __construct($name, PDO $pdo)
    {
        assert('is_string($name)');
        $this->_name = $name;
        $this->_pdo = $pdo;
    }


    /**
     * Get name of table
     *
     * @return string
     */ 
    public function getName()
    {
        return $this->_name;
    }


    /**
     * Get identifier of base and joined tables
     *
     * @return string
     */
    public function getTableIdentifier()
    {
        $name = "`{$this->getName()}`";
        foreach ($this->_naturalJoins as $joinTable) {
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
        $this->_primaryKey = $key;
    }


    /**
     * Get primary key for this table
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        if (!isset($this->_primaryKey)) {
            $this->_primaryKey = $this->reverseEngineerPK();
        }
        
        return $this->_primaryKey;
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
        $this->_nativeColumns = $columns;
    }


    /**
     * Get array of columns native to this table
     *
     * @return array
     */
    public function getNativeColumns()
    {
        if (!isset($this->_nativeColumns)) {
            $this->_nativeColumns = $this->reverseEngineerColumns();
        }
        
        return $this->_nativeColumns;
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
        if (!isset($this->_columns)) {
            // Add native columns
            $this->_columns = array();
            $nativeCols = $this->getNativeColumns();
            $nativeName = $this->getName();
            foreach ($nativeCols as $colname) {
                $this->_columns[$colname] = "`$nativeName`.`$colname`";
            }
            // Add joined columns
            foreach ($this->_naturalJoins as $joinTable) {
                $joinColumns = $joinTable->getColumns();
                $this->_columns = array_merge($this->_columns, $joinColumns);
            }
        }
        
        return $this->_columns;
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
     * @throw PdoException if column does not exist
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
        $this->_naturalJoins[] = $table;
        unset($this->_columns);
    }


    /**
     * Get array of joined Table objects
     *
     * @return array
     */
    public function getJoins()
    {
        return $this->_naturalJoins;
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
     *
     * @param ExpressionSet $where
     *
     * @return PDOStatement
     */
    public function select(Search $search, ExpressionSet $where = NULL)
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
        if ($where) {
            list($whereClause, $whereValues) = $where->buildWhereClause();
        } else {
            $whereClause = '';
            $whereValues = array();
        }

        // Set order by
        $orderBy = $search->getOrderBy();
        if (!empty($orderBy)) {
            $orderBy = " ORDER BY {$this->getColumnIdentifier($orderBy)}";
            $orderBy .= " {$search->getDirection()}";
        }

        $query = $base . $whereClause . $orderBy . ' ' . $search->getLimit();
        
        $stmt = $this->_pdo->prepare($query);
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

        $stmt = $this->_pdo->prepare($query);
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
        return intval($this->_pdo->lastInsertId());
    }


    /**
     * Update db based on where clauses
     *
     * @param ExpressionSet $data
     *
     * @param ExpressionSet $where
     *
     * @return PDOStatement
     *
     * @throws PdoException if where or data is empty
     */
    public function update(ExpressionSet $data, ExpressionSet $where)
    {
        if ($data->isEmpty()) {
            $msg = "Unable to update with no values";
            throw new PdoException($msg);
        }
        if ($where->isEmpty()) {
            $msg = "Unable to update from empty where clause";
            throw new PdoException($msg);
        }

        list($set, $setValues) = $data->buildSetStatement();
        list($whereClause, $whereValues) = $where->buildWhereClause();

        $values = array_merge($setValues, $whereValues);

        $query = "UPDATE `{$this->getName()}` $set $whereClause";
        $stmt = $this->_pdo->prepare($query);
        $stmt->execute($values);

        return $stmt;
    }


    /**
     * Delete records from db that matches where
     *
     * @param ExpressionSet $where
     *
     * @return PDOStatement
     *
     * @throws PdoException if where is empty
     */
    public function delete(ExpressionSet $where)
    {
        if ($where->isEmpty()) {
            $msg = "Unable to delete from empty where clause";
            throw new PdoException($msg);
        }
        list($clause, $values) = $where->buildWhereClause();
        $query = "DELETE FROM `{$this->getName()}` $clause";
        $stmt = $this->_pdo->prepare($query);
        $stmt->execute($values);

        return $stmt;
    }

}
