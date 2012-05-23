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
 * @subpackage PDO
 *
 */
namespace itbz\AstirMapper\PDO;
use PDO;


/**
 *
 * Internal class for organizing attributes
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class AttributeContainer
{

    /**
     *
     * Array of Attributes in container
     *
     * @var array $_attributes
     *
     */
    private $_attributes = array();


    /**
     *
     * Add any number of attributes at construct
     *
     */
    public function __construct()
    {
        foreach (func_get_args() as $attribute) {
            $this->addAttribute($attribute);
        }
    }


    /**
     *
     * Add attribute to collection
     *
     * @param Attribute $attribute
     *
     */
    public function addAttribute(Attribute $attribute)
    {
        $name = $attribute->getName();
        $this->_attributes[$name] = $attribute;
    }


    /**
     *
     * Remove the most recently added attribute
     *
     * @return Attribute
     *
     */
    public function popAttribute()
    {
        return array_pop($this->_attributes);
    }


    /**
     *
     * Check if container is empty
     *
     * @return bool
     *
     */
    public function isEmpty()
    {
        return empty($this->_attributes);
    }


    /**
     *
     * Remove attribute by name
     *
     * @param string $name
     *
     */
    public function remove($name)
    {
        unset($this->_attributes[$name]);
    }

    
    /**
     *
     * Get attribute by name
     *
     * @param string $name
     *
     * @return Attribute If no attribute is found FALSE is returned
     *
     */
    public function get($name)
    {
        $return = FALSE;
        if ($this->exists($name)) {
            $return = $this->_attributes[$name];
        }

        return $return;
    }


    /**
     *
     * Check if attribute exists
     *
     * @param string $name
     *
     * @return bool
     *
     */
    public function exists($name)
    {
        return isset($this->_attributes[$name]);
    }


    /**
     *
     * Parse where clause from attributes
     *
     * Returns an array with two indices. The first value is a string of
     * expressions constituting the where clause. The second value is an array
     * of values to be used when executing a statement prepared from the where
     * clause. This array is empty if no parameters in where clause needs to be
     * replaced.
     *
     * @return array
     *
     */
    public function getWhere()
    {
        $contexts = array();
        $values = array();

        foreach ($this->_attributes as $attr) {
            $context = $attr->getName();

            if ($attr->escapeName()) {
                $context = "`$context`";
            }

            $context .= $attr->getOperator();

            if (!$attr->escapeValue()) {
                $context .= $attr->getValue();
            } else {
                $context .= " ? ";
                $values[] = $attr->getValue();
            }

            $contexts[] = $context;
        }

        $clause = implode(' AND ', $contexts);

        if (!empty($clause)) {
            $clause = 'WHERE ' . $clause;
        }
        
        return array($clause, $values);
    }


    /**
     *
     * Parse update string from attributes
     *
     * Returns a comma separated string of expressions to be used in a SET
     * clause
     *
     * @param PDO $pdo
     *
     * @return string
     *
     */
    public function getUpdate(PDO $pdo)
    {
        $values = array();

        foreach ($this->_attributes as $attr) {
            $value = $attr->getValue();
            if ($attr->escapeValue()) {
                $value = $pdo->quote($value);
            }
            $name = $attr->getName();
            if ($attr->escapeName()) {
                $name = "`$name`";
            }

            $values[] = "$name=$value";
        }

        return implode(',', $values);
    }


    /**
     *
     * Parse insert values from attributes
     *
     * Returns an array with two indices. The first is a string of comma
     * separated list of column names. The second is a comma separated list of
     * values.
     *
     * @param PDO $pdo
     *
     * @return array
     *
     */
    public function getInsert(PDO $pdo)
    {
        $columns = array();
        $values = array();

        foreach ($this->_attributes as $attr) {
            $name = $attr->getName();
            if ($attr->escapeName()) {
                $name = "`$name`";
            }
            $columns[] = $name;

            $value = $attr->getValue();
            if ($attr->escapeValue()) {
                $value = $pdo->quote($value);
            }
            $values[] = $value;
        }

        $columns = implode(',', $columns);
        $values = implode(',', $values);
        
        return array($columns, $values);
    }

}
