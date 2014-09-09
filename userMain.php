<?php
include "connection.php";
include "algor.php";
if (!$logged) {
	die("You can't view your data before login! <a href='index.php'>&larr; Log In</a>");
}
// later on should parse dev_list.txt to set the 1st dev as default, and change according to get method (when user select a specific device)
$selectedDevID = "1"; 
// later on should parse dev_list.txt to set the last day as default, and change according to get method (when user select a specific date)
$selectedDate = "2014-06-15";
?>

<!doctype html>
<html lang="en">
   <head>
		<title>Location Service</title>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta http-equiv="content-language" content="en" />
		<meta name="author" content="Johan Säll Larsson" />
		<meta name="viewport" content="width=device-width,initial-scale=1" />
		<meta name="keywords" content="Google maps, jQuery, plugin, HTML5, Data" />
		<meta name="description" content="An example how to use jQuery data HTML5 attribute with Google maps jQuery plugin" />
		<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />
		<meta name="DC.title" content="Example with jQuery data HTML5 attribute - Google maps jQuery plugin" />
		<meta name="DC.subject" content="Google maps;jQuery;plugin;HTML5;Data" />
		<meta name="DC.description" content="An example how to use jQuery data HTML5 attribute with Google maps jQuery plugin" />
		<meta name="DC.creator" content="Johan Säll Larsson" />
		<meta name="DC.language" content="en" />
		<link type="text/css" rel="stylesheet" href="css/960/min/960.css" />
		<link type="text/css" rel="stylesheet" href="css/960/min/960_16_col.css" />
		<link type="text/css" rel="stylesheet" href="css/normalize/min/normalize.css" />
		<link type="text/css" rel="stylesheet" href="css/prettify/min/prettify.css" />
		<link type="text/css" rel="stylesheet" href="css/style.css" />
		<style type="text/css">
			.pagination { margin: 11px; }
			.pagination .display { width: 820px; text-align: center; height: 35px; line-height: 35px; border-left: 1px solid #fff; border-right: 1px solid #fff; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;}
			.pagination .btn { cursor:pointer; width:35px; height:35px; }
			.pagination .back-btn { background:url("images/arrow_left_12x12.png") no-repeat 50% 50%; border-right: 1px solid #ccc; }
			.pagination .fwd-btn { background:url("images/arrow_right_12x12.png") no-repeat 50% 50%; border-left: 1px solid #ccc; }
		</style>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> 
		<script type="text/javascript" src="js/jquery-1.7.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/underscore-1.2.2/underscore.min.js"></script>
		<script type="text/javascript" src="js/backbone-0.5.3/backbone.min.js"></script>
		<script type="text/javascript" src="js/prettify/prettify.min.js"></script>
		<script type="text/javascript" src="js/demo.js"></script>
		<script type="text/javascript" src="ui/jquery.ui.map.js"></script>
		<script type="text/javascript" src="js/modernizr-2.0.6/modernizr.min.js"></script>

		<!-- jquery for datepicker -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
		<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
		<!-- jquery for datepicker -->
		
		<script type="text/javascript">
            $(function() { 
				// show gmap
				demo.add(function() {
					$('#map_canvas').gmap({'disableDefaultUI':true, 'callback': function() {
						var self = this;
						$("[data-gmapping]").each(function(i,el) {
							var data = $(el).data('gmapping');
							self.addMarker({'id': data.id, 'position': new google.maps.LatLng(data.latlng.lat, data.latlng.lng), 'bounds':true, 'icon': "images/small-red.png" }, function(map,marker) {
								$(el).click(function() {
									$(marker).triggerEvent('click');
								});
							}).click(function() {
								self.openInfoWindow({ 'content': data.descript }, this);
							});
						});						
					}});
				}).load();
				
				$('.dev_block').click(function() {
					if (!$(this).is('.selected')) {
						// change the color of selected dev_block
						$(this).css({
							'background' : 'linear-gradient(#EFEFEF, #FFFFFF) repeat scroll 0 0 #000000',
							'color' : '#000000'
						}).addClass('selected');
						
						// remove "selected" class of all other dev_block if any
						
						// select the lastdate shown in the clicked dev_block on calendar, to trigger google loading operation 
					}
				}); // end click;
				
				// show date picker
			    $("#datepicker").datepicker({
					onSelect: function(dateText, inst) {
						var xmlhttp;
						if (window.XMLHttpRequest) {
							// code for IE7+, Firefox, Chrome, Opera, Safari
							xmlhttp = new XMLHttpRequest();
						} else {
							// code for IE6, IE5
							xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
						}
						
						xmlhttp.onreadystatechange = function() {
							if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
								//document.getElementById("myDiv").innerHTML = xmlhttp.responseText;
								document.getElementById("dev_list").innerHTML = selectDate;
							}
						}
						var tmp = dateText.split("/");
						var selectDate = tmp[2] + '-' + tmp[0] + '-' + tmp[1];
						// document.getElementById("dev_list").innerHTML = selectDate;
						var dateFile = "users/" + "<?php echo $user['Username']; ?>" + "/" + selectDate + ".txt";
						xmlhttp.open("GET", dateFile, true);
						xmlhttp.send();
					}
				});
			});
        </script>
    </head>

    <body style="-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;min-height:425px">
		<header class="dark" style="min-width:960px">
			<div id="header_main" style="margin-left:25%">
				<h1 style='float:left;padding-top:3px'>Sprintron Device Service</h1>
				<!-- add a left padder -->
				<div style="float:right;width:33%;min-width:200px;height:30px"></div>
				<?php
				if ($logged == true) {
				?>									
					<a href='./logout.php' style='float:right;font:20px/20px "Arial";padding:7px 24px 0px 0px;color:#FFFFFF'>Logout</a>
					<li style='float:right;color:#2895f1;font:bold 20px/20px "Arial";padding:8px 16px 0px 0px'> <?php echo $user['Username']; ?> </li>
				<?php
				}
				?>
			</div>
		</header>
		
		<div id="main">
			<?php
			// gets json-data from dev_list.txt file to retrieve device list
			$arr_data = array();
			$jsondata = file_get_contents("users/".$user['Username'].'/'."dev_list.txt");
			$arr_data = json_decode($jsondata, true);
			$size = count($arr_data);
			?>
			
			<!-- if user has no device, don't show this block -->
			<div id="dev_list" style="float:left;width:25%;margin-top:30px;<?php if ($size == 0) echo "display:none";?>">
				<?php
				// if there is device, show the device buttons
				for($i = 0; $i < $size; ++$i) {
					echo "<div class='dev_block' style='margin-left:80px;margin-top:15px;width:60%;height:90px;
					border-radius:5px;color:#222222;border:2px solid #aaaaaa;font-weight:bold;background:linear-gradient(#F4F4F4, #BFBFBF) repeat scroll 0 0 #FFFFFF'>";
					echo "<div class='dev_desc' style='font-size:20px;height:90px;width:5%;vertical-align:middle;display:table-cell;text-align:center'>".$arr_data[$i]['devdescript']."</div>";
					echo "<div class='dev_id' style='display:none'>".$arr_data[$i]['devid']."</div>";
					echo "<div class='dev_type' style='display:none'>".$arr_data[$i]['devtype']."</div>";
					echo "<div class='dev_lasttime' style='display:none'>".$arr_data[$i]['lasttime']."</div>";
					echo "</div>";
				}
				?>
			</div>
			
			<!-- if user has no device, don't show this block -->
			<div class="container_16" style="float:right;width:75%;<?php if ($size == 0) echo "display:none";?>">
				<div class="grid_16" style="float:left;width:65%">
					<div class="item rounded dark">
						<div id="map_canvas" class="map rounded"></div>
					</div>
				</div>
				
				<div id="datepicker" style="float:right;width:25%;margin-top:40px;margin-right:40px">
				</div>
			</div>
			
			<!-- if user has no device, show this block -->
			<?php
				if ($size == 0) {
					echo "<h1 style='margin-top:300px;text-align:center;font-size:23px'>You have no device.</h1>";
				}
			?>
		</div>
		
		<ul style="display:none">
			<?php
				$handle = fopen("users/".$user['Username'].'/'.$selectedDevID.'/'.$selectedDate.'.txt', "r");
				$line_cnt = 0;
				if ($handle) {
					while (($line = fgets($handle)) !== false) {
						// process the line read.
						$line = trim($line);
						$lat_lng_time = explode(" ", $line);
						$descript = '日期:'.$lat_lng_time[2].' 時間:'.$lat_lng_time[3];
						echo "<li data-gmapping='{\"id\":\"m_".$line_cnt."\",\"latlng\":{\"lat\":".$lat_lng_time[0].",\"lng\":".$lat_lng_time[1]
						."},\"descript\":\"".$descript."\"}'></li>";
						$line_cnt++;
					}
					if ($line_cnt == 0) {
						echo "<h1> Fail to open file</h1>";
					}
				} else {
					echo "<h1> Fail to open file</h1>";
				}
				fclose($handle);
			?>
		</ul>
	</body>
</html>