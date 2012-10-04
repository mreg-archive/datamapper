<?php
/**
 * This file is part of the datamapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package datamapper
 */

namespace itbz\datamapper;

/**
 * Signal that property should be ignored
 *
 * Attribute objects implementing IgnoreAttributeInterface will be ignored when
 * construction data queries.
 *
 * @package datamapper
 */
interface IgnoreAttributeInterface
{
}
