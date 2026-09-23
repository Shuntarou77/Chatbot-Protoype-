<?php
/**
 * CartoGo Manila Autoloader & Bootstrap
 */

// Load vendor autoloader for MongoDB driver & dependencies from shared vendor
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../insurance-chatbot/vendor/autoload.php')) {
    require_once __DIR__ . '/../insurance-chatbot/vendor/autoload.php';
}

// Register CartoGo namespace autoloader
spl_autoload_register(function ($class) {
    $prefix = 'CartoGo\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}, true, true);
