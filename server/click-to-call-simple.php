<?php
$result = "";
if(array_key_exists("num1",$_GET) == true
        && array_key_exists("num2",$_GET) == true){

        $num1 = $_GET["num1"];
        $num2 = $_GET["num2"];

        /* check if both numbers start with a +*/
        if(substr($num1,0,1)=="+" && substr($num2,0,1)=="+"){
                $num1 = substr($num1,1);
                $num2 = substr($num2,1);
                if(is_numeric($num1)==true && is_numeric($num2)){
                        shell_exec("sudo asterisk -rx \"originate Local/000"
                                .$num1."@outgoing "."extension 000".$num2."@outgoing\"");

                        $result = "call initiated";
                }else{
                        $result = "The number is invalid. It needs to start with "
                                ."a + and then only contain numbers (e.g. +493030303030)";
                }
        }else{
                $result = "The number is invalid. It needs to start with a + (e.g. +49).";
        }
}
?>
<html>
<head>
<title>Click-to-Call Simple PHP Example</title>
</head>
<body>
<?php
if($result != ""){
        echo "Status: ".$result;
}
?>
<form method="get">
        Source number:<br />
        <input type="text" name="num1" /><br />
        <br />
        Destination number:<br />
        <input type="text" name="num2" /><br />
        <br />
        <input type="submit" value="Initiate call" />
</form>
</body>
</html>
