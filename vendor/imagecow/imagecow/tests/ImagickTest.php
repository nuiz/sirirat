<?php
include_once dirname(__DIR__).'/src/autoloader.php';
include_once __DIR__.'/ImageTest_.php';

use Imagecow\Image;

class ImagickTest extends ImageTest_
{
    protected static $library = 'Imagick';
}
