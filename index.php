<?php

require './vendor/autoload.php';

$workspaceName = 'benchmark';

$username = 'admin';
$password = 'admin';

$uri = 'http://localhost:8080/server';

$driver = 'pdo_sqlite';
$path = './benchmark.sqlite';
$host = null;
$port = null;
$dbusername = null;
$dbpassword = null;
$dbname = null;
/*
$driver = 'pdo_mysql';
$path = null;
$host = 'localhost';
$port = '3306';
$dbusername = 'root';
$dbpassword = null;
$dbname = 'phpcr_benchmark';
*/
$dbConn = \Doctrine\DBAL\DriverManager::getConnection(array(
    'driver'    => $driver,
    'path'      => $path,
    'host'      => $host,
    'port'      => $port,
    'user'      => $dbusername,
    'password'  => $dbpassword,
    'dbname'    => $dbname
));

// recreate database schema
$options = array('disable_fks' => $dbConn->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform);
$repositorySchema = new \Jackalope\Transport\DoctrineDBAL\RepositorySchema($options, $dbConn);
$repositorySchema->reset();

$parameters = array(
    'jackalope.doctrine_dbal_connection' => $dbConn,
    \Jackalope\Session::OPTION_AUTO_LASTMODIFIED => false,
    'jackalope.logger' => new \Jackalope\Transport\Logging\Psr3Logger(new \Psr\Log\NullLogger()),
);

$factory = new \Jackalope\RepositoryFactoryDoctrineDBAL();

/*
$parameters = array(
    'jackalope.jackrabbit_uri' => $uri,
    \Jackalope\Session::OPTION_AUTO_LASTMODIFIED => false,
    'jackalope.logger' => new \Jackalope\Transport\Logging\Psr3Logger(new \Psr\Log\NullLogger()),
);

$factory = new \Jackalope\RepositoryFactoryJackrabbit();
*/

$repository = $factory->getRepository($parameters);
$credentials = new \PHPCR\SimpleCredentials($username, $password);

try {
    $session = $repository->login($credentials, $workspaceName);
} catch (\PHPCR\NoSuchWorkspaceException $e) {
    $adminRepository = $factory->getRepository($parameters); // get a new repository to log into
    $session = $adminRepository->login($credentials, 'default');
    $workspace = $session->getWorkspace();
    if (in_array($workspaceName, $workspace->getAccessibleWorkspaceNames())) {
        throw new \Exception("Failed to log into $workspaceName");
    }

    $workspace->createWorkspace($workspaceName);

    $repository = $factory->getRepository($parameters);
    $session = $repository->login($credentials, $workspaceName);
}

$rootPath = '/benchmark';
if ($session->nodeExists($rootPath)) {
    $root = $session->getNode($rootPath);
    $root->remove();
}

$session->save();
$session->refresh(false);

$count = 100;
$sections = 100;
$nodeName = $count/2;
$path = $rootPath.'/1/'.$nodeName;
$stopWatch = new \Symfony\Component\Stopwatch\Stopwatch();

$qm = $session->getWorkspace()->getQueryManager();
$sql = "SELECT * FROM [nt:unstructured] WHERE count = '$nodeName'";
$query = $qm->createQuery($sql, \PHPCR\Query\QueryInterface::JCR_SQL2);
$sql2 = "SELECT * FROM [nt:unstructured] WHERE count = '$nodeName' AND ISDESCENDANTNODE('$rootPath/1')";
$query2 = $qm->createQuery($sql2, \PHPCR\Query\QueryInterface::JCR_SQL2);

$total = 0;
for ($i = 1; $i <= $sections; $i++) {
    $root = \PHPCR\Util\NodeHelper::createPath($session, "$rootPath/$i");

    $stopWatch->start("insert nodes");
    insertNodes($session, $root, $count);
    $event = $stopWatch->stop("insert nodes");

    $total+= $count;
    print_r("Inserting $count nodes (total $total) took '" . $event->getDuration(). "' ms.\n");

    $repository = $factory->getRepository($parameters);
    $session = $repository->login($credentials, $workspaceName);

    $stopWatch->start("get a node");
    $node = $session->getNode($path);
    $event = $stopWatch->stop("get a node");
    print_r("Getting a node by path took '" . $event->getDuration(). "' ms.\n");
    validateNode($node, $path);

    $stopWatch->start("search a node");
    $result = $query->execute();
    $event = $stopWatch->stop("search a node");
    print_r("Searching a node by property took '" . $event->getDuration(). "' ms.\n");

    /** @var NodeIterator $nodes */
    $node = $result->getNodes()->current();
    validateNode($node, $path);

    $stopWatch->start("search a node in a subpath");
    $result = $query2->execute();
    $event = $stopWatch->stop("search a node in a subpath");
    print_r("Searching a node by property in a subpath took '" . $event->getDuration(). "' ms.\n");

    /** @var NodeIterator $nodes */
    $node = $result->getNodes()->current();
    validateNode($node, $path);
}

function validateNode(\PHPCR\NodeInterface $node = null, $path)
{
    if (!$node) {
        throw new \RuntimeException('Benchmark failing to read correct data: no node found');
    }

    if ($node->getPath() != $path) {
        throw new \RuntimeException("Benchmark failing to read correct data: '$path' does not match '".$node->getPath()."'");
    }
}

function insertNodes(\PHPCR\SessionInterface $session, \PHPCR\NodeInterface $root, $count)
{
    for ($i = 1; $i <= $count; $i++) {
        $node = $root->addNode($i);
        $node->setProperty('foo', 'bar', \PHPCR\PropertyType::STRING);
        $node->setProperty('count', $i, \PHPCR\PropertyType::STRING);
    }

    $session->save();
}
