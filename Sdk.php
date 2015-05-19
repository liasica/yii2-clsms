<?php
/**
 * Author: liasica
 * CreateTime: 15/5/20 上午6:37
 * Filename: Sdk.php
 * PhpStorm: v2
 */

namespace liasica\clsms;


use yii\base\Component;
use yii\base\InvalidConfigException;

class Sdk extends Component
{
  // 平台账号
  public $account;
  // 平台密码
  public $pswd;
  // 接口（群发）
  public $multiSend;
  // 接口（单发）
  public $singleSend;

  public function init()
  {
    if ($this->account === NULL)
    {
      throw new InvalidConfigException('必须设置账号');
    }
    if ($this->pswd === NULL)
    {
      throw new InvalidConfigException('必须设置平台密码');
    }
    if ($this->multiSend === NULL && $this->singleSend === NULL)
    {
      throw new InvalidConfigException('单发API地址和群发API地址必须有一个');
    }
  }


}