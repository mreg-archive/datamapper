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


/**
 *
 * Auto-covert value objects to scalar values
 *
 * Attributes are designed to enable auto-conversion of value objects to
 * simplify database interaction. When Records are searching or inserting
 * values to the database Attributes are converted using toSearchSql() and
 * toInsertSql() methods.
 *
 * @package AstirMapper
 *
 */
interface AttributeInterface
{

    /**
     *
     * Get value formatted for database search
     *
     * @param string $context Passed by reference. Context used when searching
     * database. :name: is replaced with attribute name. ? is replaced by a
     * correctly qouted version of return value.
     *
     * @example If $context is set to ":name: > ?" the resulting SQL will be:
     * `column_name` > 'escaped_return_value'
     *
     * @return scalar
     *
     */
    public function toSearchSql(&$context);


    /**
     *
     * Get value formatted for database insert
     *
     * @param bool $use Passed by reference.
     * Set to FALSE if this attribute should be ignored
     *
     * @return scalar
     *
     */
    public function toInsertSql(&$use);

}
