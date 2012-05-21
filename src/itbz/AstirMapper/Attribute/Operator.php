<?php
/**
 * Copyright (c) 2012 Hannes Forsgård
 * Licensed under the WTFPL (http://sam.zoy.org/wtfpl/)
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package Astir
 */
namespace itbz\Astir;
use InvalidArgumentException;


/**
 * Attribute value object for searching the database using other operators than =
 * @package Astir
 */
class Operator implements Attribute
{

    /**
     * Map of valid operators and their inversions
     * @var array $operators
     */
    static $operators = array(
        '<=>' => '!=',
        '=' => '!=',
        '>=' => '<',
        '>' => '<=',
        'IS NOT' => 'IS',
        'IS' => 'IS NOT',
        '<=' => '>',
        '<' => '>=',
        'LIKE' => 'NOT LIKE',
        '!=' => '=',
        '<>' => '=',
        'NOT LIKE' => 'LIKE',
        'NOT REGEXP' => 'REGEXP',
        'REGEXP' => 'NOT REGEXP',
        'RLIKE' => 'NOT REGEXP',
        'SOUNDS LIKE' => 'SOUNDS LIKE'
    );


    /**
     * Attribute operator
     * @var string $op
     */
    private $op;

    
    /**
     * Attribute value (operand)
     * @var scalar $value
     */
    private $value;


    /**
     * Construct and set operand and value
     * @param string $op
     * @param scalar $value
     */
    public function __construct($op, $value)
    {
        $this->setOperator($op);
        $this->setValue($value);
    }


    /**
     * Set value.
     * @param scalar $value
     * @return Operator Return Operator object for chaining
     */
    public function setValue($value)
    {
        assert('is_scalar($value)');
        $this->value = (string)$value;
        return $this;
    }


    /**
     * Set operator to one of <=>, =, >=, >, IS NOT, IS, <=, <, LIKE, !=, <>,
     * NOT LIKE, NOT REGEXP, REGEXP, RLIKE or SOUNDS LIKE
     * @param string $op
     * @return Operator Return Operator object for chaining
     * @throws \InvalidArgumentException if $op is not a valid operator
     */
    public function setOperator($op)
    {
        if ( !array_key_exists($op, self::$operators) ) {
            throw new InvalidArgumentException("'$op' is not a valid SQL operator");
        }
        $this->op = $op;
        return $this;
    }


    /**
     * Invert operator
     * @return Operator Return Operator object for chaining
     */
    public function invert()
    {
        $this->op = self::$operators[$this->op];
        return $this;
    }


    /**
     * Get value formatted for database search
     * @param string $context Passed by reference
     * @return string
     */
    public function toSearchSql(&$context)
    {
        $context = ":name: {$this->op} ?";
        return $this->value;
    }


    /**
     * Get value formatted for database insert
     * @param bool $use Passed by reference. Returns FALSE.
     * @return string
     */
    public function toInsertSql(&$use)
    {
        $use = FALSE;
        return '';
    }


}
