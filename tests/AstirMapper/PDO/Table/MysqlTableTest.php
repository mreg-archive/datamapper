<?php
namespace itbz\AstirMapper\PDO\Table;
use PDO;


/**
 * Database connection constants are definied in bootstrap.php
 */
class MysqlTableTest extends \PHPUnit_Framework_TestCase
{

    static function setUpBeforeClass()
    {
        $pdo = new PDO('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('CREATE DATABASE ' . DB_NAME);
    }


    static function tearDownAfterClass()
    {
        $pdo = new PDO('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('DROP DATABASE ' . DB_NAME);
    }


    public function setUp()
    {
        $pdo = $this->getPdo();
        $pdo->query('CREATE TABLE data (id INT PRIMARY KEY, name VARCHAR(10)) ENGINE = MEMORY');
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


    function testReverseEngineerColumns()
    {
        $table = new MysqlTable('data', $this->getPdo());
        $expected = array('id', 'name');
        $this->assertEquals($expected, $table->reverseEngineerColumns());
    }


    function testReverseEngineerPK()
    {
        $table = new MysqlTable('data', $this->getPdo());
        $this->assertEquals('id', $table->reverseEngineerPK());
    }

}
