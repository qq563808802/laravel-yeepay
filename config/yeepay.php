<?php

/*
 * This file is part of Commidity
 *
 * (c) Wangzd <wangzhoudong@foxmail.com>
 *
 */

return [
    /**
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug'  => true,

    'account' => '10000447996', //商家账号
    'merchantPrivateKey' => 'jj3Q1h0H86FZ7CD46Z5Nr35p67L199WdkgETx85920n128vi2125T9KY2hzv', //商家账号

    'aesKey' => 'jj3Q1h0H86FZ7CD4', //商家账号

    'baseUrl' => 'http://o2o.yeepay.com/zgt-api/api', //接口地址

    /**
     * 日志配置
     *
     * level: 日志级别，可选为：
     *                 debug/info/notice/warning/error/critical/alert/emergency
     * file：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log' => [
        'level' => env('YEEPAY_LOG_LEVEL', 'debug'),
        'file'  => env('YEEPAY_LOG_FILE', storage_path('logs/yeepay.log')),
    ],

];
