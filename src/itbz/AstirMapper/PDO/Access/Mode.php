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
 * @subpackage PDO\Access
 *
 */
namespace itbz\AstirMapper\PDO\Access;
use itbz\AstirMapper\PDO\Attribute;
use itbz\AstirMapper\Exception\PdoException;


/**
 *
 * Attribute for row based access control
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Access
 *
 */
class Mode extends Attribute
{

    /**
     *
     * Consruct and set mode
     *
     * @param string $action 'r' for read, 'w' for write or 'x' for execute
     *
     * @param string $table Name of table access is being checked on
     *
     * @param string $uname Name of user
     *
     * @param array $ugroups List of groups user belongs to
     *
     * @throws PdoException if action is invalid
     *
     */
    public function __construct($action, $table, $uname, array $ugroups)
    {
        assert('is_string($action)');
        assert('is_string($table)');
        assert('is_string($uname)');

        if (!in_array($action, array('r', 'w', 'x'))) {
            $msg = "Invalid action '$action'. Use 'r', 'w' or 'x'.";
            throw new PdoException($msg);
        }

        $ugroups = implode(',', $ugroups);

        $expr = array();
        $expr[] = "'$action'";
        $expr[] = "`$table`.`owner`";
        $expr[] = "`$table`.`group`";
        $expr[] = "`$table`.`mode`";
        $expr[] = "'$uname'";
        $expr[] = "'$ugroups'";

        $expr = implode(',', $expr);        
        $expr = "isAllowed($expr)";

        parent::__construct('', $expr);
        $this->_escape = FALSE;
    }

}
