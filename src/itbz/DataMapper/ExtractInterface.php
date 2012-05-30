<?php
/**
 * This file is part of the DataMapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 *
 * @package DataMapper
 */
namespace itbz\DataMapper;


/**
 * Extract constants
 *
 * @package DataMapper
 */
interface ExtractInterface
{

    /**
     * Extract context for data creation
     */
    const CONTEXT_CREATE = 1;


    /**
     * Extract context for data reads
     */
    const CONTEXT_READ = 2;

    /**
     * Extract context for data updates
     */
    const CONTEXT_UPDATE = 3;

    /**
     * Extract context for data deletes
     */
    const CONTEXT_DELETE = 4;

}
