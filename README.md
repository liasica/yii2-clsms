创蓝文化短信接口
========
创蓝文化短信网关接口

[![Latest Stable Version](https://poser.pugx.org/liasica/yii2-clsms/v/stable)](https://packagist.org/packages/liasica/yii2-clsms) [![Total Downloads](https://poser.pugx.org/liasica/yii2-clsms/downloads)](https://packagist.org/packages/liasica/yii2-clsms) [![Latest Unstable Version](https://poser.pugx.org/liasica/yii2-clsms/v/unstable)](https://packagist.org/packages/liasica/yii2-clsms) [![License](https://poser.pugx.org/liasica/yii2-clsms/license)](https://packagist.org/packages/liasica/yii2-clsms)

版本
-----------
```
dev：开发版，bug较多
release：正式发布版，bug较少
```

安装
------------

使用 [composer](http://getcomposer.org/download/) 进行安装：

直接运行

```
php composer.phar require --prefer-dist yii2-clsms/yii2-clsms "*"
```
or
```
composer require --prefer-dist yii2-clsms/yii2-clsms "*"
```

或者添加

```
"yii2-clsms/yii2-clsms": "*"
```

到你项目的 `composer.json` 中.


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


反馈或者贡献代码
--------------
您可以在[这里](https://github.com/liasica/yii2-clsms/issues)给我提出在使用中碰到的问题或Bug. 我会在第一时间回复您并修复.

您可以发送邮件`magicrolan@qq.com`或`magicrolan@gmail.com`也可以加我QQ`100408045`给我并且说明您的问题.

如果你有更好代码实现,请fork项目并发起您的pull request.我会及时处理. 感谢!
