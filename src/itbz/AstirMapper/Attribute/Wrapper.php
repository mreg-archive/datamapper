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
 */
namespace itbz\AstirMapper\Attribute;



// Också ganska galet att det här ska behövas
// om objekt kan göras om till string borde det räcka för Mapper...


/**
 *
 * Attribute wrapper class
 *
 * @package AstirMapper
 *
 */
class AttrWrap implements AttributeInterface
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
