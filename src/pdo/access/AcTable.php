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

use iio\datamapper\pdo\table\MysqlTable;
use iio\datamapper\pdo\Search;
use iio\datamapper\pdo\ExpressionSet;
use iio\datamapper\pdo\Expression;
use pdo;
use pdoStatement;

/**
 * pdo access control table object
 *
 * @package datamapper\pdo\access
 */
class AcTable extends MysqlTable implements AccessInterface
{
    /**
     * Name of owner of table
     *
     * @var string
     */
    private $owner;

    /**
     * Name of group of table
     *
     * @var string
     */
    private $group;

    /**
     * Access mode of table
     *
     * @var int
     */
    private $mode;

    /**
     * Name of user
     *
     * @var string
     */
    private $uname = '';

    /**
     * List of groups user belongs to
     *
     * @var array
     */
    private $ugroups = array();

    /**
     * Set access restrictions for table
     *
     * Use access flags to set mode. AcTable::OWNER_ALL|AcTable::GROUP_ALL
     * gives full access to owner and group. For more compact code use octal
     * notation. '0700' restrict access to owner only. '0777' allows global
     * access.
     *
     * @param string $name Name of database table
     * @param pdo $pdo pdo object for interacting with database
     * @param string $owner Name of owner of table
     * @param string $group Name of group of table
     * @param int $mode Access mode of table
     */
    public function __construct($name, pdo $pdo, $owner, $group, $mode = 0770)
    {
        assert('is_string($owner)');
        assert('is_string($group)');
        assert('is_int($mode)');

        parent::__construct($name, $pdo);

        $this->owner = $owner;
        $this->group = $group;
        $this->mode = $mode;
    }

    /**
     * Select rows from db
     *
     * @param Search $search
     * @param ExpressionSet $conditions
     *
     * @return pdoStatement
     *
     * @throws AccessDeniedException if user does not have access
     */
    public function select(Search $search, ExpressionSet $conditions = null)
    {
        if (!$this->isAllowedExecute()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        if (!$conditions) {
            $conditions = new ExpressionSet();
        }

        // Add mode constraint to conditions
        $conditions->addExpression(
            new Mode(
                'r',
                $this->getName(),
                $this->uname,
                $this->ugroups
            )
        );

        return $this->forwardValidStmt(
            'selecting from',
            parent::select($search, $conditions),
            $conditions,
            $search
        );
    }

    /**
     * Delete records from db that matches conditions
     *
     * @param ExpressionSet $conditions
     *
     * @return pdoStatement
     *
     * @throws AccessDeniedException if user does not have access
     */
    public function delete(ExpressionSet $conditions)
    {
        if (!$this->isAllowedWrite()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        // Add mode constraint to conditions
        $conditions->addExpression(
            new Mode(
                'w',
                $this->getName(),
                $this->uname,
                $this->ugroups
            )
        );

        return $this->forwardValidStmt(
            'deleting',
            parent::delete($conditions),
            $conditions
        );
    }

    /**
     * Insert values into db
     *
     * @param ExpressionSet $data
     *
     * @return pdoStatement
     *
     * @throws AccessDeniedException if user does not have access
     */
    public function insert(ExpressionSet $data)
    {
        if (!$this->isAllowedWrite()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        $defaultGroup = isset($this->ugroups[0]) ? $this->ugroups[0] : '';
        $defaults = array(
            self::OWNER_FIELD => $this->uname,
            self::GROUP_FIELD => $defaultGroup,
            self::MODE_FIELD => 0770
        );

        foreach ($defaults as $key => $value) {
            if (!$data->isExpression($key)) {
                $data->addExpression(
                    new Expression($key, $value)
                );
            }
        }

        return parent::insert($data);
    }

    /**
     * Update db based on conditions
     *
     * @param ExpressionSet $data
     * @param ExpressionSet $conditions
     *
     * @return pdoStatement
     *
     * @throws AccessDeniedException if user does not have access
     */
    public function update(ExpressionSet $data, ExpressionSet $conditions)
    {
        if (!$this->isAllowedExecute()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        // Add mode constraint to conditions
        $conditions->addExpression(
            new Mode(
                'w',
                $this->getName(),
                $this->uname,
                $this->ugroups
            )
        );

        return $this->forwardValidStmt(
            'updating',
            parent::update($data, $conditions),
            $conditions
        );
    }

    /**
     * Check if user is allowed to read table
     *
     * @return bool
     */
    public function isAllowedRead()
    {
        return $this->isAllowedTable('r');
    }

    /**
     * Check if user is allowed to write to table
     *
     * @return bool
     */
    public function isAllowedWrite()
    {
        return $this->isAllowedTable('w');
    }

    /**
     * Check if user is allowed to execute table
     *
     * @return bool
     */
    public function isAllowedExecute()
    {
        return $this->isAllowedTable('x');
    }

    /**
     * Set info about current user
     *
     * @param string $uname Name of user
     * @param array $ugroups List of groups user belongs to
     *
     * @return void
     */
    public function setUser($uname, array $ugroups = array())
    {
        assert('is_string($uname)');
        $this->uname = $uname;
        $this->ugroups = $ugroups;

        // Recursively set user on joined AcTables
        foreach ($this->getJoins() as $table) {
            if ($table instanceof AcTable) {
                $table->setUser($uname, $ugroups);
            }
        }
    }

    /**
     * Get name of current user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->uname;
    }

    /**
     * Get groups of current user
     *
     * @return array
     */
    public function getUserGroups()
    {
        return $this->ugroups;
    }

    /**
     * Check if user is root
     *
     * The root user always have access. Roots are all users named 'root' or
     * belonging to a group named 'root'.
     *
     * @return bool
     */
    public function userIsRoot()
    {
        return $this->uname == 'root' || in_array('root', $this->ugroups);
    }

    /**
     * Check if user owns table
     *
     * @return bool
     */
    public function userIsOwner()
    {
        return ($this->owner == $this->uname);
    }

    /**
     * Check if user is in group that owns table
     *
     * @return bool
     */
    public function userIsGroup()
    {
        return in_array($this->group, $this->ugroups);
    }

    /**
     * Check if user is allowed to perform action on table
     *
     * @param string $action 'r', 'w' or 'x'
     *
     * @return bool
     */
    private function isAllowedTable($action)
    {
        // Recursively check permissions on joined AcTables
        foreach ($this->getJoins() as $table) {
            if (
                $table instanceof AcTable
                && !$table->isAllowedTable($action)
            ) {

                return false;
            }
        }

        if ($this->userIsRoot()) {

            return true;
        }

        $masks = array(
            'r' => 4,
            'w' => 2,
            'x' => 1,
        );

        $mask = $masks[$action];

        if ($this->userIsOwner()) {
            $mask = $mask << 6;
        } elseif ($this->userIsGroup()) {
            $mask = $mask << 3;
        }

        return (($this->mode & $mask) == $mask);
    }

    /**
     * Validate row access for empty statements
     *
     * Checks if statment is empty (using pdoStamtement::rowCount, hence this
     * is not supported with all database drivers if the last executed query in
     * statement was a select). If statement is empty valuate if this is due to
     * access restrictions. If so throw exception. Else return statement as is.
     *
     * @param string $verb Action description to be inserted in exception
     * message
     * @param pdoStatement $stmt The statement to evaluate
     * @param ExpressionSet $conditions Conditions used when creating statement
     * @param Search $search Search clause used when creating statement, if any
     *
     * @return pdoStatement
     *
     * @throws AccessDeniedException if statement is empty due to access
     * restrictions att row level
     */
    private function forwardValidStmt(
        $verb,
        pdoStatement $stmt,
        ExpressionSet $conditions,
        Search $search = null
    ) {
        assert('is_string($verb)');

        if (!$stmt->rowCount()) {
            if (!$search) {
                $search = new Search();
            }
            $conditions->popExpression();
            $fullAccessStmt = parent::select($search, $conditions);
            if ($fullAccessStmt->rowCount()) {
                // Access restricted, build exception message
                $pk = $this->getPrimaryKey();
                $row = '';
                if ($conditions->isExpression($pk)) {
                    $row = $conditions->getExpression($pk)->getValue();
                    $row = "at row '$row'";
                }
                $msg = "Access denied $verb table '{$this->getName()}' $row";
                throw new AccessDeniedException($msg);
            }
        }

        return $stmt;
    }
}
