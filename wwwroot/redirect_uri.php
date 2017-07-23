<?php

/* Connection Properties */
$hostname	= "mysql5.gear.host";	
$database	= "cresense";
$user		= "cresense";
$pass		= "cresense_2017";

/* Open Error Logging File */
$error_file = fopen("Error_Log.txt", "a");

$web_uri = "https://developer.globelabs.com.ph/oauth/access_token";

/* Start Connection */
$conn = mysqli_connect($hostname, $user, $pass, $database);
if (!$conn)
{
	fwrite($error_file, "\nError in redirect_uri: Unable to connect to database\n");
	fclose($error_file);
	exit(0);
}

if (isset($_GET['code']))
{
	/* Build cURL POST Request */
	$fields = array(
						'app_id'=>urlencode('xXayt5oXAzhMoi6E4AcXMKh75Xk5tgAa'),
						'app_secret'=>urlencode('232a13fa1745dde9f9ebbd0b76567f6afe724019137be862d356c77a600246a0'),
						'code'=>urlencode($_GET['code'])
					);
	foreach($fields as $key=>$value)
	{
		$field_string .= $key.'='.$value.'&';
	}
	$field_string = rtrim($field_string,'&');
	
	/* Execute cURL Request */
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $web_uri);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
	if(($cr = curl_exec($ch)) === false)
	{
		fwrite($error_file, '\nError in redirect_uri: Curl Error: ' . curl_error($ch));
		mysqli_close($conn);
		fclose($error_file);
		exit(0);
	}
	
	/* Extract Subscriber Info from Web API */
	else
	{
		$web_info = json_decode($cr, true);
		curl_close($ch);
	}
	
	/* Record Subscriber Values */
	$check_q = "SELECT * FROM subscribers_info WHERE access_token = '" . $web_info["access_token"] . "'";
	$check_r = mysqli_query($conn, $check_q);
	if (!$check_r)
	{
		fwrite($error_file, "\nError in redirect_uri: Unable to query database\n");
		mysqli_close($conn);
		fclose($error_file);
		exit(0);
	}
	if (mysqli_num_rows($check_r) == 0)
	{
		$in_web_q = "INSERT INTO subscribers_info(access_token, subscriber_number) VALUES('" . $web_info["access_token"] . "', '" . $web_info["subscriber_number"] . "')";
		if (!mysqli_query($conn, $in_web_q))
		{
			fwrite($error_file, "\nError in redirect_uri: Unable to store subscriber info from Web API\n");
			mysqli_close($conn);
			fclose($error_file);
			exit(0);
		}
	}
}

/* Unsubscribe */
elseif (strcmp($_SERVER['REQUEST_METHOD'], 'POST') == 0)
{
	$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
	if (strcmp($contentType, 'application/json') == 0)
	{
		//Receive the RAW post data.
		$content = trim(file_get_contents("php://input"));
		 
		//Attempt to decode the incoming RAW post data from JSON.
		$decoded = json_decode($content, true);
		 
		//If json_decode failed, the JSON is invalid.
		if (is_array($decoded))
		{
			$check_q = "SELECT * FROM subscribers_info WHERE access_token = '" . $decoded["unsubscribed"]["access_token"] . "'";
			$check_r = mysqli_query($conn, $check_q);
			if (!$check_r)
			{
				fwrite($error_file, "\nError in redirect_uri: Unable to query database\n");
				mysqli_close($conn);
				fclose($error_file);
				exit(0);
			}
			if (mysqli_num_rows($check_r) > 0)
			{
				/* Record Subscriber Values */
				$out_sms_q = "DELETE FROM subscribers_info WHERE access_token = '" . $decoded["unsubscribed"]["access_token"] . "'";
				if (!mysqli_query($conn, $out_sms_q))
				{
					fwrite($error_file, "\nError in redirect_uri: Unable to delete subscriber info using SMS API\n");
					mysqli_close($conn);
					fclose($error_file);
					exit(0);
				}
			}
		}
	}
}

/* Opt-In via SMS */
else
{
	if (isset($_GET['access_token']) && isset($_GET['subscriber_number']))
	{
		$check_q = "SELECT * FROM subscribers_info WHERE access_token = '" . $_GET['access_token'] . "'";
		$check_r = mysqli_query($conn, $check_q);
		if (!$check_r)
		{
			fwrite($error_file, "\nError in redirect_uri: Unable to query database\n");
			mysqli_close($conn);
			fclose($error_file);
			exit(0);
		}
		if (mysqli_num_rows($check_r) == 0)
		{
			/* Record Subscriber Values */
			$in_sms_q = "INSERT INTO subscribers_info(access_token, subscriber_number) VALUES('" . $_GET['access_token'] . "', '" . $_GET['subscriber_number'] . "')";
			if (!mysqli_query($conn, $in_sms_q))
			{
				fwrite($error_file, "\nError in redirect_uri: Unable to store subscriber info from SMS API\n");
				mysqli_close($conn);
				fclose($error_file);
				exit(0);
			}
		}
	}
	else
	{
		fwrite($error_file, "\nError in redirect_uri: Missing GET values in SMS subscription\n");
		mysqli_close($conn);
		fclose($error_file);
		exit(0);
	}
}

/* Success Page */
echo "Successful Processing\n";

/* Close Connection */
mysqli_close($conn);

/* Close Error Logging File */
fclose($error_file);

?>