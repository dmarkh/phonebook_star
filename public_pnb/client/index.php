<?php
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8">
<title>STAR PhoneBook v2</title>

<link rel="stylesheet" type="text/css" href="css/jquery.layout.css" >
<link rel="stylesheet" type="text/css" href="css/redmond/jquery.ui.min.css" > 
<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css" >
<link rel="stylesheet" type="text/css" href="css/styles.css" >
<link rel="stylesheet" type="text/css" href="css/jquery.jqplot.css" >
<link rel="stylesheet" type="text/css" href="css/leaflet.css" >

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.ui.min.js"></script>
<script type="text/javascript" src="js/jquery.layout.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/jquery.chained.min.js"></script>
<script type="text/javascript" src="js/strtotime.js"></script>
<script type="text/javascript" src="js/leaflet.js"></script>
<script src="https://maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script type="text/javascript" src="js/Google.js"></script>
<script src="https://api-maps.yandex.ru/2.0/?load=package.map&lang=ru-RU" type="text/javascript"></script>
<script type="text/javascript" src="js/Yandex.js"></script>

<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
<script type="text/javascript" src="js/jquery.jqplot.min.js"></script>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.pieRenderer.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.barRenderer.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.categoryAxisRenderer.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.pointLabels.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.canvasAxisLabelRenderer.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.canvasAxisTickRenderer.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="js/plugins/jqplot.canvasTextRenderer.min.js"></SCRIPT>

<script type="text/javascript" src="js/restful-client.js?ts=<?php echo time(0); ?>"></script>

<script type="text/javascript">
  var client = new RCLIENT.Phonebook();
<?php
  $hash = trim(md5(file_get_contents('./js/restful-client.js')));
  echo '  client.md5sum = "'.$hash.'";';
?>

  var myLayout;
  $(document).ready(function() { 
  	myLayout = $('body').layout({
    	applyDefaultStyles: true,
		north__spacing_open: 0,
		south__spacing_open: 0,
		east__spacing_open: 2,
		west__spacing_open: 2,
        west__size: 260,
        east__size: 0.2,
        center__onresize: function (pane, $Pane, paneState) {}
	});
	client.tabs = $('#tabs').tabs({ "active": 0 }).css({
		// 'min-height': $('.ui-layout-center').height() - $('ul.ui-tabs-nav').height(),
   		'min-height': $('.ui-layout-center').height() - 10,
   		'overflow': 'auto'
	});
	var theight = $('#tabs').height() - $('ul.ui-tabs-nav').height();
	$('#tabs-1').css({
		'padding': 0,
   		'min-height': theight,
   		'overflow': 'auto'
	});
	$('#tabs').delegate( "span.ui-icon-close", "click", function() {
		var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
		$( "#" + panelId ).remove();
		$('#tabs').tabs("refresh");
	});
 	client.initialize();
  });

</script>

</head>
<body>
 
  <div class="ui-layout-west">
	<div class="ui-layout-content ui-widget-content">
		<h2>Phonebook</h2>
		<ul style="list-style-type: none;">
			<li onClick="client.display_institutions()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Institutions</li>
			<li onClick="client.display_members()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Members</li>
		</ul>
		<ul style="list-style-type: none;">
			<li onClick="client.display_search_institutions()" style="cursor: pointer"><img src="images/icons/find.png" border=0 style="vertical-align: middle;"> Institutions</li>
			<li onClick="client.display_search_members()" style="cursor: pointer"><img src="images/icons/find.png" border=0 style="vertical-align: middle;"> Members</li>
		</ul>
		<ul style="list-style-type: none;">
			<li onClick="client.display_statistics()" style="cursor: pointer"><img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> Statistics / Graphs</li>
			<li onClick="client.display_worldmap()" style="cursor: pointer"><img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> World Map</li>
			<li>&nbsp;</li>
            <li onClick="client.get_authorlist_aps()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Author List: APS</li>
            <li onClick="client.get_authorlist_iop()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Author List: IOP</li>
            <li onClick="client.get_authorlist_arxiv()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Author List: Arxiv</li>
			<!--
			<li> <img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> <a style="text-decoration: none;" href="http://www.star.bnl.gov/public_pnb/globe_political/">Political Globe</a> </li>
			<li> <img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> <a style="text-decoration: none;" href="http://www.star.bnl.gov/public_pnb/globe_earth/">Earth Globe</a></li>
			//-->
		</ul>
	</div>
  </div>

  <div class="ui-layout-south ui-widget-header">
    <h4 class="ui-widget-header">STAR S&C Group, BNL 2013</h4>
  </div>

  <div class="ui-layout-north">
	<div style="position: absolute; top: 10px; right: 10px;" id="close_all_tabs">[close all tabs]</div>
	<div style="position: absolute; top: 10px; left: 10px; color: #FFF !important;" ><a href="https://www.star.bnl.gov/pnb/client/" style="color: #FFF; text-decoration: none;">[ to ADMIN version ]</a></div>

	<!-- <div id="notification" style="position:relative; float:left; color: red;"></div> //-->
    <div id="notification" style="display: none; position: absolute; left: 5px; top: 5px; padding: 5px; font-weight: bold; font-size: 130%; color: red; background-color: yellow; border: 1px solid red;"></div>
    <h3 class="ui-widget-header"><img src="images/icons/star.png" border=0 style="vertical-align: middle;"> <span style="color: red; text-shadow: 2px 2px #6666ff;">STAR</span> <span style="color: white; text-shadow: 2px 2px #777;">PhoneBook</span> <sup><span style="font-size: 9px;">2.0</span></sup></h3>
  </div>

 <div class="ui-layout-center">
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Intro</a></li>
            </ul>
        	<div id="tabs-1">
				<h2>I. General Information</h2>
				<p>STAR PhoneBook allows one to find out information about STAR institutions and members of each institution, as well as statistical information on the STAR collaboration.
				Menu on the left contains clickable links. Click it, see new tab appear in the central pane. 
				Tabs could be closed by click on the [x] sign at upper-right corner.
				</p>
				<p>Main options:
				<ul>
					<li>List institutions, members</li>
					<li>Search institutions and members using several algorithms including fuzzy match</li>
					<li>Explore statistical data and various visualisations</li>
				</ul>
				</p>
				<h2>II. Security</h2>
				<p><a style="text-decoration: underline;" href="http://www.bnl.gov/bnlweb/security_notice.html">Privacy and security notice</a></p>

				<h2>III. Contact Information</h2>
				<p>Please send you comments and suggestions to : <a style="text-decoration: underline" href="mailto: starphnbk@www.star.bnl.gov">starphnbk@www.star.bnl.gov</a></p>
            </div>
        </div>

 </div>

</body>
</html>
