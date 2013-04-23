<?php
require_once("LightOpenID.php");
require_once("AES.php");

// redirect when no www
if($_SERVER['SERVER_NAME']=="kammerath.com"){
	header('Location: http://www.kammerath.com/redbox/');
}

// check if logout/cookie delete is requested
if(array_key_exists("logout", $_GET)){
        setcookie ("redbox_auth", "", time()-3600);
        header('Location: /redbox');
        die();
}

// instanciate the openid lib
$openid = new LightOpenID("www.kammerath.com");

// basic userdata
$userEmail = "";
$userFirstname = "";
$userLastname = "";
$loggedIn = false;
$authUrl = "";
$cookiePasswd = "CookiePassword";
$adminUserList = array("user1@domain.com","user2@domain.com");

if(array_key_exists("redbox_auth",$_COOKIE)){ 
        $userdata = json_decode(AES::decrypt($_COOKIE["redbox_auth"],$cookiePasswd));
        $userEmail = $userdata->email;
        $userFirstname = $userdata->first;
        $userLastname = $userdata->last;

	if(in_array($userEmail,$adminUserList)){
        	$loggedIn = true;
	}
}else{
        if ($openid->mode) {
            if ($openid->mode == 'cancel') {
                $loggedIn = false;
            } elseif($openid->validate()) {
                $data = $openid->getAttributes();
                $userEmail = $data['contact/email'];
                $userFirstname = $data['namePerson/first'];
                $userLastname = $data['namePerson/last'];

                for($u=0;$u<count($adminUserList);$u++){
                        if($adminUserList[$u]==$userEmail){
                                $loggedIn = true;

                                // save the userdata in encrypted cookie
                                setcookie("redbox_auth",AES::encrypt(json_encode(array("email" => $userEmail, 
                                        "first" => $userFirstname, "last" => $userLastname)),
                                        $cookiePasswd));
                        }
                }
            }
        } else {
            $loggedIn = false;
            $openid->identity = 'https://www.google.com/accounts/o8/id';
                $openid->required = array(
                  'namePerson/first',
                  'namePerson/last',
                  'contact/email',
                );
                $openid->returnUrl = "http://www.kammerath.com/redbox/";
                $authUrl = $openid->authUrl();
        }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <title>
    		RedBox
	</title>
        <link rel="stylesheet" href="jquery.mobile-1.2.0.min.css" />
    </head>
    <body>
        <!-- Home -->
        <div data-role="page" id="page1">
            <div data-theme="a" data-role="header">
                <h3>
                    RedBox
                </h3>
            </div>
            <div data-role="content">
		<?php
		if(!$loggedIn){
		?>
		RedBox requires you to log in with your Google Account in order
		to make sure that you're allowed to use the system.<br />
		<br />
		By tapping the Login-button you will be directed to Google and 
		asked to allow RedBox access to your Google account.<br />	
		<br />
		<a data-role="button" href="<?php echo $authUrl; ?>" data-icon="star" data-iconpos="left">
                    Login
                </a>
		<?php
		}else{
		?>

		<div data-role="fieldcontain">
            	<label for="YourNumberInput">
                	Your phone number
            	</label>
            	<input name="YourNumberInput" id="YourNumberInput" placeholder="" value="+49" type="tel">
        	</div>
        	<div data-role="fieldcontain">
            		<label for="DestinationNumberInput">
                	Destination phone number
           		</label>
            	<input name="DestinationNumberInput" id="DestinationNumberInput" placeholder="" value="+49" type="tel">
        	</div>
        	<a id="CallButton" data-role="button" data-theme="e" href="#page1" data-icon="gear" data-iconpos="left">
            		Make Call
        	</a>
        	<a id="LogoutButton" data-role="button" data-theme="d" href="#page1" onclick="javascript:location.href='/redbox/index.php?logout';" data-icon="delete" data-iconpos="left">
            		Logout
        	</a>
		<br />
		<strong>How it works:</strong> Enter your phone number and that of the person you want to call, 
		then click "Make Call". You will then receive a call from RedBox which you need to answer. After 
		you picked it up, you will notice that it dials your destination. Wait until your destination 
		picked up the line or hang up.
		<?php
		}
		?>
            </div>
        </div>

	<div data-role="page" id="dialogPage">
  		<div data-role="header">
    			<h2>Dialog</h2>
  		</div>
  		<div data-role="content">
    			<p>I am a dialog</p>
                	<a id="ThanksButton" data-role="button" data-theme="b" href="#page1" data-icon="arrow-l" data-iconpos="left">
        	                Ok, Thanks.
	                </a>
  		</div>
	</div>

        <script src="jquery-1.7.2.min.js"></script>
        <script src="jquery.mobile-1.2.0.min.js"></script>
	<?php if($loggedIn){ ?>
	<script type="text/javascript">
	$("#CallButton").click(function(){
		var num1 = $("#YourNumberInput").val();
		var num2 = $("#DestinationNumberInput").val();
		var callUrl = "/redbox/call.php?num1="+encodeURIComponent(num1)+"&num2="+encodeURIComponent(num2);
		$.mobile.loading( 'show', {text: 'Initiating call, please wait...', textVisible: true, theme: 'a'} );
		$.getJSON(callUrl, function(data) {
			$.mobile.loading('hide');
			if(data.status == "call-initiated"){
				$("#dialogPage h2").text("Call initiated");
				$("#dialogPage p").text("Your call is initiated and will arrive soon.");		
				$.mobile.changePage('#dialogPage', 'slidedown', true, true);
			}else{
				$("#dialogPage h2").text("Error initiating call");
                                $("#dialogPage p").text("There was an error initiating your call. "
						+ "Please check your data and try again. Error code: "
						+ data.status);
				$.mobile.changePage('#dialogPage', 'slidedown', true, true);
			}	
		});	
	});	
	</script>  
	<?php } ?>
</body>
</html>

