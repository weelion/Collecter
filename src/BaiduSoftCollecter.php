<?php

namespace Ltbl\Collecter;

/**
 * 百度（应用）采集器
 *
 */

class BaiduSoftCollecter implements ICollecter {

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

        $total = 500/20;
        for ($i = 1; $i < $total; $i++) {
            $offset = ($i - 1) * 20;
            $url = preg_replace('/pn=\d+/','pn='.$offset, $url);
            echo $url . "\n";
            $this->crawl($url);
        }
        
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


        // 重试两次
        if($res->getStatusCode() != 200) {
            $res = $this->httpClient->request('get', $url);
            if($res->getStatusCode() != 200) {
                sleep(1);
                $res = $this->httpClient->request('get', $url);
                if($res->getStatusCode() != 200) {
                    throw new CollecterException('采集HTTP错误:' . $res->getStatusCode());
                }
            }
        }

        $content = json_decode($res->getBody());
        if(! empty($content->result->apps)) {
            foreach($content->result->apps as $v) {
                $this->items[] =  json_encode($v);
            }
        }
        
    }

    public function items()
    {

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
            $newline = $item . "\n";

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
