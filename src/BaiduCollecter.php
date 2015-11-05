<?php

namespace Ltbl\Collecter;

/**
 * 百度（传度）采集器
 *
 */

class BaiduCollecter implements ICollecter {

    public $content;
    public $items;
    public $savePath;
    public $httpClient;

    /**
     * 初始化
     *
     * @param $httpClient 
     * @param $path
     *
     */
    public function __construct($httpClient, $savePath)
    {
        $this->httpClient = $httpClient;
        $this->savePath   = $savePath;

        if(!is_writable(dirname($this->savePath)) || !is_readable(dirname($this->savePath))) 
            throw new CollecterException('采集存放目录不可写');
    }

    /**
     * 收集器
     *
     * @param $url
     *
     * @return void
     */
    public function collect($url)
    {
        $this->crawl($url);
        $this->items();
        $this->save();
    }

    /**
     * 抓取内容
     *
     * @param $url
     *
     * @return void
     */
    public function crawl($url)
    {
        $res = $this->httpClient->request('get', $url);

        if($res->getStatusCode() != 200) 
            throw new CollecterException('采集HTTP错误:'. $res->getStatusCode());

        $this->content = $res->getBody();
    }


    /**
     * 获取内容的条目
     *
     * @return void
     */
    public function items()
    {

        if(empty($this->content))
            throw new CollecterException("没有采集到任何东西");

        $xml  = @simplexml_load_string($this->content, "SimpleXMLElement", LIBXML_NOCDATA);

        if($xml === false)
            throw new CollecterException('采集到内容不合法');

        $data = json_decode(json_encode($xml), true)['data'];

        $this->items = isset($data[0]) ? $data : [$data];
    }

    /**
     * 保存到指定路径
     *
     * @return void
     */
    public function save()
    {
        if(empty($this->items) || !is_array($this->items)) return;

        foreach($this->items as $item) {
            $newline = json_encode($item) . "\n";
            file_put_contents($this->savePath, $newline, FILE_APPEND | LOCK_EX);
        }
    }

}
