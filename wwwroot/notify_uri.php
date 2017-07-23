<?php

/* Connection Properties */
$hostname	= "mysql5.gear.host";	
$database	= "cresense";
$user		= "cresense";
$pass		= "cresense_2017";

$web_uri = 'https://devapi.globelabs.com.ph/smsmessaging/v1/outbound/3695/requests?access_token=';

$test_lines = array (
					"Naniniwala ka ba sa love at first sight?\nGusto mo bang dumaan ako ulit?",
					"Exam ka ba?\nGustong gusto na kasi kitang i-take home eh!",
					"Piolo just needs five things to live: some friends, some food, some work, some love and some Milby.",
					"Nurse: Doc, bat may thermo ka sa tenga?\nDoc: Susmaryosep, nakaiwan ako ng ballpen sa puwet ng pasyente.",
					"Pwede ba kitang maging sidecar?\nSingle kasi ako eh.",
					"Sabi nila pinaglihi ako sa mayonnaise. Tuwing nagugutom ang mga babae ako lagi ang lady's choice!",
					"Ano ang tagalog ng sex? ANEM!",
					"Walang silbing maayos mo ang a",
					"The reason why boys walk fast and girls speak more is that boys have an extra leg and girls have an extra mouth",
					"You are the voice that wakes me mornings and sends me out into the day."
					);

/* Open Error Logging File */
$error_file = fopen("Error_Log.txt", "a");

/* Start Connection */
$conn = mysqli_connect($hostname, $user, $pass, $database);
if (!$conn)
{
	fwrite($error_file, PHP_EOL);
	fwrite($error_file, "\nError in notify_uri: Unable to connect to database\n");
	fclose($error_file);
	exit(0);
}

if (strcmp($_SERVER['REQUEST_METHOD'], 'POST') == 0)
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
			$in_sms_q = "INSERT INTO sms_log(num_msg, msg_id, msg, to_addr, from_addr, recv_time) VALUES(" . $decoded['inboundSMSMessageList']['numberOfMessagesInThisBatch'] . ", '" . $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['messageId'] . "', '" . $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['message'] . "', '" . $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['destinationAddress'] . "', '" . $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['senderAddress'] . "', '" . $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['dateTime'] . "')";
			if (!mysqli_query($conn, $in_sms_q))
			{
				fwrite($error_file, PHP_EOL);
				fwrite($error_file, "\nError in notify_uri: Unable to insert data in sms log. Query: " . $in_sms_q . "\n");
				mysqli_close($conn);
				fclose($error_file);
				exit(0);
			}
			/* Process SMS Message */
			$number = substr($decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['senderAddress'], 7);
			$get_token_q = "SELECT * FROM subscribers_info WHERE subscriber_number = '" . $number . "' LIMIT 1";
			$get_token_r = mysqli_query($conn, $get_token_q);
			fwrite($error_file, "Entering");
			if (mysqli_num_rows($get_token_r) > 0)
			{
				$tk_row = mysqli_fetch_assoc($get_token_r);
				$test_string = explode(" ", $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['message']);
				if (count($test_string) == 2)
				{
					if (strcmp($test_string[0], 'Hi') == 0 && strcmp($test_string[1], 'Papi') == 0)
					{
						$payload = array('outboundSMSMessageRequest' => array('senderAddress' => 'tel:21583695', 'clientCorrelator' => '123456', 'outboundSMSTextMessage' => array('message' => $test_lines[rand(0,9)]), 'address' => 'tel:+63' . $number));
						
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $web_uri . $tk_row['access_token']);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem');
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
						
						if(($cr = curl_exec($ch)) === false)
						{
							fwrite($error_file, PHP_EOL);
							fwrite($error_file, '\nError in redirect_uri: Curl Error: ' . curl_error($ch));
							mysqli_close($conn);
							fclose($error_file);
							exit(0);
						}
						
						fwrite($error_file, PHP_EOL);
						fwrite($error_file, print_r(json_encode($payload), true));
						fwrite($error_file, $test_lines[rand(0,9)]);
					}
					else
					{
						fwrite($error_file, PHP_EOL);
						fwrite($error_file, "\nError in notify_uri: Unable to Parse Text");
					}
				}
				else
				{
					fwrite($error_file, PHP_EOL);
					fwrite($error_file, "\nError in notify_uri: Text is not in correct format for PAPI App");
				}
				$test_string = explode("\n", $decoded['inboundSMSMessageList']['inboundSMSMessage'][0]['message']);
				if (count($test_string) == 3)
				{
					if (strcmp($test_string[0], 'MAN') == 0)
					{
						if (strcmp($test_string[2], 'SAFE') == 0)
						{
							$in_stat_q = "UPDATE disaster_statistic SET status = 1 WHERE subscriber_id=" . $tk_row['subscriber_id'];
							if (!mysqli_query($conn, $in_stat_q))
							{
								fwrite($error_file, PHP_EOL);
								fwrite($error_file, "\nError in notify_uri: Unable to insert data in disaster statistics. Query: " . $in_stat_q . "\n");
								mysqli_close($conn);
								fclose($error_file);
								exit(0);
							}
						}
						elseif (strcmp($test_string[2], 'NOTSAFE') == 0)
						{
							$in_stat_q = "UPDATE disaster_statistic SET status = -1 WHERE subscriber_id=" . $tk_row['subscriber_id'];
							if (!mysqli_query($conn, $in_stat_q))
							{
								fwrite($error_file, PHP_EOL);
								fwrite($error_file, "\nError in notify_uri: Unable to insert data in disaster statistics. Query: " . $in_stat_q . "\n");
								mysqli_close($conn);
								fclose($error_file);
								exit(0);
							}
						}
					}
					elseif (strcmp($test_string[0], 'AUTO') == 0)
					{
						$in_stat_q = "INSERT INTO disaster_statistic(event_id, name, subscriber_id, status) VALUES(" .  $test_string[2] . ", '" . $test_string[1] . "', " . $tk_row['subscriber_id'] .  ", 0)";
						if (!mysqli_query($conn, $in_stat_q))
						{
							fwrite($error_file, PHP_EOL);
							fwrite($error_file, "\nError in notify_uri: Unable to insert data in disaster statistics. Query: " . $in_stat_q . "\n");
							mysqli_close($conn);
							fclose($error_file);
							exit(0);
						}
					}
				}
				else
				{
					fwrite($error_file, PHP_EOL);
					fwrite($error_file, "\nError in notify_uri: Text is not in correct format for CreSENSE App");
				}
			}
			else
			{
				fwrite($error_file, PHP_EOL);
				fwrite($error_file, "\nError in notify_uri: User is not registered as a subscriber");
			}
		}
	}
}

/* Success */

/* Close Connection */
mysqli_close($conn);

/* Close Error Logging File */
fclose($error_file);

?>