<?php
// Simple code to return JSON stream data
header("Content-Type: application/stream+json");

for ($i = 0; $i < 10; $i ++) {
    //return '\n' separated JSON
    printf(
        "%s\n",
        json_encode(["id" => $i, "name" => "item $i"])
    );

    // Emulate a delay to make streaming obvious
    sleep(1);
}
