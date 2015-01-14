<?php
function ImagecowLoader($className)
{
    if (strpos($className, 'Imagecow') !== 0) {
        return;
    }

    $className = substr($className, 9);
    $fileName = __DIR__.'/';

    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
    }

    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

    if (is_file($fileName)) {
        require $fileName;
    }
}

spl_autoload_register('ImagecowLoader');
