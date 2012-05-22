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
 * @subpackage Attribute
 *
 */
namespace itbz\AstirMapper\Attribute;



/*
    om bool kastas till sträng så blir TRUE => '1' och FALSE => ''

    det fungerar inte så bra i databasen
        men jag borde ju kunna se när någonting är bool och göra om det då...
*/


/**
 *
 * Boolean value object that enables auto-conversion
 * when interacting with the database
 *
 * @package AstirMapper
 *
 * @subpackage Attribute
 *
 */
class Bool implements AttributeInterface
{

    /**
     *
     * Internal representation
     *
     * @var bool $_bool
     *
     */
    private $_bool = FALSE;


    /**
     *
     * Set value
     *
     * For a full description of supported values se set()
     *
     * @param mixed $bool
     *
     */
    public function __construct($bool)
    {
        $this->set($bool);
    }


    /**
     *
     * Set boolean value.
     *
     * The string 'false' (case insensitive) evaluetas as boolean FALSE, for
     * other inputs regular boolean conversion is used.
     *
     * @param mixed $bool
     *
     * @return Bool Bool object for chaining
     *
     */
    public function set($bool)
    {
        if (strcasecmp($bool, 'false') === 0) {
            $bool = FALSE;
        }
        $this->_bool = (bool)$bool;
        return $this;
    }


    /**
     *
     * Get current boolean value
     *
     * @return bool
     *
     */
    public function isTrue()
    {
        return $this->_bool;
    }


    /**
     *
     * Get string representation, '1' for TRUE and '0' for FALSE.
     *
     * @return string
     *
     */
    public function __toString()
    {
        return $this->_bool ? '1' : '0';
    }


    /**
     *
     * Get int representation, 1 for TRUE and 0 for FALSE.
     *
     * @return int
     *
     */
    public function toInt()
    {
        return $this->_bool ? 1 : 0;
    }


    /**
     *
     * Get value formatted for database search
     *
     * @param string $context Passed by reference
     *
     * @return string
     *
     */
    public function toSearchSql(&$context)
    {
        return (string)$this;
    }


    /**
     *
     * Get value formatted for database insert
     *
     * @param bool $use Will be set to TRUE
     *
     * @return string
     *
     */
    public function toInsertSql(&$use)
    {
        $use = TRUE;
        return (string)$this;
    }

}
