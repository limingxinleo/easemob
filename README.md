# easemob
[![Total Downloads](https://poser.pugx.org/limingxinleo/easemob/downloads)](https://packagist.org/packages/limingxinleo/easemob)
[![Latest Stable Version](https://poser.pugx.org/limingxinleo/easemob/v/stable)](https://packagist.org/packages/limingxinleo/easemob)
[![Latest Unstable Version](https://poser.pugx.org/limingxinleo/easemob/v/unstable)](https://packagist.org/packages/limingxinleo/easemob)
[![License](https://poser.pugx.org/limingxinleo/easemob/license)](https://packagist.org/packages/limingxinleo/easemob)

## 安装方法 ##
~~~
composer require limingxinleo/easemob
~~~

## 使用方法 ##
* 初始化
~~~
use limx\tools\Easemob;
$option = [
    'client_id' => env('EASEMOB_ID'),
    'client_secret' => env('EASEMOB_SECRET'),
    'org_name' => env('EASEMOB_ORG_NAME'),
    'app_name' => env('EASEMOB_APP_NAME'),
];
$easemob = new Easemob($option);
~~~
* 发送环信
~~~
$res = $easemob->sendToUsers(['receiver'], 'sender', ['msg' => '环信测试']);
~~~
* 设置StorageAdapter
~~~
$easemob->setStorageAdapter(function ($data) {
    $file = BASE_PATH . '/storage/cache/data/huanxin';
    if ($data) {
        // 存储
        file_put_contents($file, $data);
    } else {
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return false;
    }
});
~~~