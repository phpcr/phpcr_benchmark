# PHPCR Benchmarking

The purpose of this repository is to compare different PHPCR implementations.

Run ``composer install`` and then copy the ``cli-config.php.dist`` to ``cli-config.php`` and adjust as needed.

Finally run ``php index.php`` or ``php index.php benchmark``.

It is possible to also configure the number of nodes to add per batch via ``--count`` and the number of
repetitions via ``--sections``.

For example: ``php index.php benchmark --count 4 --sections 7`` (note that no equal sign is supported)

Once the command has been run once, it can optionally be run one more time with ``php index.php benchmark --append``
to add one more "section" on top of the existing data to see how the performance is without having lots of previous
inserts/gets before. This can of course be combined with the other parameters.

Finally it is possible to run some standard jackalope commands via ``./vendor/bin/jackalope``.

## TODO

* Use Travis CI to run the benchmarks (requires refactoring the index.php to pull the configuration from else where
* Make the stored nodes a bit more complex
* Investigate slow downs (especially insert performance, why SQLite is not using an index for subpath queries, slow downs in get by path)

## Results

See also the Travis-CI [![Build Status](https://travis-ci.org/lsmith77/phpcr_benchmark.svg?branch=master)](https://travis-ci.org/lsmith77/phpcr_benchmark).

### Jackalope Doctrine DBAL MySQL

```
lsmith@localhost phpcr_benchmark (master)$ php index.php benchmark --count 100 --sections 100
Inserting 100 nodes (total 100) took '279' ms.
Getting a node by path took '1' ms.
Searching a node by property took '14' ms.
Searching a node by property in a subpath took '5' ms.
Inserting 100 nodes (total 200) took '580' ms.
Getting a node by path took '2' ms.
Searching a node by property took '30' ms.
Searching a node by property in a subpath took '11' ms.
Inserting 100 nodes (total 300) took '871' ms.
Getting a node by path took '3' ms.
Searching a node by property took '42' ms.
Searching a node by property in a subpath took '18' ms.
Inserting 100 nodes (total 400) took '1188' ms.
Getting a node by path took '4' ms.
Searching a node by property took '56' ms.
Searching a node by property in a subpath took '26' ms.
Inserting 100 nodes (total 500) took '1503' ms.
Getting a node by path took '5' ms.
Searching a node by property took '72' ms.
Searching a node by property in a subpath took '33' ms.
Inserting 100 nodes (total 600) took '1824' ms.
Getting a node by path took '6' ms.
Searching a node by property took '86' ms.
Searching a node by property in a subpath took '38' ms.
Inserting 100 nodes (total 700) took '2147' ms.
Getting a node by path took '7' ms.
Searching a node by property took '101' ms.
Searching a node by property in a subpath took '45' ms.
Inserting 100 nodes (total 800) took '2753' ms.
Getting a node by path took '8' ms.
Searching a node by property took '117' ms.
Searching a node by property in a subpath took '50' ms.
Inserting 100 nodes (total 900) took '3057' ms.
Getting a node by path took '9' ms.
Searching a node by property took '135' ms.
Searching a node by property in a subpath took '55' ms.

...

Inserting 100 nodes (total 10000) took '31670' ms.
Getting a node by path took '130' ms.
Searching a node by property took '4955' ms.
Searching a node by property in a subpath took '551' ms.
```

```
lsmith@localhost phpcr_benchmark (master)$ php index.php benchmark --count 100 --sections 100 --append
Inserting 100 nodes (total 10100) took '303' ms.
Getting a node by path took '1' ms.
Searching a node by property took '113' ms.
Searching a node by property in a subpath took '6' ms.
```

### Jackalope Doctrine DBAL SQLite

```
lsmith@localhost phpcr_benchmark (master)$ php index.php benchmark --count 100 --sections 100
Inserting 100 nodes (total 100) took '131' ms.
Getting a node by path took '1' ms.
Searching a node by property took '22' ms.
Searching a node by property in a subpath took '11' ms.
Inserting 100 nodes (total 200) took '262' ms.
Getting a node by path took '2' ms.
Searching a node by property took '47' ms.
Searching a node by property in a subpath took '28' ms.
Inserting 100 nodes (total 300) took '393' ms.
Getting a node by path took '3' ms.
Searching a node by property took '77' ms.
Searching a node by property in a subpath took '59' ms.
Inserting 100 nodes (total 400) took '521' ms.
Getting a node by path took '3' ms.
Searching a node by property took '117' ms.
Searching a node by property in a subpath took '94' ms.
Inserting 100 nodes (total 500) took '652' ms.
Getting a node by path took '3' ms.
Searching a node by property took '175' ms.
Searching a node by property in a subpath took '133' ms.
Inserting 100 nodes (total 600) took '787' ms.
Getting a node by path took '3' ms.
Searching a node by property took '226' ms.
Searching a node by property in a subpath took '180' ms.
Inserting 100 nodes (total 700) took '921' ms.
Getting a node by path took '4' ms.
Searching a node by property took '295' ms.
Searching a node by property in a subpath took '231' ms.
Inserting 100 nodes (total 800) took '1055' ms.
Getting a node by path took '5' ms.
Searching a node by property took '359' ms.
Searching a node by property in a subpath took '291' ms.
Inserting 100 nodes (total 900) took '1190' ms.
Getting a node by path took '6' ms.
Searching a node by property took '437' ms.
Searching a node by property in a subpath took '357' ms.

...

Inserting 100 nodes (total 10000) took '14616' ms.
Getting a node by path took '98' ms.
Searching a node by property took '40642' ms.
Searching a node by property in a subpath took '39664' ms.
```

```
lsmith@localhost phpcr_benchmark (master)$ php index.php benchmark --count 100 --sections 100 --append
Inserting 100 nodes (total 10100) took '140' ms.
Getting a node by path took '0' ms.
Searching a node by property took '763' ms.
Searching a node by property in a subpath took '735' ms.
```

### Jackalope Jackrabbit 2.x

```
lsmith@localhost phpcr_benchmark (master)$ php index.php benchmark --count 100 --sections 100
Inserting 100 nodes (total 100) took '103' ms.
Getting a node by path took '2' ms.
Searching a node by property took '7' ms.
Searching a node by property in a subpath took '4' ms.
Inserting 100 nodes (total 200) took '223' ms.
Getting a node by path took '4' ms.
Searching a node by property took '22' ms.
Searching a node by property in a subpath took '10' ms.
Inserting 100 nodes (total 300) took '335' ms.
Getting a node by path took '7' ms.
Searching a node by property took '38' ms.
Searching a node by property in a subpath took '17' ms.
Inserting 100 nodes (total 400) took '459' ms.
Getting a node by path took '9' ms.
Searching a node by property took '48' ms.
Searching a node by property in a subpath took '22' ms.
Inserting 100 nodes (total 500) took '591' ms.
Getting a node by path took '11' ms.
Searching a node by property took '63' ms.
Searching a node by property in a subpath took '27' ms.
Inserting 100 nodes (total 600) took '699' ms.
Getting a node by path took '13' ms.
Searching a node by property took '78' ms.
Searching a node by property in a subpath took '34' ms.
Inserting 100 nodes (total 700) took '842' ms.
Getting a node by path took '16' ms.
Searching a node by property took '92' ms.
Searching a node by property in a subpath took '40' ms.
Inserting 100 nodes (total 800) took '947' ms.
Getting a node by path took '18' ms.
Searching a node by property took '106' ms.
Searching a node by property in a subpath took '46' ms.
Inserting 100 nodes (total 900) took '1053' ms.
Getting a node by path took '20' ms.
Searching a node by property took '120' ms.
Searching a node by property in a subpath took '55' ms.

...

Inserting 100 nodes (total 10000) took '7642' ms.
Getting a node by path took '199' ms.
Searching a node by property took '1506' ms.
Searching a node by property in a subpath took '492' ms.
```

```
lsmith@localhost phpcr_benchmark (master)$ php index.php benchmark --count 100 --sections 100 --append
Inserting 100 nodes (total 10100) took '68' ms.
Getting a node by path took '2' ms.
Searching a node by property took '21' ms.
Searching a node by property in a subpath took '16' ms.
```
