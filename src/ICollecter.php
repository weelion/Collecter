<?php

namespace Ltbl\Collecter;

/**
 * 采集器接口
 */

interface ICollecter {

    // 本地化 API
    public  function collect($url);

    // 爬取 API 内容
    public function crawl($url);

    // 分离 API 条目
    public function items();

    // 存储
    public function save();

}