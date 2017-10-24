<!DOCTYPE html>
<html lang="en">
<head>
	<script>

	var currentExPos = -1;
	var maxExPos = 0;
	var currentExeId = -1;
	var currentExSetId = -1;
	
	$(document).ready(function(){		
		//set functions for ui elements
		$("#exSetSelector").change(function(){
			openExSet($("#exSetSelector").val(), 1);
		});
		
		$('input[name="videoRadio"]').change(function(){
			selectVideo($(this).val());
		});
		
		$("#timerSet").change(function(){
			if(currentExeId != -1){
				saveDuration(currentExeId, $("#timerCheck").prop("checked"), $(this).val());
			}
		});
		
		$("#timerCheck").change(function(){
			if(currentExeId != -1){
				saveDuration(currentExeId, $(this).prop("checked"), $("#timerSet").val());
			}
		});
		
		$("#instructionTextInput").change(function(){
			if(currentExeId != -1){
				saveText(currentExeId, $(this).val());
			}
		});
		
		$("#setNameInput").change(function(){
			if(currentExSetId != -1){
				saveSetName(currentExSetId, $(this).val());
			}
		});
		
		//set parameters for timer
		$("#timerSet").TouchSpin({
			min: 0, 
			max: 300, 
			postfix: "sekuntia",
			boostat: 5, 
			maxboostedstep: 10, 
			step: 1, 
			stepinterval: 100, 
			stepintervaldelay: 500 
		});
		
		getExes();
		getExSets(-1);
	});
	
	//gets exercises from database and puts them in exercise list
	function getExes(){
		$.post(queryUrl, {
		request: "getExCategories"
		},
		function(data,status){
			//populate exercise list
			var categories = jQuery.parseJSON(data);
			var content = "";
			
			$.each(categories, function(i, category){
				content += '<div class="panel panel-default">' +
				'<div class="panel-heading">' +
				'<h4 class="panel-title"><a data-toggle="collapse" data-parent="#exList" href="#cat'+ category.ex_category_id +'">'+ category.name +'</a></h4>' +
				'</div>' +
				'<div id="cat'+ category.ex_category_id +'" class="panel-collapse collapse">' +
				'</div>' +
				'</div>';
			});	
			$("#exList").html(content);
			$.each(categories, function(i, category){
				var catContent = '<ul class="list-group">';
				$.post(queryUrl, {
				request: "getExesInCategory",
				categoryId: category.ex_category_id,
				instructorId: currentInstructorId
				},
				function(data2,status2){
					var exes = jQuery.parseJSON(data2);
					$.each(exes, function(j, exe){
						catContent += '<li class="list-group-item"><button type="button" class="btn btn-default btn-block" onClick=addExeToSet('+ exe.ex_id +')>'+
						exe.name +'</button></li>';
					});
					catContent += '</ul>';
					$("#cat" + category.ex_category_id).html(catContent);
				});
			});
		});
	}

	//gets all exercise sets for current user and puts them in selector
	//param selectedExSetId: id of exercise set to automatically select; optional
	function getExSets(selectedExSetId){
		$.post(queryUrl, {
		request: "getExSets",
		instructorId: currentInstructorId
		},
		function(data,status){
			//populate exercise set selector
			var exSets = jQuery.parseJSON(data);
			var content = "";
			var count = 0;
			
			$.each(exSets, function(i, exSet){
				content += '<option value='+ exSet.ex_set_id +'>'+ exSet.name +'</option>'
				count++;
			});
			$("#exSetSelector").html(content);
			if(selectedExSetId != undefined){
				$("#exSetSelector").val(selectedExSetId);
			}
			if(count == 0 || selectedExSetId == -1){
				closeExSet();
			}
			else{
				$("#exSetSelector").change();
			}
		});
	}
	
	//gets single exercise set from database and opens it
	//param exSetId: id of exercise set 
	//param autoOpen: position of exercise to automatically open
	function openExSet(exSetId, autoOpen){
		$.post(queryUrl, {
		request: "getExSet",
		exSetId: exSetId
		},
		function(data,status){
			//populate exercise set
			var exSet = jQuery.parseJSON(data)[0];
			var content = "";
			currentExSetId = exSetId;
			
			$("#setNameInput").val(exSet.name);

			$.post(queryUrl, {
			request: "getExesInSet",
			exSetId: exSetId
			},
			function(data2,status2){
				var exes = jQuery.parseJSON(data2);
				var count = 0;
				
				$.each(exes, function(i, exe){
					content += '<li class="list-group-item">'+
					'<button type="button" class="btn btn-default btn-block" id="exButton'+ exe.position +'" onClick=openExe('+ exe.ex_in_ex_set_id +')>'+ exe.name +'</button></li>';
					count++;
				});
				$("#currentExSet").html(content);
				
				maxExPos = count;
				if(count == 0){
					closeExe();
				}
				else{
					openExe(exes[autoOpen - 1].ex_in_ex_set_id);
				}
			});
		});
	}

	//get single exercise from database and opens it
	//param exInSetId: exercise set specific id of exercise
	function openExe(exInSetId){
		$.post(queryUrl, {
		request: "getExeInSet",
		exInSetId: exInSetId
		},
		function(data, status){
			var exe = jQuery.parseJSON(data)[0];
			
			if(currentExPos != -1){
				$("#exButton" + currentExPos).attr("class", "btn btn-default btn-block");
			}
			
			//clear current video
			selectVideo();
			
			//set exercise data to ui elements
			
			//instruction text
			$("#instructionTextInput").val(exe.instruction_text);
			
			//exercise phase video
			$("#exPhaseRadio").prop("disabled", !exe.has_video);
			if(exe.has_video){
				$.post(queryUrl, {
				request: "getVideo",
				videoId: exe.video_id
				},
				function(data2, status2){
					var video = jQuery.parseJSON(data2)[0];
					$("#exPhaseRadio").val(video.filename);
					if(!exe.has_instruction_video){
						$("#exPhaseRadio").prop("checked", true);
						$('input[name="videoRadio"]').val(video.filename);
						$('input[name="videoRadio"]').change();
					}
				});
			}
			
			//instruction phase video
			$("#instPhaseRadio").prop("disabled", !exe.has_instruction_video);
			if(exe.has_instruction_video){
				$.post(queryUrl, {
				request: "getVideo",
				videoId: exe.instruction_video_id
				},
				function(data2, status2){
					var video = jQuery.parseJSON(data2)[0];
					$("#instPhaseRadio").val(video.filename);
					$("#instPhaseRadio").prop("checked", true);
					$('input[name="videoRadio"]').val(video.filename);
					$('input[name="videoRadio"]').change();
				});
			}
			
			//timer
			$("#timerCheck").prop("checked", exe.is_timed);
			$("#timerSet").val(exe.duration);
			
			//buttons
			$("#deleteExeButton").attr("onClick", "deleteExeFromSet("+ exInSetId +")");
			$("#moveBackButton").attr("onClick", (exe.position != maxExPos) ? "moveExe("+ exInSetId +", "+ 1 +")" : "");
			$("#moveFwdButton").attr("onClick", (exe.position != 1) ? "moveExe("+ exInSetId +", "+ -1 +")" : "");
			
			currentExPos = exe.position;
			currentExeId = exInSetId;
			$("#exButton" + currentExPos).attr("class", "btn btn-primary btn-block");
		});
	}
	
	//close current exercise
	function closeExe(){
		$("#instPhaseRadio").prop("disabled", true);
		$("#exPhaseRadio").prop("disabled", true);
		$("#deleteExeButton").attr("onClick", "");
		$("#moveBackButton").attr("onClick", "");
		$("#moveFwdButton").attr("onClick", "");
		$("#timerSet").val(0);
		$("#instructionTextInput").val("");
		
		selectVideo();
		
		if(currentExPos != -1){
			$("#exButton" + currentExPos).attr("class", "btn btn-default btn-block");
		}
		
		currentExPos = -1;
		currentExId = -1;
	}
	
	//close current exercise set
	function closeExSet(){
		$("#setNameInput").val("");
		$("#currentExSet").html("");
		
		currentExSetId = -1;
		maxExPos = 0;
		
		closeExe();
	}
	
	//set or clear a video
	//param videoFile: video file location, clears video if not set
	function selectVideo(videoFile){
		if(videoFile == undefined){
			$("#videoPlayer").removeAttr("src");
		}
		else{
			$("#videoPlayer").attr("src", videoFile);
		}
		document.getElementById("videoPlayer").load();
	}
	
	//add exercise to currently open exercise set
	//param exId: id of exercise to add
	function addExeToSet(exId){
		if(currentExSetId != -1){
			$.post(queryUrl, {
			request: "addExeToSet",
			exId: exId,
			exSetId: currentExSetId,
			pos: (maxExPos + 1)
			},
			function(data, status){
				//rebuild exercise set
				openExSet(currentExSetId, maxExPos + 1);
			});
		}
	}
	
	//delete exercise from set
	//param exInSetId: exercise set specific id of exercise
	function deleteExeFromSet(exInSetId){
		$.post(queryUrl, {
		request: "deleteExeFromSet",
		exInSetId: exInSetId
		},
		function(data, status){
			//rebuild exercise set
			openExSet(currentExSetId, (currentExPos > 1) ? currentExPos - 1 : currentExPos);
		});
	}
	
	//swap exercise position with another in set
	//param exInSetId: exercise set specific id of exercise
	//param posSwitch: relative position of exercise to switch with
	function moveExe(exInSetId, posSwitch){
		$.post(queryUrl, {
		request: "swapExePosition",
		exInSetId: exInSetId,
		posSwitch: posSwitch
		},
		function(data, status){
			//rebuild exercise set
			openExSet(currentExSetId, currentExPos + posSwitch);
		});
	}
	
	//save duration data of exercise to database
	//param exInSetId: exercise set specific id of exercise
	//param isTimed: is exercise timed; boolean
	//param duration: duration of timed exercise
	function saveDuration(exInSetId, isTimed, duration){
		$.post(queryUrl, {
		request: "saveDuration",
		exInSetId: exInSetId,
		isTimed: (isTimed) ? 1 : 0,
		duration: duration
		},
		function(data, status){
			
		});
	}
	
	//save instruction text to database
	//param exInSetId: exercise set specific id of exercise
	//param instructionText: instruction text
	function saveText(exInSetId, instructionText){
		$.post(queryUrl, {
		request: "saveInstructionText",
		exInSetId: exInSetId,
		instructionText: instructionText
		},
		function(data, status){
			
		});
	}
	
	//save name of exercise set to database
	//param exSetId: id of exercise set
	//param name: name of exercise set
	function saveSetName(exSetId, name){
		$.post(queryUrl, {
		request: "saveExSetName",
		exSetId: exSetId,
		exSetName: name
		},
		function(data, status){
			
		});
	}
	
	//create new exercise set
	//param: instructorId: current instructor id
	function newExSet(instructorId){
		$.post(queryUrl, {
		request: "newExSet",
		instructorId: instructorId,
		exSetName: "Uusi harjoitusohjelma",
		returnId: true
		},
		function(data, status){
			getExSets(data);
		});
	}
	
	//delete exercise set
	//param exSetId: id of exercises set to delete
	function deleteExSet(exSetId){
		if(exSetId != -1){
			if(confirm("Haluatko varmasti poistaa tämän harjoitusohjelman?")){
				$.post(queryUrl, {
				request: "deleteExSet",
				exSetId: exSetId
				},
				function(data, status){
					getExSets(-1);
				});
			}
		}
	}
	</script>
</head>
<body>
	<div class="row row-same-height">
		<div class="col-sm-4 col-same-height col-top">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">Lisää harjoitus:</h4>
				</div>
			</div>
			<div class="panel-group" id="exList"></div>
		</div>
		<div class="col-sm-2 col-same-height col-bordered col-top">
			<input type="text" class="form-control text-center" maxlength="40" id="setNameInput"></textarea>
			<ul class="list-group" id="currentExSet">
			</ul>
		</div>
		<div class="col-sm-2 col-same-height col-top">
			<br><br><br>
			<div class="btn-group-vertical">
				<button type="button" class="btn btn-default" id="moveFwdButton"><span class="glyphicon glyphicon-chevron-up"></span></button>
				<button type="button" class="btn btn-default" id="moveBackButton"><span class="glyphicon glyphicon-chevron-down"></span></button>
				<button type="button" class="btn btn-default" id="deleteExeButton">Poista</button>
			</div>
		</div>
		<div class="col-sm-12 col-same-height col-top">
			<div class="row">
				<div class="col-sm-16">
					<div align="center" class="embed-responsive embed-responsive-16by9">
						<video controls class="embed-responsive-item" id="videoPlayer">
						</video>
					</div>
				</div>
				<div class="col-sm-8">
					<p>Näytä video</p>
					<div class="radio">
						<label><input type="radio" name="videoRadio" id="instPhaseRadio">Ohjevaihe</label>
					</div>
					<div class="radio">
						<label><input type="radio" name="videoRadio" id="exPhaseRadio">Harjoitusvaihe</label>
					</div>
				</div>
			</div>  
			<div class="form-group">
				<h4>Ohjeet</h4>
				<textarea class="form-control" rows="10" id="instructionTextInput"></textarea>
			</div>
			<div class="row">
				<div class="col-sm-4">
					<div class="checkbox">
					  <label><input type="checkbox" id="timerCheck">Ajastettu</label>
					</div>
				</div>
				<div class="col-sm-8">
					<div class="input-append spinner" data-trigger="spinner">
						<input id="timerSet" type="text" value="0" name="timerSet">	
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-same-height col-top">
			<div class="form-group">
				<label for="exSetSelector">Avaa harjoitusohjelma</label>
				<select class="form-control" id="exSetSelector">
				</select>
			</div>
			<div class="btn-group-vertical center-block">
				<button type="button" class="btn btn-default" onClick=newExSet(currentInstructorId)>Luo uusi harjoitusohjelma</button>
				<button type="button" class="btn btn-default" onClick=deleteExSet(currentExSetId)>Poista harjoitusohjelma</button>
			</div>       
		</div>
	</div>
</body>
</html>
