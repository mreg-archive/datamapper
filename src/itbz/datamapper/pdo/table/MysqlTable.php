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
 * @package datamapper\pdo\table
 */

namespace itbz\datamapper\pdo\table;

/**
 * pdo table for use with MySQL
 *
 * Extends Table by adding MySQL reverse engineering capabilities. If you do
 * not need to reverse egnigeer column names and primay keys of tables use
 * the regular Table class instead.
 *
 * @package datamapper\pdo\table
 */
class MysqlTable extends Table
{
    /**
     * Reverse engineer structure of database table
     *
     * @return array Array of column names native to table
     */
    public function reverseEngineerColumns()
    {
        $query = "DESCRIBE `{$this->getName()}`";
        $statement = $this->pdo->query($query);
        $columns = array();
        while ( $col = $statement->fetchColumn() ) {
            $columns[] = $col;
        }

        return $columns;
    }

    /**
     * Reverse engineer primary key of database table
     *
     * @return string
     */
    public function reverseEngineerPK()
    {
        $q = "SHOW INDEX FROM `{$this->getName()}` WHERE Key_name='PRIMARY'";
        $stmt = $this->pdo->query($q);
        $key = '';
        while ($col = $stmt->fetchColumn(4)) {
            $key = $col;
        }

        return $key;
    }
}
