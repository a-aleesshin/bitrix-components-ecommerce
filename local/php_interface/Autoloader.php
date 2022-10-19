<?php

LocalAutoloader::register();

class LocalAutoloader
{

    public static function register()
    {
        // Register any existing autoloader function with SPL, so we don't get any clashes
        if (function_exists('__autoload'))
            spl_autoload_register('__autoload');

        // Register ourselves with SPL
        if (version_compare(PHP_VERSION, '5.3.0') >= 0)
            return spl_autoload_register(array('LocalAutoloader', 'load'), true, true);
        else
            return spl_autoload_register(array('LocalAutoloader', 'load'));

    }

    public static function load($pClassName)
    {
        if (
            (class_exists($pClassName, false))
            || (strpos($pClassName, 'Local') !== 0)
        )
            return false;

        $pClassFilePath = __DIR__.str_replace(array('\\', 'Local'), array(DIRECTORY_SEPARATOR, ''), $pClassName).'.php';

        if ((file_exists($pClassFilePath) === false) || (is_readable($pClassFilePath) === false))
            return false;

        require($pClassFilePath);

    }

}
