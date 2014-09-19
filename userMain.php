<?php
// include db connect class
require_once 'db_connect.php';
// connecting to db
$db = new DB_CONNECT();

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
						
						// after received datefile.txt, paste
						xmlhttp.onreadystatechange = function() {
							if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
								var lines = (xmlhttp.responseText).trim().split('\n');
								if (lines.length != 0) {
									// only destroy the map when there is data (if no data, only remove red dot marker but remain the location)
									$('#map_canvas').gmap('destroy');
									
									// insert new li which includes the location information we got from datefile.txt
									for (var i = 0; i < lines.length; i++) {
										var latLngTime = lines[i].split(' ');
										var descript = 'Date: ' + latLngTime[2] + '  Time: ' + latLngTime[3];
										var newLi = "<li data-gmapping='{\"id\":\"m_" + i + "\",\"latlng\":{\"lat\":" + latLngTime[0] + ",\"lng\":" + latLngTime[1]
													+ "},\"descript\":\"" + descript + "\"}'></li>";
            
										$('#datetime_panel').append(newLi);
									}
	
									// add small red dot marker (up to 96 dot) to the gmap
									$('#map_canvas').gmap({'disableDefaultUI':false, 'callback': function() {
										var self = this;
										$("[data-gmapping]").each(function(i,el) {
											var data = $(el).data('gmapping');
											self.addMarker({'id': data.id, 'position': new google.maps.LatLng(data.latlng.lat, data.latlng.lng), 'bounds':true, 
											'icon': "images/small-red.png" }, function(map,marker) {
												$(el).click(function() {
													$(marker).triggerEvent('click');
												});
											}).click(function() {
												self.openInfoWindow({ 'content': data.descript }, this);
											});
										});						
									}});
								} 
							} 
						}

						// get datefile path from selected date, and current selected device(from selected dev_block div)
						var tmp = dateText.split("/");
						var selectDate = tmp[2] + '-' + tmp[0] + '-' + tmp[1];
						// build the date.txt file name
						var dateFile = "users/" + "<?php echo $user['Username']; ?>" + "/" + $('.selected').find('.dev_id').html() + "/" + selectDate + ".txt";
						// add a random GET parameter to the end of the file name, so browser won't cache it
						dateFile = dateFile + '?nocache=' + (new Date()).getTime();

						xmlhttp.open("GET", dateFile, true);
						xmlhttp.send();
						
						// clear original data in #datetime_panel div
						$('#datetime_panel').html('');
						// remove all markers on the map
						$('#map_canvas').gmap('clear', 'markers');
					}
				});
				
				// change size of the datepicker
				$('.ui-datepicker').css('font-size', $('.ui-datepicker').width() / 14 + 'px');

				// process dev_block click event 
				$('.dev_block').click(function() {
					if (!$(this).is('.selected')) {
						// remove "selected" class of all other dev_block if any
						$('.selected').css({
							'background' : 'linear-gradient(#E4E4E4, #AFAFAF) repeat scroll 0 0 #FFFFFF',
							'color' : '#000000'
						}).removeClass('selected');
						
						// change the color of selected dev_block, and add 'selected' class to the selected block, so the datepicker can find the selected one.
						$(this).css({
							'background' : 'linear-gradient(#EFEFEF, #FFFFFF) repeat scroll 0 0 #000000',
							'color' : '#000000'
						}).addClass('selected');
						
						// get year/month/day 
						var tmp = $(this).find('.dev_lasttime').html();
						tmp = tmp.split(" ");
						tmp = tmp[0].split("-");

						// select the lastdate shown in the clicked dev_block on calendar, to trigger google loading operation
						$('#datepicker').datepicker("setDate", new Date(tmp[0],tmp[1] - 1,tmp[2]));
						$('.ui-datepicker-current-day').trigger("click");
					}
				}); // end click;
				
				// when html loaded, trigger the click event on the first dev_block
				$('.dev_block:first').trigger("click");
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
					echo "<div class='dev_block' style='margin-left:auto;margin-right:auto;padding:5px;margin-top:15px;width:55%;height:60px;
					border-radius:5px;color:#222222;border:1px solid #cccccc;font-weight:bold;background:linear-gradient(#E4E4E4, #AFAFAF) repeat scroll 0 0 #FFFFFF'>";
					echo "<div class='dev_desc' style='font-size:18px;height:60px;width:5%;vertical-align:middle;display:table-cell;text-align:center'>".$arr_data[$i]['devdescript']."</div>";
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
					<div id="map_canvas_background" style="border:1px solid #cccccc;border-radius:7px;background-color:#dddddd;">
						<div id="map_canvas" class="map rounded"></div>
					</div>
				</div>
				
				<div id="datepicker" style="float:right;width:25%;margin-top:40px;margin-right:35px">
				</div>
			</div>
			
			<!-- if user has no device, show this block -->
			<?php
				if ($size == 0) {
					echo "<h1 style='margin-top:19%;text-align:center;font-size:23px'>You have no device.</h1>";
				}
			?>
		</div>
		
		<!-- use to contain location data entries -->
		<ul id="datetime_panel" style="display:none"></ul>
	</body>
</html>
