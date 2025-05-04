<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use StreamTest\StreamHandler;

$descriptorspec = [
    ['pipe', 'r'],
    ['pipe', 'w'],
    STDOUT,
];

$handle = proc_open(
    command: 'php --server=localhost:80 /app/src/server.php',
    descriptor_spec: $descriptorspec,
    pipes: $pipes,
);

// wait a bit for the server to start
sleep(1);


$client = new Client();
$res = $client->get('http://localhost', ['stream' => true]);

/** @var StreamInterface $bodyStream */
$bodyStream = $res->getBody();

$handler = new StreamHandler();

foreach($handler->__invoke($bodyStream) as $object) {
    printf("id: %d, name: %s" . PHP_EOL, $object->id, $object->name);
}

proc_terminate($handle);
