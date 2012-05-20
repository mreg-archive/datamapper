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
namespace itbz\AstirMapper;


/**
 *
 * Basic model interface
 *
 * @package AstirMapper
 *
 */
interface ModelInterface
{

    /**
     *
     * Fill model with data from associative array
     *
     * @param array $data
     *
     */
    public function load(array $data);

}
