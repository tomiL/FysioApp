<?php 
session_start();	
if($_SESSION['login']!="1"){
	header("Location: login.php");
}?>

<!DOCTYPE html>
<html lang="en"><head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Fysio Applikaatio</title>

	<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="bootstrap/css/navbar-static-top.css" rel="stylesheet">
	<link href="bootstrap/css/zabuto_calendar.css" rel="stylesheet">
	<link href="customstyles.css" rel="stylesheet">
	
    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="bootstrap/js/ie10-viewport-bug-workaround.js"></script>
	<script src="bootstrap/js/bootstrap.touchspin.js"></script>
	<script src="bootstrap/js/zabuto_calendar.js"></script>
	
	<script>
	var currentNavId="#homeNav";
	var currentInstructorId = <?php echo $_SESSION['id'];?>;
	var queryUrl = "php/request.php";
	var instructorData = {};
	
	$(document).ready(function(){	
		//get data for current instructor user
		$.post(queryUrl, {
			request: "getInstructor",
			instructorId: currentInstructorId},
			function(data,status){
				instructorData = jQuery.parseJSON(data)[0];
				$("#nameLabel").html("<a><b>" + instructorData.name + "</a></b>");
			});
		
		//set navbar functions
		$("#mainContainer").load("mainpage.php");
		$("#homeButton").click(function(){
			$("#mainContainer").load("mainpage.php");
			$(currentNavId).attr("class", "inactive");
			$("#homeNav").attr("class", "active");
			currentNavId="#homeNav";
		});
		$("#exSetsButton").click(function(){
			$("#mainContainer").load("exsets.php");
			$(currentNavId).attr("class", "inactive");
			$("#exSetsNav").attr("class", "active");
			currentNavId="#exSetsNav";
		});
		$("#exesButton").click(function(){
			$("#mainContainer").load("exes.php");
			$(currentNavId).attr("class", "inactive");
			$("#exesNav").attr("class", "active");
			currentNavId="#exesNav";
		});
		$("#usersButton").click(function(){
			$("#mainContainer").load("users.php");
			$(currentNavId).attr("class", "inactive");
			$("#usersNav").attr("class", "active");
			currentNavId="#usersNav";
		});
	})
	</script>
</head>

<body>
<div class="fill">
    <nav class="navbar navbar-default navbar-static-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div id="navbar" class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li id="homeNav" class="active"><a href="#" id="homeButton">Pääsivu</a></li>
					<li id="exSetsNav"><a href="#" id="exSetsButton">Harjoitusohjelmat</a></li>
					<li id="exesNav"><a href="#" id="exesButton">Harjoitukset</a></li>
					<li id="usersNav"><a href="#" id="usersButton">Käyttäjät</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="php/logout.php" id="logoutButton">Kirjaudu ulos</a></li>
					<li id="nameLabel"><a>?</a></li>
				</ul>
			</div>
		</div>
    </nav>

    <div id="mainContainer" class="container-fluid">

    </div> 
</div>
</body></html>