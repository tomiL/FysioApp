<!DOCTYPE html>
<html lang="en">
<head>
	<script>
	
	var currentUserId = -1;
	var currentDaySetId = -1;
	var currentUserActive = false;
	var currentDayId = "";
	var date = {};
	
	$(document).ready(function () {
		loadCalendar();
		getUsers();
		getExSets();
	});
		
		

	//get all users for current instructor and put them in list
	//param selectedUserId: id of user to automatically select
	function getUsers(selectedUserId){
		$.post(queryUrl, {
		request: "getUsersForInstructor",
		instructorId: currentInstructorId
		},
		function(data, status){
			var users = jQuery.parseJSON(data);
			var content = "";
			$.each(users, function(i, user){
					content += '<li class="list-group-item">'+
					'<button type="button" class="btn btn-default btn-block" id="userButton'+ user.user_id +'" onClick=openUser('+ user.user_id +')>'+ user.name +'</button></li>';
			});
			$("#usersList").html(content);
			if(selectedUserId != undefined){
				openUser(selectedUserId);
			}
		});
	}
	
	//open user and put userdata in ui
	//param userId: id of user to open
	function openUser(userId){
		$.post(queryUrl, {
		request: "getUser",
		userId: userId
		},
		function(data, status){
			if(currentUserId != -1){
				$("#userButton" + currentUserId).attr("class", "btn btn-default btn-block");
			}
			
			var user = jQuery.parseJSON(data)[0];
			$("#userNameInput").val(user.name);
			$("#userEmailInput").val(user.email);
			currentUserActive = user.is_active;
			
			$.post(queryUrl, {
			request: "getScheduledSets",
			userId: userId
			},
			function(data2, status2){
				var sets = jQuery.parseJSON(data2);
				var setData = [];
				$.each(sets, function(i, set){
					setData[i] = {
					"date": set.time, 
					"badge": (set.is_done) ? "success" : true, 
					"footer": set.is_done
					};
				});
				
				currentUserId = userId;
				$("#userButton" + currentUserId).attr("class", "btn btn-primary btn-block");
				loadCalendar(setData);
			});
		});
	}
	
	//open selected day for current user
	//param dayId: id of day to open
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
	
	//convert JS date object to dd.mm.yyyy string
	//param date: JS date object to convert
	function convertDate(date){
		return date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();
	}
	
	//load calendar display
	//param data: object containing event data for calendar
	function loadCalendar(data){
		$("#calendar").empty();
		$("#calendar").zabuto_calendar({
			language: "fi",
			cell_border: true,
			today: true,
			data: data,
			action: function(){
				return openDay(this.id);
		}});
		if(currentDayId != ""){
			openDay(currentDayId);
		}
	}
	
	//select exercise set
	//param scheduledExSetId: id of scheduled set to select
	function selectSet(scheduledExSetId){
			if(currentDaySetId != -1){
				$("#dayExListButton" + currentDaySetId).attr("class", "btn btn-default btn-block");
			}
			
			currentDaySetId = scheduledExSetId;
			$("#dayExListButton" + currentDaySetId).attr("class", "btn btn-primary btn-block");
	}
	
	//gets all exercise sets for current user and puts them in selector
	function getExSets(){
		$.post(queryUrl, {
		request: "getExSets",
		instructorId: currentInstructorId
		},
		function(data,status){
			//populate exercise set selector
			var exSets = jQuery.parseJSON(data);
			var content = "";
			
			$.each(exSets, function(i, exSet){
				content += '<option value='+ exSet.ex_set_id +'>'+ exSet.name +'</option>'
			});
			$("#exSetSelector").html(content);
		});
	}
	
	//add exercise set to currently selected day
	function addDayExSet(){
		if(currentUserId != -1 && currentDayId != ""){
			$.post(queryUrl, {
			request: "addScheduledExSet",
			userId: currentUserId,
			exSetId: $("#exSetSelector").val(),
			date: date
			},
			function(data,status){
				openUser(currentUserId);
			});
		}
	}
	
	//remove currently selected exercise set from schedule
	function deleteDayExSet(){
		if(currentUserId != -1 && currentDayId != "" && currentDaySetId != -1){
			$.post(queryUrl, {
			request: "deleteScheduledExSet",
			dayExSetId: currentDaySetId
			},
			function(data,status){
				openUser(currentUserId);
			});
		}
	}
	
	//save user data to database
	function saveUserData(){
		if(currentUserId != -1){
			$.post(queryUrl, {
			request: "saveUserData",
			userId: currentUserId,
			name: $("#userNameInput").val(),
			email: $("#userEmailInput").val()
			},
			function(data,status){
				getUsers(currentUserId);
			});
		}
	}
	
	//activate user and send random password in email
	function sendPassword(){
		if(currentUserId != -1){
			saveUserData();
			if((currentUserActive) ? confirm("Vaihdetaanko käyttäjän salasana?") : true){
				$.post("php/activate_user.php", {
				userId: currentUserId,
				email: $("#userEmailInput").val()
				},
				function(data,status){
					alert((currentUserActive) ? "Uusi salasana lähetetty \n" + data : "Käyttäjä aktivoitu ja salasana lähetetty \n" + data);
					currentUserActive = true;
				});
			}
		}
	}
	
	//add new user to database
	function newUser(){
		$.post(queryUrl, {
		request: "newUser",
		instructorId: currentInstructorId,
		userName: "Uusi käyttäjä",
		returnId: true
		},
		function(data, status){
			getUsers(data);
		});
	}
	
	//delete current user from database
	function deleteUser(){
		if(currentUserId != -1){
			if(confirm("Haluatko varmasti poistaa tämän käyttäjän?")){
				$.post(queryUrl, {
				request: "deleteUser",
				userId: currentUserId
				},
				function(data, status){
					closeUser();
					getUsers();
				});
			}
		}
	}
	
	//close current user
	function closeUser(){
		if(currentUserId != -1){
			$("#userButton" + currentUserId).attr("class", "btn btn-default btn-block");
		}
		$("#userNameInput").val("");
		$("#userEmailInput").val("");
		$("#dayExList").html("");
		
		loadCalendar();
		currentUserId = -1;
	}
	
	</script>
</head>

<body>
	<div class="row row-same-height">
		<div class="col-sm-3 col-same-height col-top col-bordered col-sm-offset-1">
			<li class="list-title">Käyttäjät</li>
			<ul class="list-group" id="usersList">
			</ul>
		</div>
		<div class="col-sm-4 col-same-height col-top">
			<h4>Käyttäjätiedot</h4>
			<br>
			<label for="userNameInput">Nimi</label>
			<input type="text" class="form-control small-item-text" maxlength="40" id="userNameInput"></textarea>
			<label for="userEmailInput">Sähköpostiosoite</label>
			<input type="text" class="form-control small-item-text" maxlength="40" id="userEmailInput"></textarea>
			<br>
			<div class="btn-group-vertical center-block">
				<button type="button" class="btn btn-default" onClick="saveUserData()">Tallenna muutokset</button>
				<button type="button" class="btn btn-default" onClick="sendPassword()">Lähetä uusi salasana</button>
			</div>
			<br><br>
			<div class="row">
				<div class="col-sm-20 col-sm-offset-2 col-bordered col-min-height">
					<ul class="list-group" id="dayExList">
					</ul>
				</div>
			</div>
			<br>
			<select class="form-control" id="exSetSelector"></select>
			<br>
			<div class="btn-group-vertical center-block">
				<button type="button" class="btn btn-default" onClick="addDayExSet()">Lisää harjoitusohjelma</button>
				<button type="button" class="btn btn-default" onClick="deleteDayExSet()">Poista harjoitusohjelma</button>
			</div>
		</div>
		<div class="col-sm-12 col-same-height col-top">
			<div id="calendar"></div>
		</div>
		<div class="col-sm-4 col-same-height col-top">
			<div class="btn-group-vertical center-block">
				<button type="button" class="btn btn-default" onClick="newUser()">Lisää uusi käyttäjä</button>
				<button type="button" class="btn btn-default" onClick="deleteUser()">Poista käyttäjä</button>
			</div>
		</div>
	</div>
</body>
</html>
