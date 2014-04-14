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
 * PDO table for use with SQLite
 *
 * Extends Table by adding SQLite reverse engineering capabilities. If you do
 * not need to reverse egnigeer column names and primay keys of tables use
 * the regular Table class instead.
 *
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
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
        $stmt = $this->pdo->query($query);
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
        $stmt = $this->pdo->query($query);
        $key = '';
        while ($col = $stmt->fetch()) {
            if ($col['pk'] == '1') {
                $key = $col['name'];
            }
        }

        return $key;
    }
}
