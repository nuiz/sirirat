<?php
include_once dirname(__DIR__).'/src/autoloader.php';

use Imagecow\Image;
use Imagecow\Utils\IconExtractor;

class IconTest extends PHPUnit_Framework_TestCase
{
    public function testIcon ()
    {
    	$file = __DIR__.'/marker/favicon.ico';
        $tmpFile = __DIR__.'/marker/tmp.favicon.png';

    	$icon = new IconExtractor($file);

		$image = $icon->getBetterQuality();

		$this->assertSame(256, $image->getWidth());
		$this->assertSame(256, $image->getHeight());

		//Save
        $image->save($tmpFile);

        $this->assertTrue(is_file($tmpFile));

        $image = Image::create($tmpFile);

        $this->assertSame(256, $image->getWidth());
        $this->assertSame(256, $image->getHeight());
    }
}
