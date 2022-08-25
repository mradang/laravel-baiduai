<?php

namespace mradang\LaravelBaiduAI;

class BaiduAI extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-baiduai';
    }
}
