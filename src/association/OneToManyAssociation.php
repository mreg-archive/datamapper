<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper\association;

/**
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
 * @todo Add support for inter-mapper-relations. Create a Relation class for
 *     managing search conditions when working with the related mapper.
 *     Support for one-to-one and one-to-many using `hasOne` and `hasMany`
 *     // Load an one-to-many relation
 *     $mapper->hasMany('Address', $relationObj, $addressMapper);
 *     // Get all addresses
 *     $iterator = $mapper->getAddressIterator($model);
 *     // Insert or update an address
 *     $mapper->saveAddress($model, $address);
 *     // Remove relation to address, but do not delete address from db
 *     $memberMapper->disownAddress($member, $address);
 *     // Remove relation and remove address from db
 *     $memberMapper->purgeAddress($member, $address);
 *     // Same as above, but now getAddress returns a model, not an iterator
 *     $mapper->hasOne('Address', $relationObj, $addressMapper);
 *     $address = $mapper->getAddress($model);
 */
class OneToManyAssociation
{
    /**
     * @var array List of dynamic conditions
     */
    private $dynamicConds = array();

    /**
     * @var array List of static conditions
     */
    private $staticConds = array();

    /**
     * @var Mapper Associated mapper
     */
    private $mapper;

    /**
     * @var array List of method names this association responds to
     */
    private $interfaceMethods;

    /**
     * One-to-many association
     *
     * Interface methods are constructed from singular and plural versions of
     * name. For an association named 'Address' with plural 'Addresses' the
     * created interface methods are 'getAddresses', 'deleteAddress' and
     * 'saveAddress'.
     *
     * @param string $name Name of this association.
     * @param string $plural
     * @param Mapper $associatedMapper
     */
    public function __construct($name, $plural, Mapper $associatedMapper)
    {
        assert('is_string($name)');
        assert('is_string($plural)');
        $this->mapper = $associatedMapper;
        $this->interfaceMethods = array(
            'get' . $plural => 'findAssociatedModels',
            'delete' . $name => 'deleteAssociatedModel',
            'save' . $name => 'saveAssociatedModel'
        );
    }

    /**
     * Check if interface method exists
     *
     * @param string $methodName
     * @return bool
     */
    public function interfaceMethodExists($methodName)
    {
        return isset($this->interfaceMethods[$methodName]);
    }

    /**
     * Call interface method with param
     *
     * @param string $methodName
     * @param mixed $param
     * @return mixed
     */
    public function interfaceMethodCall($methodName, $param)
    {
        if (!$this->interfaceMethodExists($methodName)) {
            $msg = "Interface method '$methodName' does not exist";
            throw new Exception($msg);
        }
        $method = $this->interfaceMethods[$methodName];

        return $this->$method($param);
    }

    /**
     * Read associated models using master attributes
     *
     * @param array $masterAttributes
     * @return \Iterator
     */
    public function findAssociatedModels(array $masterAttributes)
    {
        $conditions = $this->buildConditions($masterAttributes);

        return $this->mapper->findMany($conditions, new Search());
    }

    /**
     * Add dynaimc condition
     *
     * A dynamic condition is met when atttribute in master model equals
     * attribute in associated model
     *
     * @param string $masterAttr Name of attribute in master model
     * @param string $associatedAttr Name of attribute in associated model
     * @return void
     */
    public function addCondition($masterAttr, $associatedAttr)
    {
        assert('is_string($masterAttr)');
        assert('is_string($associatedAttr)');
        $this->dynamicConds[$associatedAttr] = $masterAttr;
    }

    /**
     * Add static condition
     *
     * A static condition is met when value equals attribute in associated model
     *
     * @param mixed $value Anything that is convertable to string
     * @param string $associatedAttr Name of attribute in associated model
     * @return void
     */
    public function addStaticCondition($value, $associatedAttr)
    {
        assert('is_string($associatedAttr)');
        $this->staticConds[$associatedAttr] = $value;
    }

    /**
     * Get list of attributes containing mapper must supply
     *
     * @return array
     */
    public function getRequiredAttributes()
    {
        return array_values($this->dynamicConds);
    }

    /**
     * Build array of conditions from master attributes
     *
     * @param array $masterAttributes
     * @return array
     * @throws Exception if a required master attribute is missing     
     */
    protected function buildConditions(array $masterAttributes)
    {
        $conditions = $this->staticConds;
        foreach ($this->dynamicConds as $associatedAttr => $masterAttr) {
            if (!array_key_exists($masterAttr, $masterAttributes)) {
                $msg = "Required master attribute '$masterAttr' missing";
                throw new Exception($msg);
            }
            $conditions[$associatedAttr] = $masterAttributes[$masterAttr];
        }

        return $conditions;
    }
}
