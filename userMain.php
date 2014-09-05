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
		<meta name="keywords" content="Google maps, jQuery, plugin, extend, pagination" />
		<meta name="description" content="An example how to use pagination and extensions with Google maps jQuery plugin" />
		<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />
		<meta name="DC.title" content="xample with Google maps and jQuery - Google maps jQuery plugin" />
		<meta name="DC.subject" content="Google maps;jQuery;plugin;extend;pagination" />
		<meta name="DC.description" content="An example how to use pagination and extensions with Google maps jQuery plugin" />
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
		<script type="text/javascript" src="js/modernizr-2.0.6/modernizr.min.js"></script>
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
			</article>
		</div>
		
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> 
		<script type="text/javascript" src="js/jquery-1.7.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/underscore-1.2.2/underscore.min.js"></script>
		<script type="text/javascript" src="js/backbone-0.5.3/backbone.min.js"></script>
		<script type="text/javascript" src="js/prettify/prettify.min.js"></script>
		<script type="text/javascript" src="js/demo.js"></script>
		<script type="text/javascript" src="ui/jquery.ui.map.js"></script>
		<script type="text/javascript" src="ui/jquery.ui.map.services.js"></script>
		<script type="text/javascript" src="ui/jquery.ui.map.extensions.js"></script>
		<script type="text/javascript">
            $(function() { 
				demo.add(function() {
					var markers = [
						{'position': '59.32893000000001,18.064910000000054', 'title': 'Stockholm, Sweden' },
						{'position': '35.6894875,139.69170639999993', 'title': 'Tokyo, Japan' },
						{'position': '13.7234186, 100.47623190000002', 'title': 'Bangkok, Thailand' },
						{'position': '51.508129,-0.12800500000003012', 'title': 'London, Great Britain' },
						{'position': '40.7143528,-74.0059731', 'title': 'New York, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '34.0522342,-118.2436849', 'title': 'Los Angeles, USA' },
						{'position': '55.75,37.616666699999996', 'title': 'Moskva, Ryssia' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '35.6894875,139.69170639999993', 'title': 'Tokyo, Japan' },
						{'position': '13.7234186, 100.47623190000002', 'title': 'Bangkok, Thailand' },
						{'position': '51.508129,-0.12800500000003012', 'title': 'London, Great Britain' },
						{'position': '40.7143528,-74.0059731', 'title': 'New York, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '34.0522342,-118.2436849', 'title': 'Los Angeles, USA' },
						{'position': '55.75,37.616666699999996', 'title': 'Moskva, Ryssia' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Test, France' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '59.32893000000001,18.064910000000054', 'title': 'Stockholm, Sweden' },
						{'position': '35.6894875,139.69170639999993', 'title': 'Tokyo, Japan' },
						{'position': '13.7234186, 100.47623190000002', 'title': 'Bangkok, Thailand' },
						{'position': '51.508129,-0.12800500000003012', 'title': 'London, Great Britain' },
						{'position': '40.7143528,-74.0059731', 'title': 'New York, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '34.0522342,-118.2436849', 'title': 'Los Angeles, USA' },
						{'position': '55.75,37.616666699999996', 'title': 'Moskva, Ryssia' },
						{'position': '59.32893000000001,18.064910000000054', 'title': 'Stockholm, Sweden' },
						{'position': '35.6894875,139.69170639999993', 'title': 'Tokyo, Japan' },
						{'position': '13.7234186, 100.47623190000002', 'title': 'Bangkok, Thailand' },
						{'position': '51.508129,-0.12800500000003012', 'title': 'London, Great Britain' },
						{'position': '40.7143528,-74.0059731', 'title': 'New York, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '34.0522342,-118.2436849', 'title': 'Los Angeles, USA' },
						{'position': '55.75,37.616666699999996', 'title': 'Moskva, Ryssia' },
						{'position': '59.32893000000001,18.064910000000054', 'title': 'Stockholm, Sweden' },
						{'position': '35.6894875,139.69170639999993', 'title': 'Tokyo, Japan' },
						{'position': '13.7234186, 100.47623190000002', 'title': 'Bangkok, Thailand' },
						{'position': '51.508129,-0.12800500000003012', 'title': 'London, Great Britain' },
						{'position': '40.7143528,-74.0059731', 'title': 'New York, USA' },
						{'position': '48.856614,2.3522219000000177', 'title': 'Paris, France' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
						{'position': '40.7143528,-74.0059731', 'title': 'San Diego, USA' },
					];
					$('#map_canvas').gmap({'zoom': 5, 'disableDefaultUI':true, 'callback': function() {
						var self = this;
						$.each(markers, function(i, marker) {
							self.addMarker(marker).click(function() {
								self.openInfoWindow({'content': this.title}, this);
							});
						});
					}}).gmap('pagination', 'title');
				}).load();
			});
        </script>
    
	</body>
</html>