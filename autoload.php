<?php
function my_autoloader($class)
{
    $path = 'src/';
    $file = $path . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('my_autoloader');
