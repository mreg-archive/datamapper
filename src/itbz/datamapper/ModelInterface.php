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
 * Basic model interface
 *
 * @package datamapper
 */
interface ModelInterface extends ExtractInterface
{
    /**
     * Fill model with data from associative array
     *
     * @param array $data
     *
     * @return void
     */
    public function load(array $data);

    /**
     * Extract data from model
     *
     * Model must return an array with attribute names as keys and extracted
     * content as values.
     *
     * @param int $context CRUD context for this extract. One of
     * 'self::CONTEXT_CREATE', 'self::CONTEXT_READ', 'self::CONTEXT_UPDATE' or
     * 'self::CONTEXT_DELETE'.
     *
     * @param array $using List of model attributes to be extracted. Models does
     * not have to honor tis list, as unvanted attributes are removed after
     * extraction is complete.
     *
     * @return array
     */
    public function extract($context, array $using);
}
