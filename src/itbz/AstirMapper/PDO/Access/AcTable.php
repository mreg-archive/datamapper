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
use itbz\AstirMapper\PDO\Table\MysqlTable;
use itbz\AstirMapper\PDO\Search;
use itbz\AstirMapper\PDO\ExpressionSet;
use itbz\AstirMapper\PDO\Expression;
use PDO;
use PDOStatement;


/**
 *
 * PDO access control table object
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Access
 *
 */
class AcTable extends MysqlTable implements AccessInterface
{

    /**
     *
     * Name of owner of table
     *
     * @var string $owner
     *
     */
    private $_owner;


    /**
     *
     * Name of group of table
     *
     * @var string $_group
     *
     */
    private $_group;


    /**
     *
     * Access mode of table
     *
     * @var int $_mode
     *
     */
    private $_mode;


    /**
     *
     * Name of user
     *
     * @var string $_uname
     *
     */
    private $_uname = '';
    

    /**
     *
     * List of groups user belongs to
     *
     * @var array $_ugroups
     *
     */
    private $_ugroups = array();


    /**
     *
     * Set access restrictions for table
     *
     * Use access flags to set mode. AcTable::OWNER_ALL|AcTable::GROUP_ALL
     * gives full access to owner and group. For more compact code use octal
     * notation. '0700' restrict access to owner only. '0777' allows global
     * access.
     *
     * @param string $name Name of database table
     *
     * @param PDO $pdo PDO object for interacting with database
     *
     * @param string $owner Name of owner of table
     *
     * @param string $group Name of group of table
     *
     * @param int $mode Access mode of table
     *
     */
    public function __construct($name, PDO $pdo, $owner, $group, $mode = 0770)
    {
        assert('is_string($owner)');
        assert('is_string($group)');
        assert('is_int($mode)');

        parent::__construct($name, $pdo);

        $this->_owner = $owner;
        $this->_group = $group;
        $this->_mode = $mode;
    }


    /**
     *
     * Select rows from db
     *
     * @param Search $search
     *
     * @param ExpressionSet $where
     *
     * @return PDOStatement
     *
     * @throws AccessDeniedException if user does not have access
     *
     */
    public function select(Search $search, ExpressionSet $where = NULL)
    {
        if (!$this->isAllowedExecute()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        if (!$where) {
            $where = new ExpressionSet();
        }

        // Add mode constraint to where clause
        $where->addExpression(
            new Mode(
                'r',
                $this->getName(),
                $this->_uname,
                $this->_ugroups
            )
        );

        return $this->forwardValidStmt(
            'selecting from',
            parent::select($search, $where),
            $where,
            $search
        );
    }


    /**
     *
     * Delete records from db that matches where
     *
     * @param ExpressionSet $where
     *
     * @return PDOStatement
     *
     * @throws AccessDeniedException if user does not have access
     *
     */
    public function delete(ExpressionSet $where)
    {
        if (!$this->isAllowedWrite()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        // Add mode constraint to where clause
        $where->addExpression(
            new Mode(
                'w',
                $this->getName(),
                $this->_uname,
                $this->_ugroups
            )
        );

        return $this->forwardValidStmt(
            'deleting',
            parent::delete($where),
            $where
        );
    }


    /**
     *
     * Insert values into db
     *
     * @param ExpressionSet $data
     *
     * @return PDOStatement
     *
     * @throws AccessDeniedException if user does not have access
     *
     */
    public function insert(ExpressionSet $data)
    {
        if (!$this->isAllowedWrite()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        $defaultGroup = isset($this->_ugroups[0]) ? $this->_ugroups[0] : '';
        $defaults = array(
            self::OWNER_FIELD => $this->_uname,
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
     *
     * Update db based on where clauses
     *
     * @param ExpressionSet $data
     *
     * @param ExpressionSet $where
     *
     * @return PDOStatement
     *
     * @throws AccessDeniedException if user does not have access
     *
     */
    public function update(ExpressionSet $data, ExpressionSet $where)
    {
        if (!$this->isAllowedExecute()) {
            $msg = "Access denied at table '{$this->getName()}'";
            throw new AccessDeniedException($msg);
        }

        // Add mode constraint to where clause
        $where->addExpression(
            new Mode(
                'w',
                $this->getName(),
                $this->_uname,
                $this->_ugroups
            )
        );

        return $this->forwardValidStmt(
            'updating',
            parent::update($data, $where),
            $where
        );
    }


    /**
     *
     * Check if user is allowed to read table
     *
     * @return bool
     *
     */
    public function isAllowedRead()
    {
        return $this->isAllowedTable('r');
    }


    /**
     *
     * Check if user is allowed to write to table
     *
     * @return bool
     *
     */
    public function isAllowedWrite()
    {
        return $this->isAllowedTable('w');
    }


    /**
     *
     * Check if user is allowed to execute table
     *
     * @return bool
     *
     */
    public function isAllowedExecute()
    {
        return $this->isAllowedTable('x');
    }


    /**
     *
     * Set info about current user
     *
     * @param string $uname Name of user
     *
     * @param array $ugroups List of groups user belongs to
     *
     */
    public function setUser($uname, array $ugroups = array())
    {
        assert('is_string($uname)');
        $this->_uname = $uname;
        $this->_ugroups = $ugroups;

        // Recursively set user on joined AcTables
        foreach ($this->getJoins() as $table) {
            if ($table instanceof AcTable) {
                $table->setUser($uname, $ugroups);
            }
        }
    }


    /**
     *
     * Get name of current user
     *
     * @return string
     *
     */
    public function getUser()
    {
        return $this->_uname;
    }


    /**
     *
     * Get groups of current user
     *
     * @return array
     *
     */
    public function getUserGroups()
    {
        return $this->_ugroups;
    }


    /**
     *
     * Check if user is root
     *
     * The root user always have access. Roots are all users named 'root' or
     * belonging to a group named 'root'.
     *
     * @return bool
     *
     */
    public function userIsRoot()
    {
        return $this->_uname == 'root' || in_array('root', $this->_ugroups);
    }


    /**
     *
     * Check if user owns table
     *
     * @return bool
     *
     */
    public function userIsOwner()
    {
        return ($this->_owner == $this->_uname);
    }


    /**
     *
     * Check if user is in group that owns table
     *
     * @return bool
     *
     */
    public function userIsGroup()
    {
        return in_array($this->_group, $this->_ugroups);
    }


    /**
     *
     * Check if user is allowed to perform action on table
     *
     * @param string $action 'r', 'w' or 'x'
     *
     * @return bool
     *
     */
    private function isAllowedTable($action)
    {
        // Recursively check permissions on joined AcTables
        foreach ($this->getJoins() as $table) {
            if (
                $table instanceof AcTable
                && !$table->isAllowedTable($action)
            ) {

                return FALSE;
            }
        }
        
        if ($this->userIsRoot()) {

            return TRUE;
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

        return (($this->_mode & $mask) == $mask);        
    }


    /**
     *
     * Validate row access for empty statements
     *
     * Checks if statment is empty (using PDOStamtement::rowCount, hence this
     * is not supported with all database drivers if the last executed query in
     * statement was a select). If statement is empty valuate if this is due to
     * access restrictions. If so throw exception. Else return statement as is.
     *
     * @param string $verb Action description to be inserted in exception
     * message
     *
     * @param PDOStatement $stmt The statement to evaluate
     *
     * @param ExpressionSet $where Where clause used when creating
     * statement
     *
     * @param Search $search Search clause used when creating statement, if any
     *
     * @return PDOStatement
     *
     * @throws AccessDeniedException if statement is empty due to access
     * restrictions att row level
     *
     */
    private function forwardValidStmt(
        $verb,
        PDOStatement $stmt,
        ExpressionSet $where,
        Search $search = NULL
    )
    {
        assert('is_string($verb)');
        
        if (!$stmt->rowCount()) {
            if (!$search) {
                $search = new Search();
            }
            $where->popExpression();
            $fullAccessStmt = parent::select($search, $where);
            if ($fullAccessStmt->rowCount()) {
                // Access restricted, build exception message
                $pk = $this->getPrimaryKey();
                $row = '';
                if ($where->isExpression($pk)) {
                    $row = "at row '{$where->getExpression($pk)->getValue()}'";
                }
                $msg = "Access denied $verb table '{$this->getName()}' $row";
                throw new AccessDeniedException($msg);
            }
        }

        return $stmt;
    }

}
