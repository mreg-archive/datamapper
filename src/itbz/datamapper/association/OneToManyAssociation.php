<?php
/**
 * This file is part of the DataMapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package DataMapper\Association
 */

namespace itbz\DataMapper\Association;

/*
    field: id       => field: parentId
    value: 'member' => field: parentTable

    detta ska översättas till en whereclause i child..
        hämta parent field id
            sätt det till child field parentId
        hämta värde 'member'
            sätt det till child field parentTable

    vad händer som jag stoppar in mapper och hela baletten här??
        när jag laddar den till mapper kan mapper fråga vilka fält jag vill ha
            för att bygga relation

    sedan kan mapper säga ->
        foreach ($this->_associations as $assoc) {
            $assoc->findAssociatedModels(array $valuesNeeded...);
        }

    callback för att processa hämtade object ska laddas till master...

    hur vill jag hämta associerade object?
        vill jag använda en metod på mapper
            getAddresses
        eller vill jag att de ska laddas in automatiskt när jag gör en read??

        jag behöver att de går att hämta som kompletta object för redigering i
            jsclient
            men det går ju hur som helst, skillnaden är bara om de ska hämtas
                direkt

    hur ska det gå till att spara, skapa nya adresser osv???
        om adresser hämtas direkt och jag skapar en array av object snarare än
        sparar en iterator så kan jag hämta objecten
            arbeta på dem
            och sedan spara dem igen
        i så fall så ska de sparas när jag sparar master model

        när jag lägger till nya så är det kanske att jag bara skriver dem till
            array
            om när jag sparar så ska den gå igenom alla och se till att de har
                rätt conditions

        att ta bort är helt enkelt att ta bort med conditions...

    gör jag på detta sätt så behöver jag inte det här konstiga med
        interfaceMethods längre...
*/

/**
 * One-to-many association
 *
 * @package DataMapper\Association
 */
class OneToManyAssociation
{
    /**
     * List of dynamic conditions
     *
     * @var array
     */
    private $dynamicConds = array();

    /**
     * List of static conditions
     *
     * @var array
     */
    private $staticConds = array();

    /**
     * Associated mapper
     *
     * @var Mapper
     */
    private $mapper;

    /**
     * List of method names this association responds to
     *
     * @var array
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
     *
     * @param string $plural
     *
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
     *
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
     *
     * @param mixed $param
     *
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
     *
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
     *
     * @param string $associatedAttr Name of attribute in associated model
     *
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
     *
     * @param string $associatedAttr Name of attribute in associated model
     *
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
     *
     * @return array
     *
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
