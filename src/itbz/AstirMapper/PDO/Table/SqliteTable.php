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


/**
 * PDO table for use with SQLite
 *
 * Extends Table by adding SQLite reverse engineering capabilities. If you do
 * not need to reverse egnigeer column names and primay keys of tables use
 * the regular Table class instead.
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Table
 */
class SqliteTable extends Table
{

    /**
     * Reverse engineer structure of database table
     *
     * @return array Array of column names native to table
     */
    public function reverseEngineerColumns()
    {
        $query = "PRAGMA table_info(`{$this->getName()}`)";
        $stmt = $this->_pdo->query($query);
        $columns = array();
        while ($col = $stmt->fetchColumn(1)) {
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
        $query = "PRAGMA table_info(`{$this->getName()}`)";
        $stmt = $this->_pdo->query($query);
        $key = '';
        while ($col = $stmt->fetch()) {
            if ($col['pk'] == '1') {
                $key = $col['name'];
            }
        }

        return $key;
    }

}
