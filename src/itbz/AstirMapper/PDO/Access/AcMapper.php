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


/*

    jag måste hålla isär rättigheter på table och på row...
        rättigheter på table är ju väldigt enkelt, det håller jag på med nedan

    hur ska gränssnittet för att ändra ägare, grupp och mode på row se ut?
        $model->id = 1;
        $mapper->chown($model, 'hannes');
        $mapper->chgrp($model, 'root');
        $mapper->chmod($model, USER_ALL|GROUP_ALL);                

    sådant som har med ägande av databas-tabell verkar mest logiskt i Table
    
    sådant som måste köra specifika queries (eg. isAllowedRow)
        behöver av tekniska skäl vara i Table
    
    gränssnittet för att ändra grp osv verkar passa bäst i Mapper
        kanske ska jag göra både och??
        det mesta av arbetet i ett Table
        och sedan gränssnitts-grejer till Mapper...??
    
    när jag ska testa mot databas
      mysql_query("LOAD DATA LOCAL INFILE '/path/to/file' INTO TABLE mytable");

*/

/**
 *
 * PDO access control mapper object
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Access
 *
 */
class AcMapper extends \itbz\AstirMapper\PDO\Mapper implements AccessInterface
{

    /**
     *
     * Name of owner of table
     *
     * @var string $owner
     *
     */
    protected $_owner = '';


    /**
     *
     * Name of group of table
     *
     * @var string $_group
     *
     */
    protected $_group = '';


    /**
     *
     * Access mode of table
     *
     * @var int $_mode
     *
     */
    protected $_mode = 0770;


    /**
     *
     * Name of user
     *
     * @var string $_uname
     *
     */
    protected $_uname = '';
    

    /**
     *
     * List of groups user belongs to
     *
     * @var array $_ugroups
     *
     */
    protected $_ugroups = array();


    /**
     *
     * Set access restrictions for table
     *
     * Use access flags to set mode. AcMapper::OWNER_ALL|AcMapper::GROUP_ALL
     * gives full access to owner and group. For more compact code use octal
     * notation. '0700' restrict access to owner only. '0777' allows global
     * access.
     *
     * @param string $owner Name of owner
     *
     * @param string $group Name of group
     *
     * @param int $mode Access flags
     *
     */
    public function setAccess($owner, $group, $mode = 0770)
    {
        assert('is_string($owner)');
        assert('is_string($group)');
        assert('is_int($mode)');
        
        if ($mode < 0) {
            $mode = 0;
        } elseif ($mode > 0777) {
            $mode = 0777;
        }
        
        $this->_owner = $owner;
        $this->_group = $group;
        $this->_mode = $mode;
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
    }


    /**
     *
     * Check if user is root
     *
     * The root user always have access. As default roots are all users named
     * 'root' or belonging to a group named 'root'. Override to create different
     * root definitions.
     *
     * @return bool
     *
     */
    public function isRoot()
    {
        return ($this->_uname == 'root' || in_array('root', $this->_ugroups));
    }


    /**
     *
     * Check if user owns table
     *
     * @return bool
     *
     */
    public function isOwner()
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
    public function isGroup()
    {
        return in_array($this->_group, $this->_ugroups);
    }


    /**
     *
     * Check if user is allowed to read table
     *
     * @return bool
     *
     */
    public function allowTableRead()
    {
        if ($this->isRoot()) {
            return TRUE;
        }
        
        if ($this->isOwner()) {
            $mask = self::OWNER_READ;
        } elseif ($this->isGroups()) {
            $mask = self::GROUP_READ;
        } else {
            $mask = self::OTHERS_READ;
        }

        return (($this->_mode & $mask) == $mask);        
    }

}
