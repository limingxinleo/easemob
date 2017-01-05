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

use limx\func\Curl;

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
    private $token_path;

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
        $paramsMap = [
            'client_id',
            'client_secret',
            'org_name',
            'app_name'
        ];
        foreach ($paramsMap as $paramsName) {
            if (!isset($options[$paramsName])) {
                throw new \InvalidArgumentException("初始化未设置[{$paramsName}]");
            } else {
                $this->$paramsName = $options[$paramsName];
            }
        }
        $this->url = self::URL . '/' . $this->org_name . '/' . $this->app_name;
    }

    /**
     * [setTokenPath desc]
     * @desc 设置token缓存文件地址
     * @author limx
     * @param string $path
     */
    public function setTokenPath($path = '')
    {
        if (!empty($path) && Str::length($path) > 0) {
            $this->token_path = $path;
        }
    }

    /**
     * 设置
     * @param callable $callback
     */
    public function setStorageAdapter($callback)
    {
        if (is_callable($callback)) {
            $this->storageAdapter = $callback;
        }
    }

    /**
     * 持久化token
     * @param bool $saveToken
     * @return bool
     */
    private function cacheToken($saveToken = false)
    {
        $cacheFilePath = $this->token_path;
        if ($saveToken) {
            $saveToken['expires_in'] = $saveToken['expires_in'] + time();
            if ($this->storageAdapter) {
                return call_user_func($this->storageAdapter, serialize($saveToken));
            } else {
                if (!empty($cacheFilePath)) {
                    file_put_contents($cacheFilePath, serialize($saveToken));
                }
            }
        } else {
            if ($this->storageAdapter) {
                $tokenData = call_user_func($this->storageAdapter, false);
            } else {
                if (empty($cacheFilePath)) {
                    $tokenData = null;
                } else {
                    $tokenData = file_get_contents($cacheFilePath);
                }
            }
            if ($tokenData) {
                $data = unserialize($tokenData);
                if (!isset($data['expires_in']) || !isset($data['access_token'])) {
                    return false;
                }
                if ($data['expires_in'] < time()) {
                    return false;
                } else {
                    return $data['access_token'];
                }
            }
            return false;
        }
    }

    /**
     * 创建新用户[授权模式]
     * @param $username
     * @param $password
     * @return mixed
     * @throws \ErrorException
     */
    public function userAuthorizedRegister($username, $password)
    {
        $url = $this->url . '/users';
        return $this->httpCurl(
            $url,
            [
                'username' => $username,
                'password' => $password
            ]
        );
    }


    /**
     * @param string|array $groupId 发给群ID
     * @param string $from 谁发的
     * @param array $options
     * @param $options ['mixed'] 是否需要将ext的内容同时发送到txt里 环信的webim不支持接受ext 故加入此功能
     * @param $options ['msg'] 消息内容
     * @param $options ['ext'] 扩展消息内容
     * @return mixed
     */
    public function sendToGroups($groupId, $from, $options)
    {
        return $this->sendMessage($from, $groupId, $options, 'chatgroups');
    }

    /**
     * @param string|array $username 发给谁
     * @param string $from 谁发的
     * @param array $options
     * @param $options ['mixed'] 是否需要将ext的内容同时发送到txt里 环信的webim不支持接受ext 故加入此功能
     * @param $options ['msg'] 消息内容
     * @param $options ['ext'] 扩展消息内容
     * @return mixed
     */
    public function sendToUsers($username, $from, $options)
    {
        return $this->sendMessage($from, $username, $options);
    }

    /**
     * [httpCurl desc]
     * @desc 网络请求
     * @author limx
     * @param $url
     * @param array $params
     * @return mixed
     */
    private function httpCurl($url, $params = [])
    {
        $header = [];
        if ($url !== $this->url . '/token') {
            $token = $this->getToken();
            $header = [
                'Authorization: Bearer ' . $token
            ];
        }

        $response = Curl::post($url, $params, 'json', $header);
        return json_decode($response, true);
    }

    /**
     * 获取token
     * @return bool
     * @throws \ErrorException
     */
    private function getToken()
    {
        $token = $this->cacheToken();
        if ($token) {
            return $token;
        } else {
            $option ['grant_type'] = "client_credentials";
            $option ['client_id'] = $this->client_id;
            $option ['client_secret'] = $this->client_secret;
            $token = $this->httpCurl($this->url . '/token', $option);
            if (isset($token['access_token'])) {
                $this->cacheToken($token);
                return $token['access_token'];
            } else {
                return false;
            }
        }
    }


    /**
     * @param string $from 谁发的
     * @param string|array $to 发给谁,人或群
     * @param array $options
     * @param $options ['mixed'] 是否需要将ext的内容同时发送到txt里 环信的webim不支持接受ext 故加入此功能
     * @param $options ['msg'] 消息内容
     * @param $options ['ext'] 扩展消息内容
     * @param string $target_type 群还是人
     * @return mixed
     * @throws \ErrorException
     */
    private function sendMessage($from, $to, $options, $target_type = 'users')
    {
        $data = array(
            'target_type' => $target_type,
            'target' => is_array($to) ? $to : array($to),
            'from' => $from,
        );
        if (isset($options['mixed'])) {
            $data['msg'] = array(
                'type' => 'txt',
                'msg' => json_encode($options['ext'])
            );
        }
        if (isset($options['msg'])) {
            $data['msg'] = array(
                'type' => 'txt',
                'msg' => strval($options['msg'])
            );
        }
        if (isset($options['ext'])) {
            $data['ext'] = $options['ext'];
        }
        $url = $this->url . '/messages';
        return $this->httpCurl($url, $data);
    }
}