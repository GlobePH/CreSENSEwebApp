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
	$num_aff_q = "SELECT * FROM disaster_statistic WHERE event_id=" . $_GET['event_id'];
	$num_aff_r = mysqli_query($conn, $num_aff_q);
	$num_aff = mysqli_num_rows($num_aff_r);
	
	echo $num_aff;
}

mysqli_close($conn);

?>