<?php

namespace Ltbl\Collecter\Tests;

use Ltbl\Collecter\BaiduCollecter;

class BaiduCollecterTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {

        $this->httpClient = $this->getMockBuilder('HttpClient')
                                 ->setMethods(['request', 'getStatusCode', 'getBody'])
                                 ->getMock();
        $this->httpClient->method('request')->will($this->returnSelf());

        $this->savePath = test_data_path() . '/baidu_collecter.txt';
        touch($this->savePath);
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
     * @expectedExceptionMessage 采集存放路径不可写
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
    <channel>1013</channel>
    <data>
        <oappid><![CDATA[7481983]]></oappid>
        <category>402</category>
        <categoryname><![CDATA[角色扮演]]></categoryname>
        <subcate><![CDATA[]]></subcate>
        <bigmaplink><![CDATA[http://apps3.bdimg.com/store/static/kvt/b824266d1715e61cdade51685f60f5a3.jpg;http://apps2.bdimg.com/store/static/kvt/999c388194f341c07f15b3ff8e72a996.jpg;http://apps1.bdimg.com/store/static/kvt/66a4f03bec0122d73d54298688f342c0.jpg;http://apps2.bdimg.com/store/static/kvt/63d2ca7045ae9a98c373df7fd4b7a569.jpg;http://apps.bdimg.com/store/static/kvt/7d65ad37e0d129fcf19aa723d1da5025.jpg]]></bigmaplink>
        <sourcelink><![CDATA[]]></sourcelink>
        <title><![CDATA[谋定三国]]></title>
        <platform><![CDATA[Andriod]]></platform>
        <version><![CDATA[1.0.2]]></version>
        <minsdkversion><![CDATA[8]]></minsdkversion>
        <releasedate><![CDATA[2015-09-01]]></releasedate>
        <language><![CDATA[中文]]></language>
        <description><![CDATA[《谋定三国》作为一款三国策略类SLG游戏，将调兵遣将的战略精髓与沙场鏖战的热血拼杀完美结合在一起。文可羽扇纶巾，谈笑间樯橹灰飞烟灭；武能上阵夺帅，温酒时刀斩上将首级。文武争锋的宏大场面置于全景地图操作之上，千军万马弹指向前，纷飞战火洞若明烛。特殊地形与独有即时制战斗对抗模式，配合名将战法，兵种战术，成功复现三国时代挥军掩杀与锦囊秘策的霸气权谋。各种兵器，资源，官职，科技，围城，国战等特色玩法，让你穿越回三国乱世。无论是热血争霸，开疆扩土；还是权谋秘策，妙计退敌，各种战术千变万化，史册战役真实再现，谋定三国，助你三国称雄，谋定在胸！客服QQ：800032234 邮箱：800032234@b.qq.com 电话：010-56233711 400-660-9729 玩家群：336426925]]></description>
        <dpi><![CDATA[]]></dpi>
        <fee></fee>
        <packagemd5><![CDATA[8c2686607ff9347fb833bb8d11e1ee78]]></packagemd5>
        <keyword><![CDATA[]]></keyword>
        <packagesize><![CDATA[39629220]]></packagesize>
        <packageformat><![CDATA[apk]]></packageformat>
        <packagelink><![CDATA[http://m.baidu.com/api?action=redirect&token=duchuan_baidu&from=1012685v&type=app&dltype=new&tj=game_1494307_7926644_%E8%B0%8B%E5%AE%9A%E4%B8%89%E5%9B%BD&blink=c1e8687474703a2f2f6263732e39312e636f6d2f706373756974652d6465762f6368616e6e656c2f38633236383636303766663933343766623833336262386431316531656537382e61706be555&crversion=1&f=api@terminal_type]]></packagelink>
        <smallmaplink><![CDATA[http://apps.bdimg.com/store/static/kvt/d2cd45e0626ac9dc0bd1aaa5714e4704.png]]></smallmaplink>
        <appid><![CDATA[5477600]]></appid>
        <hotlevel><![CDATA[]]></hotlevel>
        <downloadnumber>0</downloadnumber>
        <browsenumber>0</browsenumber>
        <package_name><![CDATA[com.regin.gcld.mouding.BD]]></package_name>
        <developer_id><![CDATA[1311153386]]></developer_id>
        <developername><![CDATA[百度移动游戏]]></developername>
        <source><![CDATA[深圳力天保利]]></source>
        <sourcesite><![CDATA[http://duchuan13.baidu.com]]></sourcesite>
        <versioncode><![CDATA[4]]></versioncode>
        <status><![CDATA[update]]></status>
        <changelog><![CDATA[]]></changelog>
        <supportpad>0</supportpad>
        <ios_sourcelink><![CDATA[]]></ios_sourcelink>
        <aladdinflag>1</aladdinflag>
        <summary><![CDATA[【经典再续】
恢宏庞大的历史背景，引人入胜的精彩国战，感受昔日乱世，带你领略真实的三国纷争！
【任性画质】
振奋人心的格斗场面，多彩绚丽的技能效果，感受独特魅力，使你战斗气势越来越勇猛！
【最强PK】
最强单挑竞技，兄弟国战，万人跨服，其乐无穷的游戏体验，让你爽快战斗,称霸三国！
【海量福利】
每日签到，消耗送礼，全新活动，助你三国制霸之旅!
【全新玩法】
经典但不落俗，24小时制万人国战，开疆扩]]></summary>
        <server_name><![CDATA[]]></server_name>
        <server_time><![CDATA[]]></server_time>
        <server_start_test><![CDATA[1435806000]]></server_start_test>
        <server_end_test><![CDATA[]]></server_end_test>
        <server_test_type><![CDATA[不删档测试]]></server_test_type>
        <channel><![CDATA[1013]]></channel>
        <apk_id>7481983</apk_id>
        <product_id><![CDATA[5477600]]></product_id>
    </data>
    <data>
        <oappid><![CDATA[7481987]]></oappid>
        <category>402</category>
        <categoryname><![CDATA[角色扮演]]></categoryname>
        <subcate><![CDATA[]]></subcate>
        <bigmaplink><![CDATA[http://apps2.bdimg.com/store/static/kvt/32a0c7c89d04cfa312cee0e4d275a68f.jpg;http://apps2.bdimg.com/store/static/kvt/2db564ee67740ca5c0b8043a55824ca7.jpg;http://apps.bdimg.com/store/static/kvt/ffcabaabdb3788ed4a449efbe672e412.jpg;http://apps1.bdimg.com/store/static/kvt/d5ebc4f32218475e43768016fd943760.jpg;http://apps3.bdimg.com/store/static/kvt/cb5630bb2f76e754213f3b1560c13173.jpg]]></bigmaplink>
        <sourcelink><![CDATA[]]></sourcelink>
        <title><![CDATA[英雄啪啪啪]]></title>
        <platform><![CDATA[Andriod]]></platform>
        <version><![CDATA[0.4.6]]></version>
        <minsdkversion><![CDATA[9]]></minsdkversion>
        <releasedate><![CDATA[2015-09-01]]></releasedate>
        <language><![CDATA[中文]]></language>
        <description><![CDATA[《英雄啪啪啪》是明珠游戏旗下奇乐工作室推出的WCG级微操作竞技手游大作。游戏中拥有上百位独特的经典英雄形象，无论是玩LoL的小学生，还是玩DotA/DotA2的各路大神，都可以找到曾经与你并肩作战的心爱英雄。飘逸灵动的敏捷英雄，变幻莫测的智力英雄，彪悍强大的力量型英雄，在游戏中拥有各具特色的华丽杀招，演绎精彩绝伦的团战效果，足以让你享受电子竞技的超爽快感。 《英雄啪啪啪》上手轻松，一秒钟足以让你成为翻手为云覆手为雨的竞技高手。游戏中超过1000个独特副本，充满了无尽的挑战，也暗藏了让你实力飙升的稀缺资源，同时，在夺宝地图的迷雾下，也充斥着其他玩家汹涌澎湃的火热战意。 只有在《英雄啪啪啪》中才能完美体验指尖上的电子竞技！]]></description>
        <dpi><![CDATA[]]></dpi>
        <fee></fee>
        <packagemd5><![CDATA[61becb75867b4bb5bf3b831fc7389d60]]></packagemd5>
        <keyword><![CDATA[]]></keyword>
        <packagesize><![CDATA[106000716]]></packagesize>
        <packageformat><![CDATA[apk]]></packageformat>
        <packagelink><![CDATA[http://m.baidu.com/api?action=redirect&token=duchuan_baidu&from=1012685v&type=app&dltype=new&tj=game_1495647_7926648_%E8%8B%B1%E9%9B%84%E5%95%AA%E5%95%AA%E5%95%AA&blink=c1e8687474703a2f2f6263732e39312e636f6d2f706373756974652d6465762f6368616e6e656c2f36316265636237353836376234626235626633623833316663373338396436302e61706be555&crversion=1&f=api@terminal_type]]></packagelink>
        <smallmaplink><![CDATA[http://apps.bdimg.com/store/static/kvt/ae94639103418a815ce225c2ee761369.png]]></smallmaplink>
        <appid><![CDATA[5453622]]></appid>
        <hotlevel><![CDATA[]]></hotlevel>
        <downloadnumber>0</downloadnumber>
        <browsenumber>0</browsenumber>
        <package_name><![CDATA[com.pip.saga.baidu]]></package_name>
        <developer_id><![CDATA[1218043585]]></developer_id>
        <developername><![CDATA[掌上明珠]]></developername>
        <source><![CDATA[深圳力天保利]]></source>
        <sourcesite><![CDATA[http://duchuan13.baidu.com]]></sourcesite>
        <versioncode><![CDATA[3]]></versioncode>
        <status><![CDATA[update]]></status>
        <changelog><![CDATA[《英雄啪啪啪》是明珠游戏旗下奇乐工作室推出的WCG级微操作竞技手游大作。游戏中拥有上百位独特的经典英雄形象，无论是玩LoL的小学生，还是玩DotA/DotA2的各路大神，都可以找到曾经与你并肩作战的心爱英雄。飘逸灵动的敏捷英雄，变幻莫测的智力英雄，彪悍强大的力量型英雄，在游戏中拥有各具特色的华丽杀招，演绎精彩绝伦的团战效果，足以让你享受电子竞技的超爽快感。 《英雄啪啪啪》上手轻松，一秒钟足以让你成为翻手为云覆手为雨的竞技高手。游戏中超过1000个独特副本，充满了无尽的挑战，也暗藏了让你实力飙升的稀缺资源，同时，在夺宝地图的迷雾下，也充斥着其他玩家汹涌澎湃的火热战意。 只有在《英雄啪啪啪》中才能完美体验指尖上的电子竞技！]]></changelog>
        <supportpad>0</supportpad>
        <ios_sourcelink><![CDATA[]]></ios_sourcelink>
        <aladdinflag>0</aladdinflag>
        <summary><![CDATA[《英雄啪啪啪》是明珠游戏旗下奇乐工作室推出的WCG级微操作竞技手游大作。游戏中拥有上百位独特的经典英雄形象，无论是玩LoL的小学生，还是玩DotA/DotA2的各路大神，都可以找到曾经与你并肩作战的心爱英雄。飘逸灵动的敏捷英雄，变幻莫测的智力英雄，彪悍强大的力量型英雄，在游戏中拥有各具特色的华丽杀招，演绎精彩绝伦的团战效果，足以让你享受电子竞技的超爽快感。
《英雄啪啪啪》上手轻松，一秒钟足以让你成]]></summary>
        <server_name><![CDATA[]]></server_name>
        <server_time><![CDATA[]]></server_time>
        <server_start_test><![CDATA[]]></server_start_test>
        <server_end_test><![CDATA[]]></server_end_test>
        <server_test_type><![CDATA[]]></server_test_type>
        <channel><![CDATA[1013]]></channel>
        <apk_id>7481987</apk_id>
        <product_id><![CDATA[5453622]]></product_id>
    </data>
    <count>845</count>
</alldata>
EOD;

        $collecter = new BaiduCollecter($this->httpClient, $this->savePath);

        $collecter->content = $content;
        $collecter->items();

        $this->assertEquals('7481983', $collecter->items[0]['apk_id']);
        $this->assertEquals('http://m.baidu.com/api?action=redirect&token=duchuan_baidu&from=1012685v&type=app&dltype=new&tj=game_1495647_7926648_%E8%8B%B1%E9%9B%84%E5%95%AA%E5%95%AA%E5%95%AA&blink=c1e8687474703a2f2f6263732e39312e636f6d2f706373756974652d6465762f6368616e6e656c2f36316265636237353836376234626235626633623833316663373338396436302e61706be555&crversion=1&f=api@terminal_type', $collecter->items[1]['packagelink']);
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

        $referenceFile = test_data_path() . '/baidu_collecter.txt';
        $this->assertEquals(file_get_contents($this->savePath), file_get_contents($referenceFile));
    }
}
