<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper\pdo\access;

use datamapper\pdo\Expression;

/**
 * Expression for row based access control
 *
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
 */
class Mode extends Expression implements AccessInterface
{
    /**
     * Create access constraint
     *
     * @param string $action 'r' for read, 'w' for write or 'x' for execute
     * @param string $table Name of table access is being checked on
     * @param string $uname Name of user
     * @param array $ugroups List of groups user belongs to
     * @throws Exception if action is invalid
     */
    public function __construct($action, $table, $uname, array $ugroups)
    {
        assert('is_string($action)');
        assert('is_string($table)');
        assert('is_string($uname)');

        if (!in_array($action, array('r', 'w', 'x'))) {
            $msg = "Invalid action '$action'. Use 'r', 'w' or 'x'.";
            throw new Exception($msg);
        }

        $ugroups = implode(',', $ugroups);

        $expr = array();
        $expr[] = "'$action'";
        $expr[] = "`$table`.`" . self::OWNER_FIELD . "`";
        $expr[] = "`$table`.`" . self::GROUP_FIELD . "`";
        $expr[] = "`$table`.`" . self::MODE_FIELD . "`";
        $expr[] = "'$uname'";
        $expr[] = "'$ugroups'";

        $expr = implode(',', $expr);
        $expr = "isAllowed($expr)";

        parent::__construct('1', $expr);

        $this->setEscapeName(false);
        $this->setEscapeValue(false);
    }
}
