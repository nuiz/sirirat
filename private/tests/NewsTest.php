<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 9/1/2558
 * Time: 12:41
 */

class NewsTest extends PHPUnit_Framework_TestCase {
    /**
     * @var \Main\Service\NewsService $service;
     */
    protected $service, $context, $addItem;

    protected function setUp()
    {
        parent::setUp();
        $this->service = \Main\Service\NewsService::getInstance();
        $this->context = new \Main\Context\Context();
    }

    public function testAddNews(){
        $insertParam = ['title'=> 'เพิ่มข่าว', 'content'=> 'รายละเอียดข่าว'];
        $item = $this->service->add($insertParam, $this->context);
        $this->assertEquals($insertParam['title'], $item['title']);
        $this->assertEquals($insertParam['content'], $item['content']);

        $this->addItem = $item;
    }

    public function testGetsNews(){
        return $this->service->gets([], $this->context);
    }

    public function testEditNews(){

    }

    public function testDeleteNews(){
        $news = $this->testGetsNews();
        if(isset($news['data'][0])){
            $id = $news['data'][0]['id'];
            $res = $this->service->delete($id, $this->context);
            $this->assertEquals(true, $res);
        }
        else {
            $this->testAddNews();
            $this->testDeleteNews();
        }
    }
}