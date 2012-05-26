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
 * Basic model interface
 *
 * @package DataMapper
 */
interface ModelInterface
{

    /**
     * Fill model with data from associative array
     *
     * @param array $data
     *
     * @return void
     */
    public function load(array $data);

}
