<?php

namespace Weelion\Collecter;

/**
 *  采集器工厂接口
 */

abstract class ACollecterFactory {

    protected $collecter  = null;

    abstract function __construct($httpClient, $savePath);

    /**
     * 采集 API 内容
     */
    public function collect($url)
    {
        $this->collecter->collect($url);
    }
}