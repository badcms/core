<?php

namespace BadCMS\Storage;

class Functions
{
    const load = 1;
}

const STORAGE_TYPE_JSON = "JSON";
const STORAGE_TYPE_SERIALIZED = "SERIALIZED";
const STORAGE_TYPE_PHP = "PHP";

function eachEntry($dir, $callbackFunc)
{
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            switch (filetype($dir.$entry)) {
                case 'dir':
                    if (in_array($entry, ['..', '.'])) {
                        break;
                    }
                    eachEntry($dir.$entry, $callbackFunc);
                    break;
                case 'file':
                    $callbackFunc($dir.$entry);
                    break;
            }
        }
        closedir($handle);
    }
}

/**
 * @param  string  $path
 * @param  string  $type  STORAGE_TYPE_JSON, STORAGE_TYPE_SERIALIZED, STORAGE_TYPE_PHP
 *
 * @return array
 */
function create($path, $type = "JSON")
{
    list($read, $write) = getStorageFunctions($type, $path);

    return [
        "src" => $path,
        "type" => $type,
        "read" => $read,
        "write" => $write,
    ];
}

function getStorageFunctions($type, $path)
{
    list($read, $write) = [
        "BadCMS\\Storage\\read$type",
        "BadCMS\\Storage\\write$type",
    ];

    $storage = [
        function () use ($read, $path) {
            return $read($path);
        },
        function ($data) use ($write, $path) {
            return $write($path, $data);
        },
    ];

    return $storage;
}

function saveUsers($users)
{
    usort($users, function ($first, $second) {
        return strcmp($first["username"], $second["username"]);
    });

    file_put_contents(storage_path('db/users.db'), serialize($users));
}

/**
 * JSON Storage functions
 */
function readJSON($file)
{
    return json_decode(file_get_contents($file), JSON_OBJECT_AS_ARRAY);
}

function writeJSON($file, $data)
{
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}


/**
 * SERIALIZED Storage functions
 */
function readSERIALIZED($file)
{
    return unserialize(file_get_contents($file));
}

function writeSERIALIZED($file, $data)
{
    return file_put_contents($file, serialize($data));
}

/**
 * PHP Storage functions
 */
function readPHP($file)
{
    return require $file;
}

function writePHP($file, $data)
{
    $php = "<?php return %DATA%;";
    $php = str_replace("%DATA%", var_export($data, true), $php);

    return file_put_contents($file, $php);
}
