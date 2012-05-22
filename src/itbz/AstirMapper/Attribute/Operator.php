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
use itbz\AstirMapper\Exception;


/**
 *
 * Attribute for querying database using variable operators
 *
 * @package AstirMapper
 *
 * @subpackage Attribute
 *
 */
class Operator implements AttributeInterface
{

    /**
     *
     * Map of valid operators and their inversions
     *
     * @var array $operators
     *
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
     *
     * Attribute operator
     *
     * @var string $_op
     *
     */
    private $_op;

    
    /**
     *
     * Attribute value (operand)
     *
     * @var scalar $_value
     *
     */
    private $_value;


    /**
     *
     * Construct and set operand and value
     *
     * @param string $op
     *
     * @param scalar $value
     *
     */
    public function __construct($op, $value)
    {
        $this->setOperator($op);
        $this->setValue($value);
    }


    /**
     *
     * Set value.
     *
     * @param scalar $value
     *
     * @return Operator Return Operator object for chaining
     *
     */
    public function setValue($value)
    {
        assert('is_scalar($value)');
        $this->_value = (string)$value;
        return $this;
    }


    /**
     *
     * Set operator
     *
     * Set operator to one of <=>, =, >=, >, IS NOT, IS, <=, <, LIKE, !=, <>,
     * NOT LIKE, NOT REGEXP, REGEXP, RLIKE or SOUNDS LIKE
     *
     * @param string $op
     *
     * @return Operator Return Operator object for chaining
     *
     * @throws Exception if $op is not a valid operator
     *
     */
    public function setOperator($op)
    {
        if ( !array_key_exists($op, self::$operators) ) {
            $msg = "'$op' is not a valid SQL operator";
            throw new Exception($msg);
        }
        $this->_op = $op;
        return $this;
    }


    /**
     *
     * Invert operator
     *
     * @return Operator Return Operator object for chaining
     *
     */
    public function invert()
    {
        $this->_op = self::$operators[$this->_op];
        return $this;
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
        $context = ":name: {$this->_op} ?";
        return $this->_value;
    }


    /**
     *
     * Get value formatted for database insert
     *
     * @param bool $use Passed by reference. Returns FALSE.
     *
     * @return string
     *
     */
    public function toInsertSql(&$use)
    {
        $use = FALSE;
        return '';
    }

}
