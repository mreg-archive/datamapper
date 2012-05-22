<?php
namespace itbz\AstirMapper\PDO\Access;
use PDO;


/**
 * Database connection constants are definied in bootstrap.php
 */
class AcMapperTest extends \PHPUnit_Framework_TestCase
{

    static function setUpBeforeClass()
    {
        $pdo = new PDO('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('CREATE DATABASE ' . DB_NAME);
        $pathToSql = realpath(__DIR__ . "/../../../../src/itbz/AstirMapper/PDO/Access/sql/access-mysql.sql");
        $command = "mysql -u" . DB_USER . " -p" . DB_PSWD . ' ' . DB_NAME . " < " . $pathToSql;
        exec($command);
    }

    static function tearDownAfterClass()
    {
        $pdo = new PDO('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('DROP DATABASE ' . DB_NAME);
    }


    function setUp()
    {
        $pdo = $this->getPdo();
        $pdo->query('CREATE TABLE data (name VARCHAR(10) PRIMARY KEY, owner VARCHAR(10), `group` VARCHAR(10), mode SMALLINT) ENGINE = MEMORY');
        $pdo->query("INSERT INTO data (name, owner, `group`, mode) VALUES ('useronly', 'usr', 'grp', 448)");
        $pdo->query("INSERT INTO data (name, owner, `group`, mode) VALUES ('grponly', 'usr', 'grp', 56)");
        $pdo->query("INSERT INTO data (name, owner, `group`, mode) VALUES ('usrgrp', 'usr', 'grp', 504)");
        $pdo->query("INSERT INTO data (name, owner, `group`, mode) VALUES ('all', 'usr', 'grp', 511)");
    }


    function tearDown()
    {
        $pdo = $this->getPdo();
        $pdo->query('DROP TABLE data');
    }


    function getPdo()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=' . DB_NAME, DB_USER, DB_PSWD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return  $pdo;
    }


    function testBlaj()
    {
        // Det här verkar fungera, ta det härifrån...
        
        $pdo = $this->getPdo();
        $q = "select * from data where 1 = isAllowed('r',`data`.`owner`,`data`.`group`,`data`.`mode`,'usr','foo,bar')";
        $stmt = $pdo->query($q);
        foreach ( $stmt as $row ) {
            #print_r($row);
        }
    }

}
