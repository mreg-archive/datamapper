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
use itbz\AstirMapper\Exception;
use DateTimeZone;


/**
 *
 * DateTime attribute
 *
 * Wrapper around PHPs native DateTime class to enable auto-conversion
 * when interacting with the database. Times ar handled as regular unix
 * timestamps. To avoid that PHPs time zone capabilities conflict with
 * time zoning in the database times should be stored in integer columns.
 * In MySQL this amounts to the regular INT type.
 *
 * @package AstirMapper
 *
 * @todo Has not been tested on a 64bit PHP build
 *
 */
class DateTime extends \DateTime implements AttributeInterface
{

    /**
     *
     * Construct DateTime from unix timestamp.
     *
     * To set time from other date and time formats use modify() instead.
     * Constructor only deals with timestamps.
     *
     * @param numeric $timestamp Unix timestamp representing date
     *
     * @param DateTimeZone $zone DateTimeZone object representing the
     * desired time zone
     *
     */
    public function __construct($timestamp = NULL, DateTimeZone $zone = NULL)
    {
        if ( $zone ) {
            parent::__construct(NULL, $zone);
        } else {
            parent::__construct(NULL);
        }

        if ( $timestamp !== NULL ) {
            $this->setTimestamp($timestamp);
        }
    }


    /**
     *
     * Set timestamp from integer or numeric string
     *
     * @param numeric $unixtimestamp Unix timestamp representing date
     *
     * @return DateTime Returns the DateTime object for method chaining
     *
     * @throws Exception if timestamp over- or underflows.
     *
     */
    public function setTimestamp($unixtimestamp)
    {
        if ( !is_numeric($unixtimestamp) ) {
            throw new Exception("Timestamp must be numeric");
        }

        if ( $unixtimestamp < ~PHP_INT_MAX ) {
            $min = ~PHP_INT_MAX;
            $system = PHP_INT_SIZE * 8;
            $msg = "Timestamp can not be less than $min";
            $msg .= " (using $system bit version of PHP)";
            $msg .= " $unixtimestamp supplied.";
            throw new Exception($msg);
        }

        if ( $unixtimestamp > PHP_INT_MAX ) {
            $max = PHP_INT_MAX;
            $system = PHP_INT_SIZE * 8;
            $msg = "Timestamp can not be greater than $max";
            $msg .= " (using $system bit version of PHP)";
            $msg .= " $unixtimestamp supplied.";
            throw new Exception($msg);
        }

        parent::setTimestamp($unixtimestamp);
        return $this;
    }


    /**
     *
     * Returns new DateTime object formatted according to the specified format
     *
     * @param string $format
     *
     * @param string $time
     *
     * @param DateTimeZone $timezone
     *
     * @return DateTime
     *
     * @throws Exception if unable to create date
     *
     */
    static public function createFromFormat($format, $time, $timezone = FALSE)
    {
        if ( $timezone instanceof DateTimeZone ) {
            $datetime = parent::createFromFormat($format, $time, $timezone);
        } else {
            $datetime = parent::createFromFormat($format, $time);
        }

        if ( $datetime === FALSE ) {
            $msg = "Unable to create date using '$time' from format '$format'";
            throw new Exception($msg);
        }

        $timestamp = $datetime->getTimestamp();
        return new DateTime($timestamp);
    }

    
    /**
     *
     * Get date as a unix timestamp string
     *
     * @return string Unix timestamp
     *
     */
    public function __toString()
    {
        return (string)$this->getTimestamp();
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
        return (string)$this;
    }


    /**
     *
     * Get value formatted for database insert
     *
     * @param bool $use Passed by reference
     *
     * @return string
     *
     */
    public function toInsertSql(&$use)
    {
        $use = TRUE;
        return (string)$this;
    }

}
