<?php 
session_start();	
if($_SESSION['login']!="2"){
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
	<link href="bootstrap/css/zabuto_calendar.css" rel="stylesheet">
	<link href="customstyles.css" rel="stylesheet">
	
    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="bootstrap/js/ie10-viewport-bug-workaround.js"></script>
	<script src="bootstrap/js/zabuto_calendar.js"></script>
	
	<script>
	var queryUrl = "php/request.php";
	var currentUserId = <?php echo $_SESSION['id'];?>;
	var currentDaySetId = -1;
	var currentDayId = "";
	var date = {};
	
	$(document).ready(function () {
		loadCalendar();
		
		$.post(queryUrl, {
			request: "getUser",
			userId: currentUserId},
			function(data,status){
				user = jQuery.parseJSON(data)[0];
				$("#nameLabel").html("<a><b>" + user.name + "</a></b>");
		});
	});
	
	function convertDate(date){
		return date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();
	}
	
	function loadCalendar(){
		$("#calendar").empty();
		
		$.post(queryUrl, {
		request: "getScheduledSets",
		userId: currentUserId
		},
		function(data, status){
			var sets = jQuery.parseJSON(data);
			var setData = [];
			$.each(sets, function(i, set){
				setData[i] = {
				"date": set.time, 
				"badge": (set.is_done) ? "success" : true, 
				"footer": set.is_done
				};
			});
			
			$("#calendar").zabuto_calendar({
			language: "fi",
			cell_border: true,
			today: true,
			data: setData,
			action: function(){
				return openDay(this.id);
			}});
		});
	}
	
	function openDay(dayId){
		currentDaySetId = -1;
		currentDayId = dayId;
		date = $("#" + dayId).data("date");
		var content = "";
		content += '<li class="list-title">'+ convertDate(new Date(date)) +'</li>';
		if($("#" + dayId).data("hasEvent")){
			$.post(queryUrl, {
			request: "getExSetsForDay",
			userId: currentUserId,
			date: date
			},
			function(data, status){
				var sets = jQuery.parseJSON(data);
				$.each(sets, function(i, set){
					content += '<li class="list-group-item">'+
					'<button type="button" class="btn btn-default btn-block" id="dayExListButton'+ set.scheduled_ex_set_id +'" onClick=selectSet('+ set.scheduled_ex_set_id +')>'+
					((set.is_done) ? '<span class="glyphicon glyphicon-ok pull-right"></span>' : "") + '<div class="text-left">' + set.name + '</div>' + 
					'</button></li>';
				});
				$("#dayExList").html(content);
			});
		}
		else{
			$("#dayExList").html(content);
		}
	}
	
	function selectSet(scheduledExSetId){
		if(currentDaySetId != -1){
			$("#dayExListButton" + currentDaySetId).attr("class", "btn btn-default btn-block");
		}
		
		currentDaySetId = scheduledExSetId;
		$("#dayExListButton" + currentDaySetId).attr("class", "btn btn-primary btn-block");
	}
	
	function startExercise(){
		if(currentDaySetId != -1){
			window.location.assign("exercise.php?exSet="+ currentDaySetId);
		}
	}
	</script>
</head>
<body>
	<div class="fill">
		<nav class="navbar navbar-default navbar-static-top">
			<div class="container">
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="php/logout.php" id="logoutButton">Kirjaudu ulos</a></li>
						<li id="nameLabel"><a>?</a></li>
					</ul>
				</div>
			</div>
		</nav>
		<div id="mainContainer" class="container">
			<div class="row row-same-height">
				<div class="col-xs-6 col-sm-offset-2 col-same-height">
					<br>
					<div class="row">
						<div class="col-xs-24 col-min-height col-bordered">
							<ul class="list-group" id="dayExList"></ul>
						</div>
					</div>
					<br>
					<button type="button" class="btn btn-default center-block" onClick=startExercise()>Aloita harjoitusohjelma</button>
				</div>
				<div class="col-xs-16 col-same-height col-top">
					<div id="calendar"></div>
				</div>
			</div>
		</div> 
	</div>
</body>
</html>