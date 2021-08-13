<?php

class Functions
{
    const load = 1;
}

const CACHE_MISS = -1;
const CACHE_HIT = 1;

/**
 * Проверка времени модификации файла
 *
 * @param $file
 * @param $timestamp
 *
 * @return bool|int
 */
function fileModifiedAfter(string $file, int $timestamp): bool
{
    $stat = @stat($file);
    if (!$stat) {
        return CACHE_MISS;
    }

    return $stat['mtime'] > $timestamp;
}

function cache($file, $callback)
{
    // TODO: write cache for files

    $cacheFile = storage_path('cache/'.substr($file, strlen(APP_ROOT)));

    dd($cacheFile);

    if (fileModifiedAfter($file)) {
        if (is_string($callback)) {
            ;
        }
    }// если сохраняться будет строка
}
