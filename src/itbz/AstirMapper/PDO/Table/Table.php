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
namespace itbz\AstirMapper\PDO\Table;
use itbz\AstirMapper\Exception\TableException;
use itbz\AstirMapper\PDO\Search;
use PDO;
use PDOStatement;


/**
 *
 * PDO table for use by PDO models
 *
 * NOTE: Expects all database tables to have primary keys definied over only
 * one column. In other words PDO\Table can not handle tables with composite
 * primary keys.
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class Table
{

    /**
     *
     * PDO instance used
     *
     * @var PDO $_pdo
     *
     */
    protected $_pdo;


    /**
     *
     * Name of database table
     *
     * @var string $_name
     *
     */
    private $_name;
    

    /**
     *
     * Name of primary key
     *
     * @var string $_primaryKey
     *
     */
    private $_primaryKey;
    

    /**
     *
     * Array of columns in table
     *
     * @var string $_nativeColumns
     *
     */
    private $_nativeColumns;


    /**
     *
     * Associative array of columns in both native and joined tables. Column
     * names as keys. Column identfiers (`table`.`column`) as values.
     *
     * @var string $_columns
     *
     */
    private $_columns;


    /**
     *
     * Array of naturally joined Tables
     *
     * @var array $naturalJoins
     *
     */
    private $_naturalJoins = array();


    /**
     *
     * PDO table for use by PDO models 
     *
     * @param string $name Name of database table
     *
     * @param PDO $pdo PDO object for interacting with database
     *
     */
    public function __construct($name, PDO $pdo)
    {
        assert('is_string($name)');
        $this->_name = $name;
        $this->_pdo = $pdo;
    }


    /**
     *
     * Get name of table
     *
     * @return string
     *
     */ 
    public function getName()
    {
        return $this->_name;
    }


    /**
     *
     * Get identifier of base and joined tables
     *
     * @return string
     *
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
     *
     * Set primary key of table
     *
     * If this method is not called primary key will be reverse engineered
     * as necessary.
     *
     * @param string $key
     *
     * @throws TableException if key is not a native column in table
     *
     */
    public function setPrimaryKey($key)
    {
        assert('is_string($key)');
        if (!$this->isNativeColumn($key)) {
            $name = $this->getName();
            $msg = "Unable to set non-native primary key '$key' to '$name'";
            throw new TableException($msg);
        }
        $this->_primaryKey = $key;
    }


    /**
     *
     * Get primary key for this table
     *
     * @return string
     *
     */
    public function getPrimaryKey()
    {
        if (!isset($this->_primaryKey)) {
            $this->_primaryKey = $this->reverseEngineerPK();
        }
        
        return $this->_primaryKey;
    }


    /**
     *
     * Set native column names of table.
     *
     * If this method is not called native columns will be reverse engineered
     * as necessary.
     *
     * @param array $columns
     *
     */
    public function setColumns(array $columns)
    {
        $this->_nativeColumns = $columns;
    }


    /**
     *
     * Get array of columns native to this table
     *
     * @return array
     *
     */
    public function getNativeColumns()
    {
        if (!isset($this->_nativeColumns)) {
            $this->_nativeColumns = $this->reverseEngineerColumns();
        }
        
        return $this->_nativeColumns;
    }


    /**
     *
     * Get associative array of columns in native and joined tables
     *
     * Column names as keys. Column identfiers (`table`.`column`) as values.
     *
     * @return array
     *
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
     *
     * Check if column is native to table
     *
     * @param string $colname
     *
     * @return bool
     *
     */
    public function isNativeColumn($colname)
    {
        assert('is_string($colname)');
        $columns = $this->getNativeColumns();
        
        return in_array($colname, $columns);
    }


    /**
     *
     * Check if column exist in native or joined table
     *
     * @param string $colname
     *
     * @return bool
     *
     */
    public function isColumn($colname)
    {
        assert('is_string($colname)');
        $columns = $this->getColumns();

        return array_key_exists($colname, $columns);
    }


    /**
     *
     * Get full column identifier for column name
     *
     * @param string $colname Use regular column name with no backticks
     *
     * @return string
     *
     * @throw TableException if column does not exist
     *
     */
    public function getColumnIdentifier($colname)
    {
        if (!$this->isColumn($colname)) {
            $msg = "Column '$colname' does not exist in '{$this->getName()}'";
            throw new TableException($msg);
        }
        $columns = $this->getColumns();
        
        return $columns[$colname];
    }


    /**
     *
     * Add a naturally joined table
     *
     * @param Table $table
     *
     */
    public function addNaturalJoin(Table $table)
    {
        $this->_naturalJoins[] = $table;
        unset($this->_columns);
    }


    /**
     *
     * Reverse engineer structure of database table
     *
     * Defaults to an empty array. Override to implement real DB reverse
     * enginerring.
     *
     * @return array Array of column names native to table
     *
     */
    public function reverseEngineerColumns()
    {
        return array();
    }


    /**
     *
     * Reverse engineer primary key of database table
     *
     * Defaults to the first item in the native columns array. Override
     * to implement real DB reverse engineering
     *
     * @return string
     *
     */
    public function reverseEngineerPK()
    {
        $columns = $this->getNativeColumns();
        
        return isset($columns[0]) ? $columns[0] : '';
    }


    /**
     *
     * Select based on custom where clause
     *
     * @param Search $search
     *
     * @param array $where Array of sql contexts and values
     *
     * @return PDOStatement
     *
     */
    public function select(Search $search, array $where = array())
    {
        $columns = $search->getColumns();

        // Set columns
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
        $sWhere = '';
        if (!empty($where)) {
            $sWhere = " WHERE " . implode(' AND ', array_keys($where));
        }

        // Set order by
        $orderBy = $search->getOrderBy();
        if (!empty($orderBy)) {
            $orderBy = " ORDER BY {$this->getColumnIdentifier($orderBy)}";
            $orderBy .= " {$search->getDirection()}";
        }

        $query = $base . $sWhere . $orderBy . $search->getLimit();
        
        $stmt = $this->_pdo->prepare($query);
        $stmt->execute(array_values($where));
        
        return $stmt;
    }


    /**
     *
     * Insert based on custom set clause
     *
     * @param array $data Column names as keys. Data will be escaped and
     * enclosed in '. Column names will be onclosed in `.
     *
     * @return PDOStatement
     *
     */
    public function insert(array $data)
    {
        $columns = array();
        $values = array();
        foreach ( $data as $col => $val ) {
            $columns[] = "`$col`";
            $values[] = $this->_pdo->quote($val);
        }
        $columns = implode(',', $columns);
        $values = implode(',', $values);
        $query = "INSERT INTO `{$this->getName()}` ($columns) VALUES ($values)";

        return $this->_pdo->query($query);
    }


    /**
     *
     * Get the ID of the last inserted row.
     *
     * The return value will only be meaningful on tables with an auto-increment
     * field and with a PDO driver that supports auto-increment. NOTE: must be
     * called directly after a database insert.
     *
     * @return int
     *
     */
    public function lastInsertId()
    {
        return intval($this->_pdo->lastInsertId());
    }


    /**
     *
     * Update based on custom set and where clauses
     *
     * @param array $data Column names as keys. Data will be escaped and
     * enclosed in '. Column names will be onclosed in `.
     *
     * @param array $where Array of sql contexts and values
     *
     * @return PDOStatement
     *
     * @throws TableException if where is empty
     *
     */
    public function update(array $data, array $where)
    {
        if (empty($where)) {
            $msg = "Unable to delete from empty where clause";
            throw new TableException($msg);
        }

        $values = array();
        foreach ( $data as $col => $val ) {
            $values[] = "`$col`=" . $this->_pdo->quote($val);
        }
        $values = implode(',', $values);

        $sWhere = implode(' AND ', array_keys($where));
        $query = "UPDATE `{$this->getName()}` SET $values WHERE $sWhere";
        $stmt = $this->_pdo->prepare($query);
        $stmt->execute(array_values($where));

        return $stmt;
    }


    /**
     *
     * Delete based on custom where clause
     *
     * Sql contexts should be in the form '`id` = ?' or '`id` > ?.
     *
     * @param array $where Array of sql contexts and values
     *
     * @return PDOStatement
     *
     * @throws TableException if where is empty
     *
     */
    public function delete(array $where)
    {
        if (empty($where)) {
            $msg = "Unable to delete from empty where clause";
            throw new TableException($msg);
        }
        $sWhere = implode(' AND ', array_keys($where));
        $query = "DELETE FROM `{$this->getName()}` WHERE $sWhere";
        $stmt = $this->_pdo->prepare($query);
        $stmt->execute(array_values($where));

        return $stmt;
    }

}
