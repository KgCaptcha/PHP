<?php
 // SDK 文件
include "./sdk/KgCaptchaSDK.php";
header("Content-Type:text/html;charset=utf-8");

// 后端处理
if (!empty($_POST)) {

    // 设置 AppId 及 AppSecret，在应用管理中获取
    if($_GET["cty"] == "1"){
        $appId = "rA9qRcl6";
        $appSecret = "6h75TuboCunnHNQhI5zzxZOZav0Wzf9e";
    } elseif ($_GET["cty"] == "2"){
        $appId = "nC1sCjwg";
        $appSecret = "Vq2okDtS8XqtRgCH2sR9SLq0A5eS30Cq";
    } else {
        $appId = "4gXIWZzz";
        $appSecret = "VqFz4RCxtzYu9IzvhvtiEQDdPrmkA7If";
    }

    $request = new kgCaptcha($appId, $appSecret);  // 填写你的 AppId 和 AppSecret，在应用管理中获取

    // 填写应用服务域名，在应用管理中获取
    $request->appCdn = "https://cdn9.kgcaptcha.com";


    // 前端验证成功后颁发的 token，有效期为两分钟
    $request->token = $_POST["kgCaptchaToken"];

    // 当安全策略中的防控等级为3时必须填写，一般情况下可以忽略
    // 可以填写用户输入的登录帐号（如：$_POST["username"]），可拦截同一帐号多次尝试等行为
    $request->userId = "kgCaptchaDemo";

    // 请求超时时间，秒
    $request->connectTimeout = 10;

    $requestResult = $request->sendRequest();

    if ($requestResult->code === 0) {
        // 验签成功逻辑处理 ***

        // 这里做验证通过后的数据处理
        // 如登录/注册场景，这里通常查询数据库、校验密码、进行登录或注册等动作处理
        // 如短信场景，这里可以开始向用户发送短信等动作处理
        // ...

        echo "<script>alert('验证通过');history.back();</script>";
    } else {
        // 验签失败逻辑处理
        echo "<script>alert('" . $requestResult->msg . " - " . $requestResult->code . "');history.back();</script>";
    }
} else {
    header("Location: index.html");
}