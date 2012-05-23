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
 * @subpackage PDO\Access
 *
 */
namespace itbz\AstirMapper\PDO\Access;


/**
 *
 * Access control flags
 *
 * @package AstirMapper
 *
 * @subpackage PDO\Access
 *
 */
interface AccessInterface
{

    /**
     *
     * Name of owner field in db
     *
     * @const string OWNER_FIELD
     *
     */
    const OWNER_FIELD = 'owner';


    /**
     *
     * Name of group field in db
     *
     * @const string GROUP_FIELD
     *
     */
    const GROUP_FIELD = 'group';


    /**
     *
     * Name of mode field in db
     *
     * @const string MODE_FIELD
     *
     */
    const MODE_FIELD = 'mode';


    /**
     *
     * Owner read, write and execute permission flag
     *
     * @const int OWNER_ALL
     *
     */
    const OWNER_ALL = 0700;


    /**
     *
     * Owner read permission flag
     *
     * @const int OWNER_READ
     *
     */
    const OWNER_READ = 0400;


    /**
     *
     * Owner write permission flag
     *
     * @const int OWNER_WRITE
     *
     */
    const OWNER_WRITE = 0200;


    /**
     *
     * Owner execute permission flag
     *
     * @const int OWNER_EXECUTE
     *
     */
    const OWNER_EXECUTE = 0100;


    /**
     *
     * Group read, write and execute permission flag
     *
     * @const int GROUP_ALL
     *
     */
    const GROUP_ALL = 070;


    /**
     *
     * Group read permission flag
     *
     * @const int GROUP_READ
     *
     */
    const GROUP_READ = 040;


    /**
     *
     * Group write permission flag
     *
     * @const int GROUP_WRITE
     *
     */
    const GROUP_WRITE = 020;


    /**
     *
     * Group execute permission flag
     *
     * @const int GROUP_EXECUTE
     *
     */
    const GROUP_EXECUTE = 010;


    /**
     *
     * Others read, write and execute permission flag
     *
     * @const int OTHERS_ALL
     *
     */
    const OTHERS_ALL = 07;


    /**
     *
     * Others read permission flag
     *
     * @const int OTHERS_READ
     *
     */
    const OTHERS_READ = 04;


    /**
     *
     * Others write permission flag
     *
     * @const int OTHERS_WRITE
     *
     */
    const OTHERS_WRITE = 02;


    /**
     *
     * Others execute permission flag
     *
     * @const int OTHERS_EXECUTE
     *
     */
    const OTHERS_EXECUTE = 01;

}
