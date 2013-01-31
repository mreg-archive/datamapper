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
 * @package datamapper\pdo\access
 */

namespace iio\datamapper\pdo\access;

use iio\datamapper\pdo\Expression;

/**
 * Expression for row based access control
 *
 * @package datamapper\pdo\access
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
     *
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
