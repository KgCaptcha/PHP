<?php
/*
 * PHP 7.x/8.x
 * KgCaptcha v1.0.0
 * http://www.KgCaptcha.com
 *
 * Copyright © 2022 Kyger. All Rights Reserved.
 * http://www.kyger.com.cn
 *
 * Copyright © 2022 by KGCMS.
 * http://www.kgcms.com
 *
 * Date: Thu May 20 15:28:23 2022
*/
class kgCaptcha{
    public $appCdn = "https://cdn9.kgcaptcha.com"; // 风险防控服务URL
    public $appId; // 公钥
    public $appSecret; // 秘钥

    public $connectTimeout = 50;  // 连接超时断开请求，秒

    public $clientIp;  //  客户端IP，安全等级为1和2时必须设置
    public $clientBrowser;  // 客户端浏览器信息，安全等级为1和2时必须设置
    public $userId;  // 用户手机号/ID/登录用户名，安全等级为2时必须设置


    public $domain;  // 授权域名，当前应用域名
    public $token;  // 前端验证成功后颁发的 token

    private $time, $data;

	// 构造函数
	public function __construct($appId, $appSecret){
	    $this->appId = $appId;
	    $this->appSecret = $appSecret;
	    $this->time = time();

        /* 来源页面 */
        $this->domain = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/";
	}

    // 数据包
    public function putData(){
        if (empty($this->clientIp)){
            $this->clientIp = $_SERVER["REMOTE_ADDR"];
        }
        if($this->clientIp == "::1"){
            $this->clientIp = "127.0.0.1";
        }
        if (empty($this->clientBrowser)){
            $this->clientBrowser = $_SERVER["HTTP_USER_AGENT"];
        }
        return array(
            "ip" => $this->clientIp,
            "browser" => $this->clientBrowser,
            "time" => $this->time,
            "uid" => $this->userId,
            "timeout" => $this->connectTimeout,
            "token" => $this->token,
        );
    }

    // 生成签名URL
	public function signUrl(){
	    $data = "";
	    foreach ($this->data as $key => $value){
	        $data .= $key . $value;
	    }
	    $sign = md5($this->appId . $data . $this->appSecret);
	    return "{$this->appCdn}/requestBack?appid={$this->appId}&sign={$sign}";
	}

	// 发送请求
	public function sendRequest(){
	    $this->data = $this->putData();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->signUrl());
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->domain);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->data));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);  # 最长等待时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->connectTimeout);  # 整个cURL函数执行过程的最长等待时间
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $rInfo = curl_exec($curl);
        $err = curl_errno($curl);
        if ($err) {
            $rInfo = '{"code": 20000, "msg": "SDK请求错误：' . $err . '"}';
		} else {
            curl_close($curl);
        }
        return json_decode($rInfo, FALSE);
    }
}