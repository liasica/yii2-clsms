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
use Yii;

class Sdk extends Component
{
  // 平台账号
  public $account;
  // 平台密码
  public $pswd;
  // 接口（群发）
  public $batchSendUrl;
  // 接口（单发）
  public $sendUrl;
  // 模板
  public $tpl1;
  public $tpl2;
  public $tpl3;
  // 签名
  public $signature;
  // 即将发送的代码
  public $code;
  // 手机
  public $phone;

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
    if ($this->batchSendUrl === NULL && $this->sendUrl === NULL)
    {
      throw new InvalidConfigException('单发API地址或群发API地址必须有一个');
    }
  }

  /**
   * 通过模板获取即将发送的消息
   *
   * @param      $tpl
   * @param bool $sign
   *
   * @return mixed|string
   * @throws InvalidConfigException
   */
  public function getMessageWithTpl($tpl, $sign = TRUE)
  {
    if ($this->tpl1 === NULL && $this->tpl2 === NULL && $this->tpl3 === NULL)
    {
      throw new InvalidConfigException('你没有设置模板');
    }
    if ($sign === TRUE && $this->signature === NULL)
    {
      throw new InvalidConfigException('你没有设置签名');
    }
    if ($this->code === NULL)
    {
      throw new InvalidConfigException('没有即将发送的消息');
    }
    $tpl = str_replace('#code#', $this->code, $tpl);
    if ($tpl == $this->code)
    {
      throw new InvalidConfigException('所选模板不存在');
    }
    $sign === TRUE && $tpl .= $this->signature;
    return $tpl;
  }

  /**
   * 拼接数组
   *
   * @param $tpl
   * @param $sign
   * @param $customTpl
   *
   * @return array
   * @throws InvalidConfigException
   */
  public function getParams($tpl, $sign, $customTpl)
  {
    if ($this->phone === NULL)
    {
      throw new InvalidConfigException('The phone property must be set!');
    }
    $params = [
      'account' => $this->account,
      'pswd'    => $this->pswd,
      'mobile'  => $this->phone
    ];
    $tpl !== NULL && $sign === FALSE && $params['msg'] = $this->getMessageWithTpl($tpl, FALSE);
    $sign !== FALSE && $tpl !== NULL && $params['msg'] = $this->getMessageWithTpl($tpl);
    if ($tpl === NULL && $sign === FALSE)
    {
      if ($customTpl === NULL)
      {
        $params['msg'] = $this->code;
      }
      else
      {
        $params['msg'] = str_replace('#code#', $this->code, $customTpl);
      }
    }
    return $params;
  }

  /**
   * 获取拼接好的数据或者URL
   *
   * @param $tpl
   * @param $sign
   * @param $customTpl
   *
   * @return string
   * @throws InvalidConfigException
   */
  public function getData($tpl, $sign, $customTpl)
  {
    $params = $this->getParams($tpl, $sign, $customTpl);
    $data   = '';
    foreach ($params as $key => $param)
    {
      $data .= $key . '=' . $param . '&';
    }
    $data = substr($data, 0, -1);
    return $data;
  }

  /**
   * 发送单条信息
   *
   * @param null $tpl
   * @param bool $sign
   * @param null $customTpl
   *
   * @return mixed
   */
  public function sendMessage($tpl = NULL, $sign = FALSE, $customTpl = NULL)
  {
    $url = $this->sendUrl . $this->getData($tpl, $sign, $customTpl);
    // curl 初始化
    $ch = curl_init();
    // 设置选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    $res = explode(',', $output);
    if (isset($res[1]) && $res[1] == 0)
    {
      $session = Yii::$app->session;
      $session->open();
      $session->set('code' . $this->phone, $this->code);
      $session->set('time' . $this->phone, time());
      $session->close();
    }
    return $output;
  }

  /**
   * 获取发送的验证码
   * @return array
   */
  public function getCode()
  {
    $session = Yii::$app->session;
    return [
      'code' => $session->get('code' . $this->phone),
      'time' => $session->get('time' . $this->phone)
    ];
  }

  /**
   * 移除session
   * @return bool
   */
  public function removeCode()
  {
    $session = Yii::$app->session;
    $session->remove('code' . $this->phone);
    $session->remove('time' . $this->phone);
    return TRUE;
  }

  /**
   * 群发
   */
  public function sendBatchMessage()
  {

  }

}