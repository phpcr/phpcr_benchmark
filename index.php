<?php

require './vendor/autoload.php';
$session = require './cli-config.php';

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
$nodeName = $count/2;
$path = $rootPath.'/1/'.$nodeName;
$stopWatch = new \Symfony\Component\Stopwatch\Stopwatch();

$qm = $session->getWorkspace()->getQueryManager();
$sql = "SELECT * FROM [nt:unstructured] WHERE count = '$nodeName'";
$query = $qm->createQuery($sql, \PHPCR\Query\QueryInterface::JCR_SQL2);
$sql2 = "SELECT * FROM [nt:unstructured] WHERE count = '$nodeName' AND ISDESCENDANTNODE('$rootPath/1')";
$query2 = $qm->createQuery($sql2, \PHPCR\Query\QueryInterface::JCR_SQL2);

gc_enable();

$total = ($sectionStart - 1) * $count;
for ($i = $sectionStart; $i <= $sections; $i++) {
    $root = \PHPCR\Util\NodeHelper::createPath($session, "$rootPath/$i");

    $stopWatch->start("insert nodes");
    insertNodes($session, $root, $count);
    $event = $stopWatch->stop("insert nodes");

    $total+= $count;
    print_r("Inserting $count nodes (total $total) took '" . $event->getDuration(). "' ms.\n");

    unset($session);

    gc_collect_cycles();
    $repository = $factory->getRepository($parameters);
    $session = $repository->login($credentials, $workspace);

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
