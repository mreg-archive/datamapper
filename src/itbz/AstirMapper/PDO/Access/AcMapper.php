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
use itbz\AstirMapper\ModelInterface;
use itbz\AstirMapper\PDO\Mapper;
use itbz\AstirMapper\PDO\Attribute;
use itbz\AstirMapper\Exception\AccessDeniedException;
use itbz\AstirMapper\Exception\PdoException;


/**
 *
 * PDO access control mapper object
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Access
 *
 */
class AcMapper extends Mapper implements AccessInterface
{

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
        $this->_table->setUser($uname, $ugroups);
    }


    /**
     *
     * Set new owner on rows matching model
     *
     * Only roots can change owner
     *
     * @param ModelInterface $model
     *
     * @param string $newOwner
     *
     * @return int Number of affected rows
     *
     * @throws AccessDeniedException if user is not root
     *
     * @throws PdoException if primary key model is not set
     *
     */
    public function chown(ModelInterface $model, $newOwner)
    {
        assert('is_string($newOwner)');

        if (!$this->_table->userIsRoot()) {
            $msg = "Access denied changing owner, must be root.";
            throw new AccessDeniedException($msg);
        }
        if (!$this->getPk($model)) {
            $msg = "Unable to change owner, primary key not set.";
            throw new PdoException($msg);
        }

        // Set new owner
        $data = $this->getAttributes($model);
        $data->remove(self::OWNER_FIELD);
        $data->addAttribute(
            new Attribute(self::OWNER_FIELD, $newOwner)
        );

        // Update model
        $columns = array($this->_table->getPrimaryKey());
        $where = $this->getAttributes($model, $columns);
        $stmt = $this->_table->update($data, $where);

        return $stmt->rowCount();
    }


    /**
     *
     * Set new mode on rows matching model
     *
     * Only owner and root can change mode
     *
     * @param ModelInterface $model
     *
     * @param int $newMode
     *
     * @return int Number of affected rows
     *
     * @throws PdoException if primary key model is not set
     *
     */
    public function chmod(ModelInterface $model, $newMode)
    {
        assert('is_int($newMode)');

        if (!$this->getPk($model)) {
            $msg = "Unable to change access mode, primary key not set.";
            throw new PdoException($msg);
        }

        // Set new mode
        $data = $this->getAttributes($model);
        $data->remove(self::MODE_FIELD);
        $data->addAttribute(
            new Attribute(self::MODE_FIELD, $newMode)
        );

        $columns = array($this->_table->getPrimaryKey());
        $where = $this->getAttributes($model, $columns);

        // Only owner and root can change mode
        if (!$this->_table->userIsRoot()) {
            $uname = $this->_table->getUser();
            $where->addAttribute(
                new Attribute(self::OWNER_FIELD, $uname)
            );
        }

        $stmt = $this->_table->update($data, $where);

        return $stmt->rowCount();
    }


    /**
     *
     * Set new group on rows matching model
     *
     * Only owner and root can change group
     *
     * @param ModelInterface $model
     *
     * @param string $newGroup
     *
     * @return int Number of affected rows
     *
     * @throws PdoException if primary key model is not set
     *
     * @throws AccessDeniedException if is not a member of the new group
     *
     */
    public function chgrp(ModelInterface $model, $newGroup)
    {
        assert('is_string($newGroup)');

        if (!$this->getPk($model)) {
            $msg = "Unable to change group, primary key not set.";
            throw new PdoException($msg);
        }

        // Remove 'root' from the list of groups
        $ugroups = $this->_table->getUserGroups();
        $index = array_search('root', $ugroups);
        if ( $index !== FALSE ) {
            unset($ugroups[$index]);
        }

        if (
            !$this->_table->userIsRoot()
            && !in_array($newGroup, $ugroups)
        ) {
            $msg = "Unable to set group '$newGroup', you are not a member.";
            throw new AccessDeniedException($msg);
        }

        // Set new group
        $data = $this->getAttributes($model);
        $data->remove(self::GROUP_FIELD);
        $data->addAttribute(
            new Attribute(self::GROUP_FIELD, $newGroup)
        );

        $columns = array($this->_table->getPrimaryKey());
        $where = $this->getAttributes($model, $columns);

        // Only owner and root can change group
        if (!$this->_table->userIsRoot()) {
            $uname = $this->_table->getUser();
            $where->addAttribute(
                new Attribute(self::OWNER_FIELD, $uname)
            );
        }

        $stmt = $this->_table->update($data, $where);

        return $stmt->rowCount();
    }

}
