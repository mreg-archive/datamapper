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
use itbz\AstirMapper\PDO\Mapper;
use itbz\AstirMapper\Exception\AccessDeniedException;

/*

    hur ska gränssnittet för att ändra ägare, grupp och mode på row se ut?
        $model->id = 1;
        $mapper->chown($model, 'hannes');
        $mapper->chgrp($model, 'root');
        $mapper->chmod($model, USER_ALL|GROUP_ALL);                

    gränssnittet för att ändra grp osv verkar passa bäst i Mapper
        kanske ska jag göra både och??
        det mesta av arbetet i ett Table
        och sedan gränssnitts-grejer till Mapper...??
    

    MODE

    måste veta vilket table det gäller
        skapa mode i mapper

    måste få till så att det blir ett giltigt SQL uttryck även med
            name = isAllowed...
        det svåra just nu verkar vara att koden alltid sätter ``


    TABLE
    
    kan hålla redan på accessinformatino om table, ägare osv...
        - FIXAT
        
    
    MAPPER
    
    fråga table om det är okey att läsa eller skriva till table
        - FIXAT

    mapper kan också skapa lämpligt mode object när jag ber om att få läsa osv..
        och även då för att byta ägare, ändra mode osv...

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
class AcMapper extends Mapper
{

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
     * Construct and inject table instance
     *
     * @param AcTable $table
     *
     * @param ModelInterface $prototype Prototype model that will be cloned when
     * mapper needs a new return object.
     *
     */
    public function __construct(AcTable $table, ModelInterface $prototype)
    {
        parent::__construct($table, $prototype);
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
     * Get iterator containing multiple racords based on search
     *
     * @param ModelInterface $model
     *
     * @param SearchInterface $search
     *
     * @return Iterator
     *
     * @throws AccessDeniedException if user does not have access
     *
     */
    public function findMany(ModelInterface $model, SearchInterface $search)
    {
        if (!$this->_table->isAllowedExecute($this->_uname, $this->_ugroups)) {
            $msg = "Access denied at table '{$this->_table->getName()}'";
            throw new AccessDeniedException($msg);
        }

        return parent::findMany($model, $search);
    }


    /**
     *
     * Delete model from persistent storage
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     * @throws AccessDeniedException if user does not have access
     *
     */
    public function delete(ModelInterface $model)
    {
        if (!$this->_table->isAllowedWrite($this->_uname, $this->_ugroups)) {
            $msg = "Access denied at table '{$this->_table->getName()}'";
            throw new AccessDeniedException($msg);
        }

        return parent::delete($model);
    }


    /**
     *
     * Insert model into db
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     */
    protected function insert(ModelInterface $model)
    {
        if (!$this->_table->isAllowedWrite($this->_uname, $this->_ugroups)) {
            $msg = "Access denied at table '{$this->_table->getName()}'";
            throw new AccessDeniedException($msg);
        }

        return parent::insert($model);
    }


    /**
     *
     * Update db using primary key as where clause.
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     */
    protected function update(ModelInterface $model)
    {
        if (!$this->_table->isAllowedExecute($this->_uname, $this->_ugroups)) {
            $msg = "Access denied at table '{$this->_table->getName()}'";
            throw new AccessDeniedException($msg);
        }

        return parent::update($model);
    }

}
