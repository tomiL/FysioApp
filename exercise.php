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
	<link href="customstyles.css" rel="stylesheet">
	
    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="bootstrap/js/ie10-viewport-bug-workaround.js"></script>

	<script>
	var queryUrl = "php/request.php";
	var exSetId = <?php echo $_GET['exSet']; ?>;
	var exSet = [];
	var exPhase = {duration: 0, isTimed: false, hasVideo: false, videoId: 0};
	var currentExPosition = -1;
	var inInstPhase = false;
	
	$(document).ready(function () {
		$.post(queryUrl, {
		request: "getScheduledSet",
		dayExSetId: exSetId
		},
		function(data, status){
			var daySet = jQuery.parseJSON(data)[0];
			$("#exSetName").text(daySet.name);
			$.post(queryUrl, {
			request: "getExesInSet",
			exSetId: daySet.ex_set_id
			},
			function(data2, status2){
				var exes = jQuery.parseJSON(data2);
				var content = "";
				$.each(exes, function(i, exe){
					content += '<li class="list-group-item text-center" id="exListItem' + exe.position + '">'+ exe.name + '</li>';
					exSet[i] = exe.ex_in_ex_set_id;
				});
				$("#exList").html(content);
				nextExe();
			});
		});
	});
	
	function openExe(exId){
		$("#progBar").prop("hidden", true);
		if(currentExPosition != -1){
			$("#exListItem" + currentExPosition).attr("class", "list-group-item text-center");
		}
		$.post(queryUrl, {
		request: "getExeInSet",
		exInSetId: exId
		},
		function(data, status){
			var exe = jQuery.parseJSON(data)[0];
			$("#instructionText").text(exe.instruction_text);
			currentExPosition = exe.position;
			$("#exListItem" + exe.position).attr("class", "list-group-item text-center list-group-item-success");
			
			exPhase.duration = exe.duration;
			exPhase.isTimed = exe.is_timed;
			exPhase.hasVideo = exe.has_video;
			exPhase.videoId = exe.video_id;
		
			if(exe.has_instruction_phase){
				if(exe.has_instruction_video){
					loadVideo(exe.instruction_video_id);
				}
				else{
					$("#videoPlayer").removeAttr("src");
					document.getElementById("videoPlayer").load();
				}
				$("#fwdButton").text("Aloita");
				inInstPhase = true;
			}
			else{
				runExe();
			}
		});
	}
	
	function nextExe(){
		$("#timeBar").stop(true);
		$("#timeBar").attr("style", "width: 0%");
		if(inInstPhase){
			inInstPhase = false;
			$("#fwdButton").text("Seuraava");
			runExe();
		}
		else{
			if(exSet.length == 0){
				endSet();
			}
			openExe(exSet.shift());
		}
	}
	
	function runExe(){
		if(exPhase.hasVideo){
			loadVideo(exPhase.videoId);
		}
		else{
			$("#videoPlayer").removeAttr("src");
			document.getElementById("videoPlayer").load();
		}
		if(exPhase.isTimed){
			$("#progBar").prop("hidden", false);
			$("#timeBar").animate({width: "100%"}, exPhase.duration*1000, function(){
				nextExe();
			});
		}
	}
	
	function loadVideo(videoId){
		$.post(queryUrl, {
		request: "getVideo",
		videoId: videoId
		},
		function(data, status){
			var video = jQuery.parseJSON(data)[0];
			$("#videoPlayer").attr("src", video.filename);
			document.getElementById("videoPlayer").load();
		});
	}
	
	function endSet(){
		$.post(queryUrl, {
		request: "setExeDone",
		dayExSetId: exSetId
		},
		function(data, status){
			window.location.assign("fysio_user.php");
		});
	}
	
	</script>
</head>
<body>
	<div class="fill">
		<nav class="navbar navbar-default navbar-static-top">
			<div class="container">
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-left">
						<li><a href="fysio_user.php" id="backButton">Takaisin</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="php/logout.php" id="logoutButton">Kirjaudu ulos</a></li>
					</ul>
				</div>
			</div>
		</nav>
		<div class="container">
			<div class="row row-same-height">
				<div class="col-xs-4 col-same-height col-bordered">
					<li class="list-title" id="exSetName">?</li>
					<ul class="list-group" id="exList">
					</ul>
				</div>
				<div class="col-xs-2 col-same-height col-top"></div>
				<div class="col-xs-16 col-same-height col-top">
					<div class="row">
						<div class="col-xs-18 col-xs-offset-3">
							<div align="center" class="embed-responsive embed-responsive-16by9">
								<video controls autoplay loop class="embed-responsive-item" id="videoPlayer"></video>
							</div>
						</div>
					</div>
					<br>
					<button type="button" class="btn btn-default btn-lg center-block" id="fwdButton" onClick=nextExe()>Seuraava</button>
					<br>
					<div class="progress" id="progBar">
						<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%" id="timeBar">
						</div>
					</div>
					<br>
					<div class="well col-min-height" id="instructionText"></div>
				</div>
				<div class="col-xs-2 col-same-height col-top"></div>
			</div>
		</div> 
	</div>
</body>
</html>