<?php
// +----------------------------------------------------------------------
// | Easemob [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2017/1/5 Time: 上午10:49
// +----------------------------------------------------------------------
namespace limx\tools;

class Easemob
{
    const URL = 'https://a1.easemob.com';

    private $client_id;
    private $client_secret;
    private $org_name;
    private $app_name;
    private $url;
    private $debug;
    private $storageAdapter;

    /**
     * 初始化环形参数
     *
     * @param array $options
     * @param $options ['client_id']
     * @param $options ['client_secret']
     * @param $options ['org_name']
     * @param $options ['app_name']
     */
    public function __construct($options, $debug = false)
    {
        $this->debug = $debug;
        $paramsMap = array(
            'client_id',
            'client_secret',
            'org_name',
            'app_name'
        );
        foreach ($paramsMap as $paramsName) {
            if (!isset($options[$paramsName])) {
                throw new \InvalidArgumentException("初始化未设置[{$paramsName}]");
            } else {
                $this->$paramsName = $options[$paramsName];
            }
        }
        $this->url = self::URL . '/' . $this->org_name . '/' . $this->app_name;
    }
}