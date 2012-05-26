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
 * DateTime wrapper.
 *
 * Wrapper around PHPs native DateTime class to enable auto-conversion. To avoid
 * that PHPs time zone capabilities conflict with time zoning in the database
 * times should be stored as integers.
 *
 * @package DataMapper
 */
class DateTime extends \DateTime
{

    /**
     * Get date as a unix timestamp string
     *
     * @return string Unix timestamp
     */
    public function __toString()
    {
        return (string)$this->getTimestamp();
    }

}
