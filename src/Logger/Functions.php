<?php

namespace BadCMS\Logger;

class Functions
{
    const load = 1;
}

function log($message, $context = null)
{
    $fp = fopen(storage_path('logs/badcms-'.date('Ymd').".log"), 'a+');

    fputs($fp, PHP_EOL."[".date('Y-m-d H:i:s')."] \t");
    fputs($fp, $message.PHP_EOL);
    if ($context) {
        fputs($fp, var_export($context).PHP_EOL);
    }

    fclose($fp);
}

