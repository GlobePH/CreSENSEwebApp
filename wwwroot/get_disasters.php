<?php

/* Connection Properties */
$hostname	= "mysql5.gear.host";	
$database	= "cresense";
$user		= "cresense";
$pass		= "cresense_2017";

/* Start Connection */
$conn = mysqli_connect($hostname, $user, $pass, $database);
if ($conn)
{
	$dis_q = "SELECT * from disaster_details ORDER BY event_id DESC";
	$dis_r = mysqli_query($conn, $dis_q);
	if (mysqli_num_rows($dis_r) > 0)
	{
		while(($dis_row = mysqli_fetch_assoc($dis_r)))
		{
			if ($dis_row['event_id'] == $_GET['q'])
			{
				echo "<option class=\"sel_dis\" value=\"" . $dis_row['event_id'] . "\" SELECTED>" . $dis_row['event_name'] . "</option>";
			}
			else
			{
				echo "<option class=\"sel_dis\" value=\"" . $dis_row['event_id'] . "\">" . $dis_row['event_name'] . "</option>";
			}
		}
	}
	else
	{
		echo "<option class=\"sel_dis\">None</option>";
	}
}

mysqli_close($conn);

?>