<?php
namespace itbz\AstirMapper\PDO\Table;
use PDO;


define('DB_USER', 'root');
define('DB_PSWD', 'platon68');
define('DB_NAME', 'testing123123123132');


class MysqlTableTest extends \PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $pdo = new PDO('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('CREATE DATABASE ' . DB_NAME);
    }


    function tearDown()
    {
        $pdo = new PDO('mysql:host=localhost', DB_USER, DB_PSWD);
        $pdo->query('DROP DATABASE ' . DB_NAME);
    }


    function getPdo()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=' . DB_NAME, DB_USER, DB_PSWD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query('CREATE TABLE data (id INT PRIMARY KEY, name VARCHAR(10)) ENGINE = MEMORY;');
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
