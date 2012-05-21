<?php
/**
 * Copyright (c) 2012 Hannes Forsgård
 * Licensed under the WTFPL (http://sam.zoy.org/wtfpl/)
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package Astir
 */
namespace itbz\Astir;
use InvalidArgumentException;
use DateTimeZone;


/**
 * Wrapper around PHPs native DateTime class to enable auto-conversion
 * when interacting with the database. Times ar handled as regular unix
 * timestamps. To avoid that PHPs time zone capabilities conflict with
 * time zoning in the database times should be stored in integer columns.
 * In MySQL this amounts to the regular INT type.
 * @package Astir
 * @todo Has not been tested on a 64bit PHP build
 */
class DateTime extends \DateTime implements Attribute
{

    /**
     * Construct DateTime from unix timestamp. To set time from other date and
     * time formats use modify() instead. Constructor only deals with timestamps.
     * @param numeric $unixtimestamp Unix timestamp representing date
     * @param DateTimeZone $timezone DateTimeZone object representing the desired time zone
     */
    public function __construct($unixtimestamp = NULL, DateTimeZone $timezone = NULL)
    {
        if ( $timezone ) {
            parent::__construct(NULL, $timezone);
        } else {
            parent::__construct(NULL);
        }

        if ( $unixtimestamp !== NULL ) {
            return $this->setTimestamp($unixtimestamp);
        }
    }


    /**
     * Set timestamp from integer or numeric string
     * @param numeric $unixtimestamp Unix timestamp representing date
     * @return DateTime Returns the DateTime object for method chaining
     * @throws InvalidArgumentException if timestamp over- or underflows.
     */
    public function setTimestamp($unixtimestamp)
    {
        if ( !is_numeric($unixtimestamp) ) {
            throw new InvalidArgumentException("Timestamp must be numeric");
        }

        if ( $unixtimestamp < ~PHP_INT_MAX ) {
            $min = ~PHP_INT_MAX;
            $system = PHP_INT_SIZE * 8;
            $msg = "Timestamp can not be less than $min (using $system bit version of PHP), $unixtimestamp supplied.";
            throw new InvalidArgumentException($msg);
        }

        if ( $unixtimestamp > PHP_INT_MAX ) {
            $max = PHP_INT_MAX;
            $system = PHP_INT_SIZE * 8;
            $msg = "Timestamp can not be greater than $max (using $system bit version of PHP), $unixtimestamp supplied.";
            throw new InvalidArgumentException($msg);
        }

        parent::setTimestamp($unixtimestamp);
        return $this;
    }


    /**
     * Returns new DateTime object formatted according to the specified format
     * @param string $format
     * @param string $time
     * @param DateTimeZone $timezone
     * @return DateTime
     * @throws AstirException if unable to create date
     */
    static public function createFromFormat($format, $time, $timezone = FALSE)
    {
        if ( $timezone instanceof DateTimeZone ) {
            $datetime = parent::createFromFormat($format,$time, $timezone);
        } else {
            $datetime = parent::createFromFormat($format,$time);
        }

        if ( $datetime === FALSE ) {
            $msg = "Unable to create date using '$time' for format '$format'";
            throw new AstirException($msg);
        }

        $timestamp = $datetime->getTimestamp();
        
        if ( !$timestamp ) {
            $msg = "Unable to create date using '$time' for format '$format'";
            throw new AstirException($msg);
        }

        return new DateTime($timestamp);
    }

    
    /**
     * Get date as a unix timestamp string
     * @return string Unix timestamp
     */
    public function __toString()
    {
        return (string)$this->getTimestamp();
    }


    /**
     * Get value formatted for database search
     * @param string $context Passed by reference
     * @return string
     */
    public function toSearchSql(&$context)
    {
        return (string)$this;
    }


    /**
     * Get value formatted for database insert
     * @param bool $use Passed by reference
     * @return string
     */
    public function toInsertSql(&$use)
    {
        $use = TRUE;
        return (string)$this;
    }

}