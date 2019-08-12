<?php

    $object = (object) [
        "foo" => "bar",
        "property" => "value"
    ];

    $data = [
        "string_1" => "this is a test",
        "string_2" => "another test",
        "my_struct" => [
            "item_2" => "world",
            "item_1" => "hello"
        ],
        "my_array" => [
            "this",
            "is",
            "an",
            "array"
        ],
        "my_boolean" => true,
        "my_object" => $object
    ];

    include "dump.php";

    $dump = new Dump();
    $dump->Output($data);

?>
