<?php
/**
 * Copyright (c) 2012 Hannes Forsgård
 * Licensed under the WTFPL (http://sam.zoy.org/wtfpl/)
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package Astir
 */
namespace itbz\Astir;


/**
 * Attribute wrapper class
 * @package Astir
 */
class AttrWrap implements Attribute
{

    /**
     * Wrapped attribute
     * @var mixed $attr
     */
    public $attr;


    /**
     * NOTE: Attribute must be convertable to string
     * @param mixed $attr
     */
    public function __construct($attr)
    {
        $this->attr = $attr;
    }


    /**
     * Get value formatted for database search
     * @param string $context Passed by reference.
     * @return string
     */
    public function toSearchSql(&$context)
    {
        return (string)$this->attr;
    }


    /**
     * Get value formatted for database insert
     * @param bool $use Passed by reference.
     * @return string
     */
    public function toInsertSql(&$use)
    {
        return (string)$this->attr;
    }


    /**
     * PHP magic method get attribute as string
     * @return string
     */
    public function __tostring()
    {
        return (string)$this->attr;
    }

}
