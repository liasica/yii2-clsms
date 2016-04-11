<?php
/**
 * Author: liasica
 * CreateTime: 16/2/2 14:26
 * Filename: Clsms.php
 * PhpStorm: v2
 */
namespace liasica\clsms;

use liasica\helpers\Curl;
use Yii;
use yii\base\InvalidConfigException;

class Clsms
{
  // 平台账号
  private $account;
  // 平台密码
  private $pswd;
  // 接口（群发）
  private $batchSendUrl;
  // 接口（单发）
  private $sendUrl;
  // 模板
  public $tpl;
  // 签名
  public $signature;
  // 即将发送的消息
  private $msg;
  // 代码
  private $code;
  // 手机
  private $mobile;

  /**
   * Clsms constructor.
   *
   * @param null $account
   * @param null $pswd
   * @param null $sendUrl
   *
   * @throws InvalidConfigException
   */
  public function __construct($account = null, $pswd = null, $sendUrl = null)
  {
    $params = Yii::$app->params;
    if (!isset($params['clsms']) && ($account == null || $pswd == null || $sendUrl == null))
    {
      throw new InvalidConfigException('缺乏必要的设置');
    }
    if (!isset($params['clsms']))
    {
      $this->sendUrl = $sendUrl;
      $this->account = $account;
      $this->pswd    = $pswd;
    }
    else
    {
      $clsms         = $params['clsms'];
      $this->sendUrl = $clsms['sendUrl'];
      $this->account = $clsms['account'];
      $this->pswd    = $clsms['pswd'];
    }
  }

  /**
   * 设置即将发送的消息
   *
   * @param null $tpl
   * @param null $msg
   *
   * @return $this
   * @throws \yii\base\InvalidConfigException
   */
  public function setMsg($tpl = null, $msg = null)
  {
    $params = Yii::$app->params;
    // 消息空时设置默认消息
    if ($msg == null)
    {
      $msg = mt_rand(1000, 9999);
    }
    $this->code = $msg;
    // 模板
    if ($tpl != null)
    {
      if ($tpl == 'tpl1' || $tpl == 'tpl2' || $tpl == 'tpl3' || $tpl == 'tpl4')
      {
        if (!isset($params['clsms'][$tpl]))
        {
          throw new InvalidConfigException('模板不存在');
        }
        $tpl = $params['clsms'][$tpl];
      }
      elseif (strpos($tpl, '#code#') === false)
      {
        throw new InvalidConfigException('模板错误');
      }
      $msg = str_replace('#code#', $msg, $tpl);
    }
    $this->msg = $msg;
    // 设置签名
    if (isset($params['clsms']['signature']) && $params['clsms']['signature'] != null)
    {
      $this->msg .= $params['clsms']['signature'];
    }

    return $this;
  }

  /**
   * @param $mobile
   *
   * @return array
   */
  public function send($mobile)
  {
    $this->mobile = $mobile;
    $url          = $this->sendUrl . 'account=%s&pswd=%s&mobile=%s&msg=%s&needstatus=true';
    $url          = sprintf($url, $this->account, $this->pswd, $mobile, $this->msg);
    $curl         = new Curl($url);
    $res          = $curl->Get();
    $res          = explode(',', $res);
    if (isset($res[1]) && $res[1] == 0)
    {
      $errCode = 0;
      $errDesc = $this;
    }
    else
    {
      $errCode = 1;
      $errDesc = $res;
    }

    return [
      'errCode' => $errCode,
      'errDesc' => $errDesc,
    ];
  }
}
