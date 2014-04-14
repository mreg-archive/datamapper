<?php

$base = __DIR__ . "/../src";

require_once "$base/Exception.php";
require_once "$base/ExtractInterface.php";
require_once "$base/IgnoreAttributeInterface.php";
require_once "$base/MapperInterface.php";
require_once "$base/ModelInterface.php";
require_once "$base/SearchInterface.php";

require_once "$base/association/OneToManyAssociation.php";

require_once "$base/exception/DataNotFoundException.php";
require_once "$base/exception/PdoException.php";

require_once "$base/pdo/Expression.php";
require_once "$base/pdo/ExpressionSet.php";
require_once "$base/pdo/Iterator.php";
require_once "$base/pdo/Mapper.php";
require_once "$base/pdo/Search.php";

require_once "$base/pdo/table/Table.php";
require_once "$base/pdo/table/SqliteTable.php";
require_once "$base/pdo/table/MysqlTable.php";

require_once "$base/pdo/access/AccessInterface.php";
require_once "$base/pdo/access/AcMapper.php";
require_once "$base/pdo/access/AcTable.php";
require_once "$base/pdo/access/Exception.php";
require_once "$base/pdo/access/AccessDeniedException.php";
require_once "$base/pdo/access/Mode.php";

require_once __DIR__ . "/Model.php";
require_once __DIR__ . "/MockPDO.php";
require_once __DIR__ . "/pdo-mysql/MysqlTestCase.php";
