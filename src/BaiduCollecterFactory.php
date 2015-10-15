<?php

namespace Ltbl\Collecter;

/**
 * 百度（度传）采集器具体工厂
 */

class BaiduCollecterFactory extends ACollecterFactory {

    public function __construct($httpClient, $savePath)
    {
        $this->collecter = new BaiduCollecter($httpClient, $savePath);
    }
    
}