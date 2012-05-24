<?php
namespace itbz\AstirMapper\PDO\Access;
use itbz\AstirMapper\PDO\Search;


class AccesStackTest extends \itbz\AstirMapper\MysqlTestCase
{

    function setUp()
    {
        $pdo = parent::setUp();
        $pdo->query("INSERT INTO data (name, data, owner, `group`, mode) VALUES ('useronly', 'test', 'usr', 'grp', 448)");
        $pdo->query("INSERT INTO data (name, data, owner, `group`, mode) VALUES ('grponly', 'test', 'usr', 'grp', 56)");
        $pdo->query("INSERT INTO data (name, data, owner, `group`, mode) VALUES ('usrgrp', 'test', 'usr', 'grp', 504)");
    }


    function getMapper()
    {
        $table = new AcTable('data', $this->getPdo(), '', '', 0777);
        $mapper = new AcMapper($table, new \Model());
        return $mapper;
    }


    function testFindMany()
    {
        $mapper = $this->getMapper();

        // 'usr' should find rows 'useronly' and 'usrgrp'
        $mapper->setUser('usr', array('foo', 'bar'));

        $iterator = $mapper->findMany(new \Model(), new Search());
        $found = '';
        foreach ($iterator as $key => $data)
        {
            $found .= $key . ' ';
        }
        $this->assertEquals('useronly usrgrp ', $found);
    }


    /**
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testRowAccessException()
    {
        // Unnamed user is blocked
        $mapper = $this->getMapper();
        $mapper->findMany(new \Model(), new Search());
    }


    function testDelete()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('usr', array('foo', 'bar'));
        $mapper->delete(new \Model());

        $model = new \Model();
        $model->name = "useronly";

        $iterator = $mapper->findMany($model, new Search());
        $count = 0;
        foreach ($iterator as $key => $data) $count++;
        $this->assertEquals(0, $count, 'useronly should be deleted');

        $mapper->setUser('', array('grp'));

        $iterator = $mapper->findMany(new \Model(), new Search());
        $count = 0;
        foreach ($iterator as $key => $data) $count++;
        $this->assertEquals(1, $count, 'grp can read grponly');
    }


    /**
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testRowDeleteException()
    {
        $mapper = $this->getMapper();
        $mapper->delete(new \Model());
    }


    function testInsert()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('foo', array('bar'));

        $model = new \Model();
        $model->name = 'foobar';
        $mapper->save($model);
        
        $fromDb = $mapper->findByPk('foobar');
        $this->assertEquals('foo', $fromDb->owner);
        $this->assertEquals('bar', $fromDb->group);
    }


    function testUpdate()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('usr', array('foo', 'bar'));

        $model = new \Model();
        $model->name = "useronly";
        $model->data = "updated";
        $mapper->save($model);

        $fromDb = $mapper->findByPk('useronly');
        $this->assertEquals('updated', $fromDb->data);
    }


    /**
     * @expectedException itbz\AstirMapper\PDO\Access\AccessDeniedException
     */
    function testRowUpdateException()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('usr', array('grp'));
        
        $model = new \Model();
        $model->name = "foobar";
        $model->mode = 04;
        $mapper->save($model);

        $mapper->setUser('');
        $update = new \Model();
        $update->name = "foobar";
        $update->data = "updated";
        $mapper->save($update);
    }

    
    function testChown()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('root');

        $model = new \Model();
        $model->name = "useronly";
        $mapper->chown($model, 'foobar');

        $mapper->setUser('foobar');

        // Now only useronly, with owner foobar, should be readable
        $iterator = $mapper->findMany(new \Model(), new Search());
        $found = '';
        foreach ($iterator as $key => $data)
        {
            $found .= $key . ' ';
        }
        $this->assertEquals('useronly ', $found);
    }


    function testChmod()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('non-root');

        $model = new \Model();
        $model->name = "useronly";

        // Chmod does nothing as current user does not own
        $nRows = $mapper->chmod($model, 0777);
        $this->assertEquals(0, $nRows);

        // But owner can change
        $mapper->setUser('usr');
        $nRows = $mapper->chmod($model, 0777);
        $this->assertEquals(1, $nRows);
        
        // And so can root
        $mapper->setUser('root');
        $nRows = $mapper->chmod($model, 0770);
        $this->assertEquals(1, $nRows);
    }


    function testChgrp()
    {
        $mapper = $this->getMapper();
        $mapper->setUser('non-root', array('newgroup'));

        $model = new \Model();
        $model->name = "useronly";

        // Chmod does nothing as current user does not own
        $nRows = $mapper->chgrp($model, 'newgroup');
        $this->assertEquals(0, $nRows);

        // But owner can change
        $mapper->setUser('usr', array('newgroup'));
        $nRows = $mapper->chgrp($model, 'newgroup');
        $this->assertEquals(1, $nRows);
        
        // And so can root
        $mapper->setUser('root');
        $nRows = $mapper->chgrp($model, 'root');
        $this->assertEquals(1, $nRows);
    }

}
