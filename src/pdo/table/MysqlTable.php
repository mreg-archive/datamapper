<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper\pdo\table;

/**
 * PDO table for use with MySQL
 *
 * Extends Table by adding MySQL reverse engineering capabilities. If you do
 * not need to reverse egnigeer column names and primay keys of tables use
 * the regular Table class instead.
 *
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
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
