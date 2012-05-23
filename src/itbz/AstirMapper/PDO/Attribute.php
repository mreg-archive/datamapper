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
use itbz\AstirMapper\Exception\PdoException;


/**
 *
 * Internal class for modeling SQL attributes
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class Attribute
{

    /**
     *
     * The name of this attribute
     *
     * @var string $_name
     *
     */
    protected $_name;


    /**
     *
     * The value of this attribute
     *
     * @var string $_value
     *
     */
    protected $_value;


    /**
     *
     * Flag if attribute value should be escaped
     *
     * @var bool $_escapeValue
     *
     */
    private $_escapeValue = TRUE;


    /**
     *
     * Flag if attribute name should be escaped
     *
     * @var bool $_escapeName
     *
     */
    private $_escapeName = TRUE;


    /**
     *
     * Construct and cast value to string
     *
     * The type conversion used differs from standard PHP type juggling rules.
     * Boolean TRUE is converted to '1' and FALSE to '0'. Arrays are imploded
     * using ',' as field delimiter. NULL is converted to the lower case string
     * 'null'. Objects are converted to strings if they implement the __tostring
     * magic method. Integers and floats are converted using the standard type
     * juggling rules.
     *
     * @param string $name The name of the attribute
     *
     * @param mixed $value
     *
     * @throws PdoException if unable to value object to string
     *
     */
    public function __construct($name, $value)
    {
        assert('is_string($name)');
        $this->_name = $name;

        switch (gettype($value)) {
            case 'boolean':
                $this->_value = $value ? '1' : '0';
                break;

            case 'array':
                $this->_value = implode(',', $value);
                break;

            case 'NULL':
                $this->_value = 'null';
                $this->setEscapeValue(FALSE);
                break;

            case 'object':
                if (method_exists($value, '__toString')) {
                    $this->_value = (string)$value;
                } else {
                    $classname = get_class($value);
                    $msg = "Unable to convert class '$classname' to string";
                    throw new PdoException($msg);
                }
                break;

            default:
                $this->_value = (string)$value;
        }
    }


    /**
     *
     * Get name of attribute
     *
     * @return string
     *
     */
    public function getName()
    {
        return $this->_name;
    }


    /**
     *
     * Get value of attribute
     *
     * @return string
     *
     */
    public function getValue()
    {
        return $this->_value;
    }


    /**
     *
     * Set if attribute value should be escaped
     *
     * @param bool $flag
     *
     */
    public function setEscapeValue($flag)
    {
        assert('is_bool($flag)');
        $this->_escapeValue = $flag;
    }


    /**
     *
     * Check if attribute value should be escaped
     *
     * @return bool
     *
     */
    public function escapeValue()
    {
        return $this->_escapeValue;
    }


    /**
     *
     * Set if attribute name should be escaped
     *
     * @param bool $flag
     *
     */
    public function setEscapeName($flag)
    {
        assert('is_bool($flag)');
        $this->_escapeName = $flag;
    }


    /**
     *
     * Check if attribute name should be escaped
     *
     * @return bool
     *
     */
    public function escapeName()
    {
        return $this->_escapeName;
    }


    /**
     *
     * Get operator for comparisons
     *
     * @return string
     *
     */
    public function getOperator()
    {
        return '=';
    }

}
