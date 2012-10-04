<?php
namespace itbz\datamapper;

/**
 * Database connection constants are definied in phpunit.xml
 */
class MysqlTestCase extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $pdo = new \pdo('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('CREATE DATABASE ' . DB_NAME);
        $pathToSql = realpath(__DIR__ . "/../../../src/itbz/datamapper/pdo/access/sql/access-mysql.sql");
        $command = "mysql -u" . DB_USER . " -p" . DB_PSWD . ' ' . DB_NAME . " < " . $pathToSql;
        exec($command);
    }

    public static function tearDownAfterClass()
    {
        $pdo = new \pdo('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('DROP DATABASE ' . DB_NAME);
    }

    public function setUp()
    {
        $pdo = $this->getPdo();
        $pdo->query(
            'CREATE TABLE data (name VARCHAR(10) PRIMARY KEY, data VARCHAR(10),'
            .' owner VARCHAR(10), `group` VARCHAR(10), mode SMALLINT) ENGINE = MEMORY'
        );

        return $pdo;
    }

    public function tearDown()
    {
        $pdo = $this->getPdo();
        $pdo->query('DROP TABLE data');
    }

    public function getPdo()
    {
        $pdo = new \pdo('mysql:host=localhost;dbname=' . DB_NAME, DB_USER, DB_PSWD);
        $pdo->setAttribute(\pdo::ATTR_ERRMODE, \pdo::ERRMODE_EXCEPTION);

        return  $pdo;
    }
}
