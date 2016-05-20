<?php

namespace Ltbl\Collecter;

/**
 * 百度（应用）采集器具体工厂
 */

class BaiduSoftCollecterFactory extends ACollecterFactory {

    public function __construct($httpClient, $savePath)
    {
        $this->collecter = new BaiduSoftCollecter($httpClient, $savePath);
    }
    
}