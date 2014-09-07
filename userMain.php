<?php
include "connection.php";
include "algor.php";
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
		<script type="text/javascript">
            $(function() { 
				demo.add(function() {
					$('#map_canvas').gmap({'disableDefaultUI':true, 'callback': function() {
						var self = this;
						$("[data-gmapping]").each(function(i,el) {
							var data = $(el).data('gmapping');
							self.addMarker({'id': data.id, 'tags':data.tags, 'position': new google.maps.LatLng(data.latlng.lat, data.latlng.lng), 'bounds':true }, function(map,marker) {
								$(el).click(function() {
									$(marker).triggerEvent('click');
								});
							}).click(function() {
								self.openInfoWindow({ 'content': /*$(el).find('.info-box').text()*/ data.time }, this);
							});
						});						
					}});
				}).load();
			});
        </script>
    </head>
    <body style="-webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
		<header class="dark">
			<div class="container_16">
				<h1 style='float: left; padding-top: 3px'>Sprintron Location Service</h1>
				<?php
				if ($logged == true) {
				?>
				<a href='./logout.php' style='float: right; font: 20px/20px "Arial"; padding: 7px 24px 0px 0px; color: #FFFFFF'>Logout</a>
                <li style='float: right; color: #527fc2; font: bold 20px/20px "Arial"; padding: 8px 24px 0px 0px'> <?php echo $user['Username']; ?> </li>
				<?php
				}
				?>
			</div>
		</header>
		
		<div class="container_16">
			<article class="grid_16">
				<div class="item rounded dark">
					<div id="map_canvas" class="map rounded"></div>
				</div>

				<ul style="visibility:hidden">
					<li data-gmapping='{"id":"m_1","latlng":{"lat":27.6648274,"lng":-81.51575350000002},"tags":"drupal","time":"Feb 11 2012"}'>
						<p class="info-box">Florida DrupalCamp - Feb 11 2012</p>
					</li>
					<li data-gmapping='{"id":"m_2","latlng":{"lat":1.352083,"lng":103.81983600000001},"tags":"drupal","time":"Feb 11 2012"}'>
						<p class="info-box">DrupalCamp Singapore - Mar 03 2012</p>					
					</li>
					<li data-gmapping='{"id":"m_3","latlng":{"lat":39.7391536,"lng":-104.9847034},"tags":"drupal","time":"Feb 11 2012"}'>
						<p class="info-box">DrupalCon 2012 Denver - Mar 20 2012</p>				
					</li>
					<li data-gmapping='{"id":"m_4","latlng":{"lat":36.1658899,"lng":-86.7844432},"tags":"drupal","time":"Feb 11 2012"}'>
						<p class="info-box">DrupalCamp Nashville - Apr 28 2012</p>				
					</li>
					<li data-gmapping='{"id":"m_5","latlng":{"lat":55.6760968,"lng":12.568337100000008},"tags":"drupal","time":"Feb 11 2012"}'>
						<p class="info-box">DrupalCamp Copenhagen 5.0 - May 11 2012</p>				
					</li>
				</ul>
			</article>
		</div>
	</body>
</html>