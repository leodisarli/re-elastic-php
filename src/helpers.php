<?php

function showLog(
    $content
) {
    $time = date('Y-m-d H:i:s');
    $text = '[' . $time . '] ' . $content;
    print_r($text);
    echo "\n";
}

function dd(
    $content
) {
    echo "\n\n";
    print_r($content);
    echo "\n";
    die;
}