<?php

header("Content-Type: text/html");

/* Connection Properties */
$hostname	= "mysql5.gear.host";	
$database	= "cresense";
$user		= "cresense";
$pass		= "cresense_2017";

/* Open Error Logging File */
$error_file = fopen("Error_Log.txt", "a");

$web_uri = 'https://devapi.globelabs.com.ph/smsmessaging/v1/outbound/3695/requests?access_token=';

/* Start Connection */
$conn = mysqli_connect($hostname, $user, $pass, $database);
if (!$conn)
{
	fwrite($error_file, PHP_EOL);
	fwrite($error_file, "\nError in index: Unable to connect to database\n");
	fclose($error_file);
	exit(0);
}

?>

<!DOCTYPE HTML>
<html>
<head>
<title>CreSENSE</title>
<style type="text/css">
html
{
	height: 100%;
	width: 100%;
}
body
{
	height: 100%;
	width: 100%;
	margin: 0;
	padding: 0;
	border: 0;
}
#banner
{
	height: 10%;
	width: 100%;
	margin: 0;
	padding: 0;
	border: 0;
	background-color: rgb(171, 171, 171);
}
#banner_pic
{
	height: 100%;
	padding-left: 2%;
}
#statistics_right
{
	height: 44%;
	width: 48%;
	margin: 0;
	padding: 0;
	padding-top: 1%;
	padding-right: 2%;
	border: 0;
	background-color: rgb(217, 217, 217);
	float: right;
}
#disaster
{
	height: 45%;
	width: 96%;
	margin: 0;
	padding-left: 2%;
	padding-right: 2%;
	border: 0;
	background-color: rgb(242,242,242);
	clear: left;
}
#display_num
{
	width: 98%;
	max-height: 100%;
	margin-top: auto;
	margin-bottom: auto;
	padding: 0;
	border-width: 1%;
	border-color: rgb(217, 217, 217);
	border-style: solid;
}
.display_row
{
	height: 34%;
	width: 50%;
	margin: 0;
	padding: 0;
	border: 0;
}
.part_title
{
	height: 10%;
	width: 50%;
	margin: 0;
	padding-top: 3%;
	padding-bottom: 2%;
	border: 0;
	font-family: DisplayFont;
	font-size: 38px;
}
#statistics_left
{
	height: 44%;
	width: 48%;
	float: left;
	margin: 0;
	padding: 0;
	padding-top: 1%;
	padding-left: 2%;
	border: 0;
	background-color: rgb(217, 217, 217);
}
#pie_chart
{
	height: 80%;
	width: 100%;
}
#select_disaster
{
	margin-right: 3%;
	float: right;
	text-align: center;
	font-family: DisplayFont;
	font-size: 15px;
	width: 30%;
}
.td_digits
{
	width: 60%;
	max-height: 30%;
	text-align: center;
	border-style: solid;
	border: 0;
	background: linear-gradient(rgb(40, 45, 30), rgb(31, 38, 18));
}
.td_label
{
	width: 40%;
	text-align: center;
	border: 0;
}
.numdigits
{
	font-family: DisplayFont;
	font-size: 84px;
	text-align: center;
	color: rgb(121, 181, 10);
	animation: blinker 5s linear infinite;
}
.dis_label
{
	font-family: DisplayFont;
	font-size: 15px;
	width: 200px;
}
.dis_input
{
	width: 300px;
}
.dis_text
{	
	width: 100%;
}
#add_dis
{
	width: 80%
	margin: auto;
	padding: 0;
	border: 0;
}
#disaster_form
{
	margin: auto;
	padding: 0;
	border: 0;
}
#form_submit
{
}
#enter_pic
{
	max-width: 100px;
	max-height: 100px;
}
@keyframes blinker {  
  50% { opacity: 0; }
}
.numlabel
{
	font-family: DisplayFont;
	font-size: 36px;
	text-align: center;
}
@font-face
{
	font-family: DisplayFont;
	src: url(Graphics/Roboto-Condensed.ttf);
}
</style>
<script type="text/javascript" src="../src/jquery.min.js"></script>
<script type="text/javascript" src="../src/jquery.jqplot.js"></script>
<script type="text/javascript" src="../src/plugins/jqplot.pieRenderer.js"></script>
<link rel="stylesheet" type="text/css" href="../src/jquery.jqplot.css" />
<script type="text/javascript">

function getDisaster(event_id)
{
	var xmlhttp1 = new XMLHttpRequest();
	
	xmlhttp1.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
		{
			document.getElementById("select_disaster").innerHTML = this.responseText;
		}
	};
	xmlhttp1.open("GET", "get_disasters.php?q=" + event_id, true);
	xmlhttp1.send();
}

function inputDisaster()
{
	var xmlhttp1 = new XMLHttpRequest();
	
	xmlhttp1.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
		{
			getDisaster(document.getElementById("select_disaster").value);
			alert("Successfully Registered the Disaster Event");
		}
	};
	xmlhttp1.open("GET", "inputdisasters.php?event_name=" + document.getElementById("disaster_name").value + "&event_desc=" + document.getElementById("desc").value + "&loc_long=" + document.getElementById("long").value + "&loc_lat=" + document.getElementById("lat").value + "&loc_rad=" + document.getElementById("aoe").value, true);
	xmlhttp1.send();
}

function setDisaster(event_id)
{
	var xmlhttp1 = new XMLHttpRequest();
	var xmlhttp2 = new XMLHttpRequest();
	var xmlhttp3 = new XMLHttpRequest();
	
	xmlhttp1.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
		{
			document.getElementById("affected_field").innerHTML = this.responseText;
		}
	};
	xmlhttp1.open("GET", "calc_affected.php?event_id=" + event_id, true);
	xmlhttp1.send();
	
	xmlhttp2.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
		{
			document.getElementById("safe_field").innerHTML = this.responseText;
		}
	};
	xmlhttp2.open("GET", "calc_safe.php?event_id=" + event_id, true);
	xmlhttp2.send();
	
	xmlhttp3.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
		{
			document.getElementById("bad_field").innerHTML = this.responseText;
		}
	};
	xmlhttp3.open("GET", "calc_bad.php?event_id=" + event_id, true);
	xmlhttp3.send();
}

$(document).ready(function(){ 
    var s1 = [['Safe',0], ['In Distress',0]];
         
    var plot = $.jqplot('pie_chart', [s1], {
        grid: {
            drawBorder: false, 
            drawGridlines: false,
            background: '#D9D9D9',
            shadow:false
        },
        axesDefaults: {
             
        },
        seriesDefaults:{
            renderer:$.jqplot.PieRenderer,
            rendererOptions: {
                showDataLabels: true
            }
        },
        legend: {
            show: true,
            rendererOptions: {
                numberRows: 1
            },
            location: 's'
        }
    });

	function updateSeries() {
		setDisaster(document.getElementById("select_disaster").value);
        var myData = [['Safe', document.getElementById("safe_field").innerHTML], ['In Distress', (document.getElementById("affected_field").innerHTML) - (document.getElementById("safe_field").innerHTML)]];
        plot.series[0].data = myData;
        plot.replot();
    }
	
	window.setInterval(updateSeries, 1000);
	
});

/*
$(document).ready(function(){
  var plot2 = $.jqplot ('chart2', [[3,7,9,1,5,3,8,2,5]], {
      // Give the plot a title.
      title: 'Plot With Options',
      // You can specify options for all axes on the plot at once with
      // the axesDefaults object.  Here, we're using a canvas renderer
      // to draw the axis label which allows rotated text.
      axesDefaults: {
        labelRenderer: $.jqplot.CanvasAxisLabelRenderer
      },
      // Likewise, seriesDefaults specifies default options for all
      // series in a plot.  Options specified in seriesDefaults or
      // axesDefaults can be overridden by individual series or
      // axes options.
      // Here we turn on smoothing for the line.
      seriesDefaults: {
          rendererOptions: {
              smooth: true
          }
      },
      // An axes object holds options for all axes.
      // Allowable axes are xaxis, x2axis, yaxis, y2axis, y3axis, ...
      // Up to 9 y axes are supported.
      axes: {
        // options for each axis are specified in seperate option objects.
        xaxis: {
          label: "X Axis",
          // Turn off "padding".  This will allow data point to lie on the
          // edges of the grid.  Default padding is 1.2 and will keep all
          // points inside the bounds of the grid.
          pad: 0
        },
        yaxis: {
          label: "Y Axis"
        }
      }
    });
});
*/

</script>
</head>

<body>
	<div id="banner">
		<img id="banner_pic" src="/Graphics/LOGO/CreSENSE Logo Final 1.png"></img>
	</div>
	<div id="statistics_left">
		<font class="part_title">View Statistics</font>
		<select id="select_disaster" onChange="setDisaster(this.value)" onClick="getDisaster(this.value)">
			<?php
				$dis_q = "SELECT * from disaster_details ORDER BY event_id DESC";
				$dis_r = mysqli_query($conn, $dis_q);
				$i = 0;
				$last_ev_id = 0;
				if (mysqli_num_rows($dis_r) > 0)
				{
					while(($dis_row = mysqli_fetch_assoc($dis_r)))
					{
						if ($i == 0)
						{
							$last_ev_id = $dis_row['event_id'];
							$i++;
						}
						echo "<option class=\"sel_dis\" value=\"" . $dis_row['event_id'] . "\">" . $dis_row['event_name'] . "</option>";
					}
				}
				else
				{
					echo "<option class=\"sel_dis\">None</option>";
				}
			?>
		</select>
		<div id="pie_chart"></div>
		<?php
			if ($i > 0)
			{
				$num_safe_q = "SELECT * FROM disaster_statistic WHERE status=1 AND event_id=" . $last_ev_id;
				$num_safe_r = mysqli_query($conn, $num_safe_q);
				$num_safe = mysqli_num_rows($num_safe_r);
				$num_bad_q = "SELECT * FROM disaster_statistic WHERE status=-1 AND event_id=" . $last_ev_id;
				$num_bad_r = mysqli_query($conn, $num_bad_q);
				$num_bad = mysqli_num_rows($num_bad_r);
				$num_non_q = "SELECT * FROM disaster_statistic WHERE status=0 AND event_id=" . $last_ev_id;
				$num_non_r = mysqli_query($conn, $num_non_q);
				$num_non = mysqli_num_rows($num_non_r);
			}
			else
			{
				$num_safe = 0;
				$num_bad = 0;
				$num_non = 0;
			}
		?>
	</div>
	<div id="statistics_right">
		<table id="display_num">
			<tr class="display_row">
				<td class="td_digits"><font class="numdigits" id="affected_field"><?php echo ($num_non + $num_safe + $num_bad); ?></font></td>
				<td class="td_label"><font class="numlabel"># of Affected<br><img src=""></img></font></td>
			</tr>
			<tr class="display_row">
				<td class="td_digits"><font class="numdigits" id="safe_field"><?php echo ($num_safe); ?></font></td>
				<td class="td_label"><font class="numlabel"># of Safe<br><img src=""></img></font></td>
			</tr>
			<tr class="display_row">
				<td class="td_digits"><font class="numdigits" id="bad_field"><?php echo ($num_bad); ?></font></td>
				<td class="td_label"><font class="numlabel"># of Distressed<br><img src=""></img></font></td>
			</tr>
		</table>
	</div>
	<div id="disaster">
		<center>
		<h3 class="part_title">Input Disaster Event</h3>
		<form id="disaster_form">
		<table id="add_dis">
			<tr>
				<td class="dis_label">Disaster</td>
				<td class="dis_input"><input class="dis_text" id="disaster_name" type="text"></td>
				<td rowspan="5"><a id="form_submit" href="javascript:inputDisaster();"><img id="enter_pic" src="/Graphics/enter.png"></img></a></td>
			</tr>
			<tr>
				<td class="dis_label">Description</td>
				<td class="dis_input"><input class="dis_text" id="desc" type="text"></td>
			</tr>
			<tr>
				<td class="dis_label">Location (LONG)</td>
				<td class="dis_input"><input class="dis_text" id="long" type="text"></td>
			</tr>
			<tr>
				<td class="dis_label">Location (LAT)</td>
				<td class="dis_input"><input class="dis_text" id="lat" type="text"></td>
			</tr>
			<tr>
				<td class="dis_label">Area of Effect</td>
				<td class="dis_input"><input class="dis_text" id="aoe" type="text"></td>
			</tr>
		</table>
		</form>
		</center>
	</div>
</body>
</html>