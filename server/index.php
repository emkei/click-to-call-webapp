<?php
/* JSON API for calling */
$passwd = "Krtek1992!";

// default standard result
$result = array("status" => "no-request", "num1" => "", "num2" => "");

if(array_key_exists("num1",$_GET) == true
	&& array_key_exists("num2",$_GET) == true
	&& array_key_exists("key",$_GET) == true){

	// secret key is a hash of the number and the passwd	
	$secretKey = md5($_GET["num1"].$_GET["num2"].$passwd);
	
	if($_GET["key"]==$secretKey){
		$num1 = $_GET["num1"];
		$num2 = $_GET["num2"];
			
		/* check if both numbers start with a +*/
		if(substr($num1,0,1)=="+" && substr($num2,0,1)=="+"){
			$num1 = substr($num1,1);
			$num2 = substr($num2,1);
			if(is_numeric($num1)==true && is_numeric($num2)){
				shell_exec("sudo asterisk -rx \"originate Local/000"
					.$num1."@outgoing "."extension 000".$num2."@outgoing\"");
				
				$result = array("status" => "call-initiated", 
						"num1" => $num1, "num2" => $num2);
			}else{
				$result["status"] = "number-invalid";
			}
		}else{
			$result["status"] = "number-invalid";
		}
	}else{
		$result["status"] = "auth-failure";
	}
}

// output the result
header("Content-type: application/json");
echo(json_encode($result));
?>
