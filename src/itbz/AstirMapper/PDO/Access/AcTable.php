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
use PDO;


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
    protected $_owner;


    /**
     *
     * Name of group of table
     *
     * @var string $_group
     *
     */
    protected $_group;


    /**
     *
     * Access mode of table
     *
     * @var int $_mode
     *
     */
    protected $_mode;


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
     * Check if user is allowed to read table
     *
     * @param string $uname
     *
     * @param array $ugroups
     *
     * @return bool
     *
     */
    public function isAllowedRead($uname, array $ugroups)
    {
        assert('is_string($uname)');
        
        if ($this->isRoot($uname, $ugroups)) {
            return TRUE;
        }
        
        if ($this->isOwner($uname)) {
            $mask = self::OWNER_READ;
        } elseif ($this->isGroup($ugroups)) {
            $mask = self::GROUP_READ;
        } else {
            $mask = self::OTHERS_READ;
        }

        return (($this->_mode & $mask) == $mask);        
    }


    /**
     *
     * Check if user is allowed to write to table
     *
     * @param string $uname
     *
     * @param array $ugroups
     *
     * @return bool
     *
     */
    public function isAllowedWrite($uname, array $ugroups)
    {
        assert('is_string($uname)');
        
        if ($this->isRoot($uname, $ugroups)) {
            return TRUE;
        }
        
        if ($this->isOwner($uname)) {
            $mask = self::OWNER_WRITE;
        } elseif ($this->isGroup($ugroups)) {
            $mask = self::GROUP_WRITE;
        } else {
            $mask = self::OTHERS_WRITE;
        }

        return (($this->_mode & $mask) == $mask);        
    }


    /**
     *
     * Check if user is allowed to execute table
     *
     * @param string $uname
     *
     * @param array $ugroups
     *
     * @return bool
     *
     */
    public function isAllowedExecute($uname, array $ugroups)
    {
        assert('is_string($uname)');
        
        if ($this->isRoot($uname, $ugroups)) {
            return TRUE;
        }
        
        if ($this->isOwner($uname)) {
            $mask = self::OWNER_EXECUTE;
        } elseif ($this->isGroup($ugroups)) {
            $mask = self::GROUP_EXECUTE;
        } else {
            $mask = self::OTHERS_EXECUTE;
        }

        return (($this->_mode & $mask) == $mask);        
    }


    /**
     *
     * Check if user is root
     *
     * The root user always have access. As default roots are all users named
     * 'root' or belonging to a group named 'root'. Override to create different
     * root definitions.
     *
     * @param string $uname
     *
     * @param array $ugroups
     *
     * @return bool
     *
     */
    private function isRoot($uname, array $ugroups)
    {
        return ($uname == 'root' || in_array('root', $ugroups));
    }


    /**
     *
     * Check if user owns table
     *
     * @param string $uname
     *
     * @return bool
     *
     */
    private function isOwner($uname)
    {
        return ($this->_owner == $uname);
    }


    /**
     *
     * Check if user is in group that owns table
     *
     * @param array $ugroups
     *
     * @return bool
     *
     */
    private function isGroup(array $ugroups)
    {
        return in_array($this->_group, $ugroups);
    }

}
