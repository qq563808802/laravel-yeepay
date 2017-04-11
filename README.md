# 易宝掌柜通接口

## 概述

易宝支付，掌柜通分账创建

## 运行环境
- PHP 5.3+
- cURL extension

提示：

- Ubuntu下可以使用apt-get包管理器安装php的cURL扩展 `sudo apt-get install php5-curl`

## 安装方法

1. 如果您通过composer管理您的项目依赖，可以在你的项目根目录运行：

        $ composer require yeepay/sdk-php

   或者在你的`composer.json`中声明对yeepay/sdk-php for PHP的依赖：

        "require": {
            "yeepay/sdk-php": "~1.0"
        }

   然后通过`composer install`安装依赖。composer安装完成后，在您的PHP代码中引入依赖即可：

        require_once __DIR__ . '/vendor/autoload.php';


3. 下载SDK源码，在您的代码中引入SDK目录下的`autoload.php`文件：

        require_once '/path/to/yeepay/autoload.php';
## 配置

### Laravel应用
1. 注册 `ServiceProvider`:
```php
YeePay\YeePay\YeePayServiceProvider::class,
```

2. 创建配置和迁移文件
```shell
php artisan vendor:publish
```

3. 可以选择修改根目录下的`config/yeepay.php`中对应的修改设置

4. 执行迁移命令,生成数据表
```shell
php artisan migrate
```

5.添加门面到`config/app.php`中的`aliases`部分:
```php
'YeePay' => YeePay\YeePay\Facades\YeePay::class,
```

### Lumen应用

1. 在`bootstrap/app.php`的 `82` 行左右:
```php
$app->register(YeePay\YeePayServiceProvider::class);
```

```
## 快速使用

### 创建支付
    app('yeepay')->pay->add($post)
###

###创建子账号
        $post = ['requestid' => 'real_order|||3305' . rand(11111,999999),
                'bindmobile' => '12345678901',
                'customertype' => 'PERSON',
                'signedname' => '掌柜通测试',
                'linkman' => ' 张三',
                'idcard' => '123456789012345678',
                'businesslicence' => '11113333',
                'legalperson' => '张三',
                'minsettleamount' => '1',
                'riskreserveday' => '1',
                'bankaccountnumber' => '1234567890123456',
                'bankname' => ' 招商银行股份有限公司杭州分行',
                'accountname' => ' 张三',
                'bankaccounttype' => 'PrivateCash',
                'bankprovince' => '浙江',
                'bankcity' => '杭州',
                'deposit' => '',
                'email' => '',
            ];
        app('yeepay')->ledger->register($post)
