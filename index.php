<?php

require './vendor/autoload.php';

require './cli-config.php';

if (!empty($optimize)) {
    switch ($driver) {
        case 'pdo_sqlite':
            break;
        case 'pdo_mysql':
            $result = $dbConn->executeQuery('OPTIMIZE TABLE phpcr_nodes');
            $result->fetchAll();
            break;
        case 'pdo_pgsql':
            $result = $dbConn->executeQuery('ANALYZE phpcr_nodes');
            $result->fetchAll();
            break;
    }
}

$session = getSession($factory, $credentials, $parameters, $workspace);
if (!$session instanceof \PHPCR\SessionInterface) {
    exit("Failed to connect properly. If you add parameters, the first one needs to be 'benchmark', ie. 'php index.php benchmark --append' \n");
}

$rootPath = '/benchmark';
if (!$append && $session->nodeExists($rootPath)) {
    $root = $session->getNode($rootPath);
    $root->remove();
}

$session->save();
$session->refresh(false);

$sectionStart = 1;
if ($append) {
    $sectionStart+= $sections;
    $sections++;
}
$nodeName = '1/'.ceil($count/2);
$path = $rootPath.'/'.$nodeName;
$stopWatch = new \Symfony\Component\Stopwatch\Stopwatch();

if (empty($disableQuery)) {
    $qm = $session->getWorkspace()->getQueryManager();
    $sql = "SELECT * FROM [nt:unstructured] WHERE count = '$nodeName' AND section = '1'";
    $query = $qm->createQuery($sql, \PHPCR\Query\QueryInterface::JCR_SQL2);
    $sql2 = "SELECT * FROM [nt:unstructured] WHERE count = '$nodeName' AND ISDESCENDANTNODE('$rootPath/1')";
    $query2 = $qm->createQuery($sql2, \PHPCR\Query\QueryInterface::JCR_SQL2);
    $sql = "SELECT * FROM [nt:unstructured] WHERE CONTAINS([nt:unstructured].md5, '".md5($nodeName)."')";
    $query3 = $qm->createQuery($sql, \PHPCR\Query\QueryInterface::JCR_SQL2);
    $sql2 = "SELECT * FROM [nt:unstructured] WHERE CONTAINS([nt:unstructured].md5, '".md5($nodeName)."') AND ISDESCENDANTNODE('$rootPath/1')";
    $query4 = $qm->createQuery($sql2, \PHPCR\Query\QueryInterface::JCR_SQL2);
}

gc_enable();

$total = ($sectionStart - 1) * $count;
for ($i = $sectionStart; $i <= $sections; $i++) {
    print_r("Current memory use is '".memory_get_usage()."' bytes \n");

    $root = \PHPCR\Util\NodeHelper::createPath($session, "$rootPath/$i");

    $stopWatch->start("insert nodes");
    insertNodes($session, $root, $count, $i);
    $event = $stopWatch->stop("insert nodes");

    $total+= $count;
    print_r("Inserting $count nodes (total $total) took '" . $event->getDuration(). "' ms.\n");

    unset($session);

    gc_collect_cycles();
    $session = getSession($factory, $credentials, $parameters, $workspace);

    $stopWatch->start("get a node");
    $node = $session->getNode($path);
    $event = $stopWatch->stop("get a node");
    print_r("Getting a node by path took '" . $event->getDuration(). "' ms.\n");
    validateNode($node, $path);

    if (empty($disableQuery)) {
        $stopWatch->start("search a node");
        $result = $query->execute();
        $event = $stopWatch->stop("search a node");
        print_r("Searching a node by property took '" . $event->getDuration(). "' ms.\n");

        $node = $result->getNodes()->current();
        validateNode($node, $path);

        $stopWatch->start("search a node in a subpath");
        $result = $query2->execute();
        $event = $stopWatch->stop("search a node in a subpath");
        print_r("Searching a node by property in a subpath took '" . $event->getDuration(). "' ms.\n");

        $node = $result->getNodes()->current();
        validateNode($node, $path);

        $stopWatch->start("search a node via contains");
        $result = $query3->execute();
        $event = $stopWatch->stop("search a node via contains");
        print_r("Searching a node via contains took '" . $event->getDuration(). "' ms.\n");

        $node = $result->getNodes()->current();
        validateNode($node, $path);

        $stopWatch->start("search a node via contains in a subpath");
        $result = $query4->execute();
        $event = $stopWatch->stop("search a node via contains in a subpath");
        print_r("Searching a node via contains in a subpath took '" . $event->getDuration(). "' ms.\n");

        $node = $result->getNodes()->current();
        validateNode($node, $path);
    }
}

print_r("Current memory use is '".memory_get_usage()."' bytes \n");

function validateNode(\PHPCR\NodeInterface $node = null, $path)
{
    if (!$node) {
        throw new \RuntimeException('Benchmark failing to read correct data: no node found');
    }

    if ($node->getPath() != $path) {
        throw new \RuntimeException("Benchmark failing to read correct data: '$path' does not match '".$node->getPath()."'");
    }
}

function insertNodes(\PHPCR\SessionInterface $session, \PHPCR\NodeInterface $root, $count, $section)
{
    for ($i = 1; $i <= $count; $i++) {
        $node = $root->addNode($i);
        $node->setProperty('foo', 'bar', \PHPCR\PropertyType::STRING);
        $node->setProperty('count', $i, \PHPCR\PropertyType::STRING);
        $node->setProperty('section', $section, \PHPCR\PropertyType::STRING);
        $node->setProperty('md5', md5($i), \PHPCR\PropertyType::STRING);
    }

    $session->save();
}

function getSession(\PHPCR\RepositoryFactoryInterface $factory, \PHPCR\SimpleCredentials $credentials, array $parameters, $workspace) {
    $repository = $factory->getRepository($parameters);
    return $repository->login($credentials, $workspace);

}
