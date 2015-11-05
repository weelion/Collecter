<?php

use Ltbl\Collecter\BaiduCollecter;

class BaiduCollecterTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {

        $this->httpClient = $this->getMockBuilder('HttpClient')
                                 ->setMethods(['request', 'getStatusCode', 'getBody'])
                                 ->getMock();
        $this->httpClient->method('request')->will($this->returnSelf());

        $this->savePath = test_data_path() . '/baidu_collecter.txt';
        file_put_contents($this->savePath, '');
    }

    public function tearDown()
    {
        unlink($this->savePath);
    }

    /**
     * 测试构造函数
     */
    public function testConstruct()
    {
        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);
        $this->assertInstanceOf('\Ltbl\Collecter\BaiduCollecter', $collecter);
    }

    /**
     * 测试目录不可写异常
     * @expectedException        Ltbl\Collecter\CollecterException
     * @expectedExceptionMessage 采集存放目录不可写
     */
    public function testSavePathNotWritable()
    {
        $collecter = new BaiduCollecter($this->httpClient, '');
    }

     /**
      * 测试抓取异常
      *
      * @expectedException              Ltbl\Collecter\CollecterException
      * @expectedExceptionMessageRegExp /采集HTTP错误:\d+/
      */
    public function testCrawlException()
    {
        // $this->httpClient->method('request')->will($this->returnSelf());
        $this->httpClient->method('getStatusCode')->willReturn(404);

        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);

        $collecter->crawl('http://xxx');
    }

    /**
     * 正确的抓取
     */
    public function testRightCrawl()
    {
        $this->httpClient->method('getStatusCode')->willReturn(200);
        $this->httpClient->method('getBody')->willReturn('xxx');

        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);

        $collecter->crawl('somehost');
        $this->assertEquals('xxx', $collecter->content);
    }

    /**
     * 测试 items 参数为空异常
     *
     * @expectedException        Ltbl\Collecter\CollecterException
     * @expectedExceptionMessage 没有采集到任何东西
     */
    public function testItemsEmptyArgumentException()
    {
        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);
        $collecter->content = '';
        $collecter->items();
    }

    /**
     * 测试 items 内容不合法异常
     *
     * @expectedException        Ltbl\Collecter\CollecterException
     * @expectedExceptionMessage 采集到内容不合法
     */
    public function testItemsNotValidContentException()
    {
        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);
        $collecter->content = 'valid content';
        $collecter->items();
    }


    /**
     * 获取正确的 items
     */
    public function testRightItems()
    {
        $content = <<<EOD
<?xml version="1.0" encoding="UTF-8" ?>
<alldata>
    <version>1.0</version>
    <channel>8888</channel>
    <data>
        <apk_id><![CDATA[99]]></apk_id>
        <category>1</category>
        <links><![CDATA[http://test.com/1.jpg;http://test.com/2.jpg]]></links>
        <title><![CDATA[手机助手]]></title>
        <summary><![CDATA[手机助手简介]]></summary>
    </data>
    <data>
        <apk_id><![CDATA[100]]></apk_id>
        <category>2</category>
        <links><![CDATA[http://test.com/1.jpg;http://test.com/2.jpg]]></links>
        <title><![CDATA[游戏中心]]></title>
        <summary><![CDATA[游戏中心简介]]></summary>
    </data>
    <count>2</count>
</alldata>
EOD;

        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);

        $collecter->content = $content;
        $collecter->items();

        $this->assertEquals('99', $collecter->items[0]['apk_id']);
        $this->assertEquals('100', $collecter->items[1]['apk_id']);
        $this->assertEquals('游戏中心', $collecter->items[1]['title']);
        $this->assertEquals(2, count($collecter->items));
    }

    /**
     * 测试 items 空时保存
     */
    public function testEmptyItemsSave()
    {
        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);
        $originTime = filemtime($this->savePath);
        $collecter->items = '';
        $collecter->save();

        $this->assertEquals($originTime, filemtime($this->savePath));
    }

    /**
     * 测试 items 是字符串时保存
     */
    public function testStringItemsSave()
    {
        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);
        $originTime = filemtime($this->savePath);

        $collecter->items = 'sdf';
        $collecter->save();

        $this->assertEquals($originTime, filemtime($this->savePath));
    }

    /**
     * 测试 items 正确保存
     */
    public function testRightSave()
    {
        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);

        $collecter->items = [
            ['aa' => 'aa', 'bb' => 'bb'],
            ['cc' => 'cc', 'dd' => 'dd']
        ];

        $collecter->save();

        $referenceFile = test_data_path() . '/baidu_collecter_reference.txt';
        $this->assertEquals(file_get_contents($this->savePath), file_get_contents($referenceFile));
    }
}
