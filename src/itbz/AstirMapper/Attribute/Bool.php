<?php
/**
 * Copyright (c) 2012 Hannes Forsgård
 * Licensed under the WTFPL (http://sam.zoy.org/wtfpl/)
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package Astir
 */
namespace itbz\Astir;


/**
 * Boolean value object that enables auto-conversion
 * when interacting with the database
 * @package Astir
 */
class Bool implements Attribute
{

    /**
     * Internal representation
     * @var bool $bool
     */
    private $bool = FALSE;


    /**
     * Set value
     * @see set()
     * @param mixed $bool
     */
    public function __construct($bool)
    {
        $this->set($bool);
    }


    /**
     * Set boolean value. The string 'false' (case insensitive) evaluetas as
     * boolean FALSE, for other inputs regular boolean conversion is used.
     * @param mixed $bool
     * @return Bool Bool object for chaining
     */
    public function set($bool)
    {
        if ( strcasecmp ($bool, 'false') === 0 ) $bool = FALSE;
        $this->bool = (bool)$bool;
        return $this;
    }


    /**
     * Get current boolean value
     * @return bool
     */
    public function isTrue()
    {
        return $this->bool;
    }


    /**
     * Get string representation, '1' for TRUE and '0' for FALSE.
     * @return string
     */
    public function __toString()
    {
        return $this->bool ? '1' : '0';
    }


    /**
     * Get int representation, 1 for TRUE and 0 for FALSE.
     * @return int
     */
    public function toInt()
    {
        return $this->bool ? 1 : 0;
    }


    /**
     * Get value formatted for database search
     * @param string $context Passed by reference
     * @return string
     */
    public function toSearchSql(&$context)
    {
        return (string)$this;
    }


    /**
     *
     * Get value formatted for database insert
     *
     * @param bool $use Passed by reference
     * @return string
     */
    public function toInsertSql(&$use)
    {
        $use = TRUE;
        return (string)$this;
    }

}
