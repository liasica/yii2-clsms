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
use Redis;

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
  public $tpl4;
  // 签名
  public $signature;
  // 即将发送的代码
  public $code;
  // 手机
  public $phone;
  // sessionId
  public $sessionId;

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
   * curl获取数据
   *
   * @param      $url
   * @param null $params
   *
   * @return mixed
   */
  public function getcurl($url, $params = NULL)
  {
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
    return $output;
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
   * @param bool $useRedis
   * @param null $tpl
   * @param bool $sign
   * @param null $session
   * @param null $customTpl
   *
   * @return mixed
   */
  public function sendMessage($useRedis = TRUE, $tpl = NULL, $sign = FALSE, $session = NULL, $customTpl = NULL)
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
      if ($session != NULL)
      {
        $session->set('code' . $this->phone, $this->code);
        $session->set('time' . $this->phone, time());
      }
      if ($useRedis)
      {
        $this->redisSetCode();
      }
    }
    return $output;
  }

  /**
   * 发送单条信息，时间限制，默认10分钟
   *
   * @param bool $useRedis
   * @param int  $time 单位：秒
   * @param null $tpl
   * @param bool $sign
   * @param null $session
   * @param null $customTpl
   *
   * @return bool|mixed
   */
  public function sendTimeLimitCode($useRedis = TRUE, $time = 600, $tpl = NULL, $sign = FALSE, $session = NULL, $customTpl = NULL)
  {
    $serverCode = $this->getCode();
    // 短信认证码已发送并且处于有效期内
    if ($serverCode['code'] != NULL && $serverCode['time'] + $time > time())
    {
      return 'FAIL';
    }
    else
    {
      $url    = $this->sendUrl . $this->getData($tpl, $sign, $customTpl);
      $output = $this->getcurl($url);
      $res    = explode(',', $output);
      if (isset($res[1]) && $res[1] == 0)
      {
        if ($session != NULL)
        {
          $session->set('code' . $this->phone, $this->code);
          $session->set('time' . $this->phone, time());
        }
        if ($useRedis)
        {
          $this->redisSetCode();
        }
      }
      return $output;
    }
  }

  /**
   * 生成随机字符串只包含大小写字母以及数字
   *
   * @param      $length
   * @param null $Symbol
   *
   * @return null|string
   */
  public function renderRandChar($length, $Symbol = NULL)
  {
    $str    = NULL;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz" . $Symbol;
    $max    = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i++)
    {
      $str .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
  }

  /**
   * 获取发送的验证码
   *
   * @param bool $useRedis
   * @param null $session
   *
   * @return array
   */
  public function getCode($useRedis = TRUE, $session = NULL)
  {
    if ($session != NULL)
    {
      $code = $session->get('code' . $this->phone);
      $time = $session->get('time' . $this->phone);
    }
    if ($useRedis)
    {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);
      $redis->select(2);
      $code = $redis->get('code' . $this->phone);
      $time = $redis->get('time' . $this->phone);
    }
    return [
      'code' => $code,
      'time' => $time
    ];
  }

  /**
   * 移除session
   *
   * @param $session
   *
   * @return bool
   */
  public function removeCode($session)
  {
    $session->remove('code' . $this->phone);
    $session->remove('time' . $this->phone);
    return TRUE;
  }

  /**
   * redis设置键值
   */
  public function redisSetCode()
  {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->select(2);
    $redis->set('code' . $this->phone, $this->code);
    $redis->set('time' . $this->phone, time());
  }

  /**
   * redis清除键值
   */
  public function redisRemoveCode()
  {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->select(2);
    $redis->del('code' . $this->phone, 'time' . $this->phone);
  }

  /**
   * 群发
   */
  public function sendBatchMessage()
  {

  }
}