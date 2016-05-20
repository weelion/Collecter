<?php

namespace Ltbl\Collecter;

/**
 * 百度（传度）采集器
 *
 */

class BaiduGameCollecter implements ICollecter {

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
            throw new CollecterException('采集HTTP错误:' . $res->getStatusCode());

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

        if(! $this->getXml() )
            throw new CollecterException('采集到内容不合法');

        $data = $this->xmlToArray($this->getXml());

        $this->items = isset($data['data'][0]) ? $data['data'] : [$data['data']];
    }

    public function getXml()
    {
        return  @simplexml_load_string(
            $this->content, 
            "SimpleXMLElement", 
            LIBXML_NOCDATA
        );
    }

    public function xmlToArray($xml)
    {
        return json_decode(json_encode($xml), true);
    }

    /**
     * 保存到指定路径
     *
     * @return void
     */
    public function save()
    {
        if ($this->isWriteable()) { 
            throw new CollecterException('采集存放目录不可写');
        }

        if (is_array($this->items)) {
            $this->saveToFile();
        }
    }

    public function saveToFile() 
    {
        foreach($this->items as $item) {
            $newline = json_encode($item) . "\n";

            file_put_contents(
                $this->savePath, 
                $newline, 
                FILE_APPEND | LOCK_EX
            );
        }
    }


    public function isWriteable() 
    {
        return ! is_writable(dirname($this->savePath));
    }

}
