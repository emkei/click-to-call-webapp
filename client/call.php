<?php
require_once("AES.php");

header("Content-type: application/json");
$result = array("status" => "auth-failure", "num1" => "", "num2" => "");

$cookiePasswd = "CookiePassword";
$serverKey = "SecretKey";
$adminUserList = array("user1@domain.com","user2@domain.com");

if(array_key_exists("redbox_auth",$_COOKIE)){
        $userdata = json_decode(AES::decrypt($_COOKIE["redbox_auth"],$cookiePasswd));
        $userEmail = $userdata->email;
        $userFirstname = $userdata->first;
        $userLastname = $userdata->last;

        if(in_array($userEmail,$adminUserList)){
        	$result["status"] = "number-invalid";
        	if(array_key_exists("num1",$_GET)==true
			&&array_key_exists("num2",$_GET)==true){
			// forward the call request to the redbox
			$num1 = $_GET["num1"];
			$num2 = $_GET["num2"];
			$secretKey = md5($num1.$num2.$serverKey);
			$result = json_decode(file_get_contents
					("http://MYASTERISKSERVERDOMAIN:8080/?num1="
					.urlencode($num1)."&num2=".urlencode($num2)
					."&key=".$secretKey));
		}
	}
}

echo(json_encode($result));
?>
