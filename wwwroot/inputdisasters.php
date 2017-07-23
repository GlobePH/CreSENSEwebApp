<?php

/* Connection Properties */
$hostname	= "mysql5.gear.host";	
$database	= "cresense";
$user		= "cresense";
$pass		= "cresense_2017";

/* Open Error Logging File */
$error_file = fopen("Error_Log.txt", "a");

$event_name = $_GET['event_name'];
$event_desc = $_GET['event_desc'];
$loc_long = $_GET['loc_long'];
$loc_lat = $_GET['loc_lat'];
$loc_rad = $_GET['loc_rad'];

echo $event_name;
echo $event_desc;
echo $loc_long;

$web_uri = 'https://devapi.globelabs.com.ph/smsmessaging/v1/outbound/3695/requests?access_token=';

/* Start Connection */
$conn = mysqli_connect($hostname, $user, $pass, $database);
if ($conn)
{
	$dis_q = "INSERT INTO disaster_details(event_name, event_desc, loc_long, loc_lat, loc_rad) VALUES('" . $event_name . "', '" . $event_desc . "', " . $loc_long . ", " . $loc_lat . ", " . $loc_rad . ")";
	if (mysqli_query($conn, $dis_q))
	{
		$get_id_q = "SELECT * FROM disaster_details ORDER BY event_id DESC LIMIT 1";
		$get_id_r = mysqli_query($conn, $get_id_q);
		$id_row = mysqli_fetch_assoc($get_id_r);
		
		$get_sub_q = "SELECT * FROM subscribers_info ORDER BY subscriber_id ASC";
		$get_sub_r = mysqli_query($conn, $get_sub_q);
		if (mysqli_num_rows($get_sub_r) > 0)
		{
			while(($getsub_row = mysqli_fetch_assoc($get_sub_r)))
			{
				
				$message = "ADVISORY\n" . $event_name . "\n" . $id_row['event_id'] . "\n" . $id_row['recv_time'];
				
				$payload = array('outboundSMSMessageRequest' => array('senderAddress' => 'tel:21583695', 'clientCorrelator' => '123456', 'outboundSMSTextMessage' => array('message' => $message), 'address' => 'tel:+63' . $getsub_row['subscriber_number']));
						
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $web_uri . $getsub_row['access_token']);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				
				if(($cr = curl_exec($ch)) === false)
				{
					fwrite($error_file, PHP_EOL);
					fwrite($error_file, '\nError in inputdisasters: Curl Error: ' . curl_error($ch));
					mysqli_close($conn);
					fclose($error_file);
					exit(0);
				}
			}
			echo "Nice";
		}
	}
	else
	{
		fwrite($error_file, PHP_EOL);
		fwrite($error_file, '\nError in inputdisasters: No subscriber data');
		mysqli_close($conn);
		fclose($error_file);
		exit(0);
	}
}

mysqli_close($conn);

?>