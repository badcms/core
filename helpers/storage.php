<?php

function app_path($path = "")
{
    return APP_ROOT."/".$path;
}

function public_path($path = "")
{
    return APP_ROOT."/public/".$path;
}

function config_path($path = "")
{
    return CONFIG_ROOT.$path;
}

function storage_path($path = "")
{
    return STORAGE_ROOT.$path;
}

function view_path($path = "")
{
    return APP_ROOT."/resources/views/".$path;
}
