创蓝文化短信接口
========
创蓝文化短信网关接口

[![Latest Stable Version](https://poser.pugx.org/liasica/yii2-clsms/v/stable)](https://packagist.org/packages/liasica/yii2-clsms) [![Total Downloads](https://poser.pugx.org/liasica/yii2-clsms/downloads)](https://packagist.org/packages/liasica/yii2-clsms) [![Latest Unstable Version](https://poser.pugx.org/liasica/yii2-clsms/v/unstable)](https://packagist.org/packages/liasica/yii2-clsms) [![License](https://poser.pugx.org/liasica/yii2-clsms/license)](https://packagist.org/packages/liasica/yii2-clsms)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2-clsms/yii2-clsms "*"
```

or add

```
"yii2-clsms/yii2-clsms": "*"
```

to the require section of your `composer.json` file.


使用方法
-----
1.配置
```php
'components' => [
  ...
  'clsms'        => [
    'class'        => 'liasica\clsms\Sdk',
    'account'      => 'your account',
    'pswd'         => 'your password',
    'sendUrl'      => 'your HttpSendSM',
    'batchSendUrl' => 'your HttpBatchSendSM',
    'signature'    => 'your signature',
    'tpl1'         => 'your tpl1',
    'tpl2'         => 'your tpl2',
    'tpl3'         => 'your tpl3'
  ],
]
```

2.发送消息
```php
$clsms        = Yii::$app->clsms;
$clsms->code  = 'your code';
$clsms->phone = 'your phone number';
$clsms->sendMessage($clsms->tpl1, TRUE);
```

3.获取发送的code
```php
$clsms        = Yii::$app->clsms;
$clsms->phone = 'your phone';
$codeArr      = $clsms->getCode();
var_dump($codeArr);
```
