<?php
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}
/*
  require_once('/opt/simplesaml/lib/_autoload.php');
 
  $as = new SimpleSAML_Auth_Simple('star-sso');
  $as->requireAuth();
  $attributes = $as->getAttributes();
  print_r($attributes);
*/
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
<script type="text/javascript" src="js/Blob.js"></script>
<script type="text/javascript" src="js/FileSaver.js"></script>
<script type="text/javascript" src="js/xlsx.core.min.js"></script>
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
			<li onClick="client.display_filter_members()" style="cursor: pointer"><img src="images/icons/find.png" border=0 style="vertical-align: middle;"> Filter Members</li>
		</ul>
		<ul style="list-style-type: none;">
			<li onClick="client.display_statistics()" style="cursor: pointer"><img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> Statistics / Graphs</li>
			<li onClick="client.display_worldmap()" style="cursor: pointer"><img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> World Map</li>
			<li><img src="images/icons/stat.png" border=0 style="vertical-align: middle;"> <a style="text-decoration: none;" href="http://www.star.bnl.gov/pnb/globe_political/">Globe 1</a> <a style="text-decoration: none;" href="http://www.star.bnl.gov/pnb/globe_earth/">Globe 2</a></li>

			<li onClick="client.export_excel()" style="cursor: pointer"><img src="images/icons/excel.png" border=0 style="vertical-align: middle;"> Data Export</li>
			<li onClick="client.confirm_mass_email()" style="cursor: pointer; margin-top: 10px;"><img src="images/icons/excel.png" border=0 style="vertical-align: middle;"> Mass Email Notifications</li>
			<li onClick="client.confirm_mass_emailr()" style="cursor: pointer; margin-top: 10px;"><img src="images/icons/excel.png" border=0 style="vertical-align: middle;"> Mass Email: Representatives</li>
			<li>&nbsp;</li>
			<li onClick="client.get_authorlist_aps()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Author List: APS</li>
			<li onClick="client.get_authorlist_iop()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Author List: IOP</li>
			<li onClick="client.get_authorlist_arxiv()" style="cursor: pointer"><img src="images/icons/list.png" border=0 style="vertical-align: middle;"> Author List: ARXIV</li>
		</ul>
		<h2>Management</h2>
		<ul style="list-style-type: none;">
			<li onClick="client.display_institution_fields()" style="cursor: pointer"><img src="images/icons/edit.png" border=0 style="vertical-align: middle;"> Institution Fields</li>
			<li onClick="client.display_member_fields()" style="cursor: pointer"><img src="images/icons/edit.png" border=0 style="vertical-align: middle;"> Member Fields</li>
		</ul>
		<ul style="list-style-type: none;">
			<li onClick="client.display_institution_fieldgroups()" style="cursor: pointer"><img src="images/icons/edit.png" border=0 style="vertical-align: middle;"> Institution FieldGroups</li>
			<li onClick="client.display_member_fieldgroups()" style="cursor: pointer"><img src="images/icons/edit.png" border=0 style="vertical-align: middle;"> Member FieldGroups</li>
		</ul>
		<h2>External links</h2>
		<ul style="list-style-type: none;">
			<li><a target="_blank" href="http://www.star.bnl.gov/" style="text-decoration: none;"><img src="images/icons/link-star.png" style="vertical-align: middle;"> STAR WWW</a></li>
		</ul>
	</div>
  </div>

  <div class="ui-layout-south ui-widget-header">
    <h4 class="ui-widget-header">STAR S&C Group, BNL 2013</h4>
  </div>

  <div class="ui-layout-north">
	<div style="position: absolute; top: 10px; right: 10px;" id="close_all_tabs">[close all tabs]</div>
    <div style="position: absolute; top: 10px; left: 10px; color: #FFF !important;" ><a href="https://www.star.bnl.gov/public_pnb/client/" style="color: #FFF; text-decoration: none;">[ to PUBLIC version ]</a></div>

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
					<li>Administration tasks: create new institution. Click on "Institutions" first, then click on "create institution" button)</li>
					<li>Administration tasks: create new member. Click on "Institutions", select institution, then click on "add member" button)</li>
				</ul>
				</p>
				<h2>II. Security</h2>
				<p><a style="text-decoration: underline;" href="http://www.bnl.gov/bnlweb/security_notice.html">Privacy and security notice</a></p>

				<h2>III. Contact Information</h2>
				<p>Please send you comments and suggestions to Dmitry Arkhipkin, <a style="text-decoration: underline" href="mailto: arkhipkin@bnl.gov">arkhipkin@bnl.gov</a></p>
            </div>
        </div>

 </div>

</body>
</html>
