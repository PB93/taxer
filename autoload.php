<?php

// autoload.php

/**
 * Simple autoloader function to automatically include classes
 */
function my_autoloader($class)
{
    // Define the path where your classes are stored
    $path = 'src/';  // Change this path to the directory where your classes are stored

    // Convert class name to a valid file path
    $file = $path . str_replace('\\', '/', $class) . '.php';

    // Check if the file exists and require it
    if (file_exists($file)) {
        require_once $file;
    }
}

// Register the autoloader function
spl_autoload_register('my_autoloader');