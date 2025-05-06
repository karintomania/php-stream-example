<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use StreamTest\StreamScanner;


function startStreamServer() {
    print("Starting the server".PHP_EOL);
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

    return $handle;
}

function shutdownStreamServer($handle) {
    print("Shutting down the server".PHP_EOL);
    proc_terminate($handle);
    proc_close($handle);
}

$handle = startStreamServer();

$client = new Client();

$res = $client->get('http://localhost', ['stream' => true]);

// try without stream option
// $res = $client->get('http://localhost');

/** @var StreamInterface $bodyStream */
$bodyStream = $res->getBody();

$handler = new StreamScanner();

foreach($handler->__invoke($bodyStream) as $object) {
    printf("Got json from the stream! [id: %d, name: %s]" . PHP_EOL, $object->id, $object->name);
}

shutdownStreamServer($handle);
