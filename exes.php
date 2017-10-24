<!DOCTYPE html>
<html lang="en">
<head>
	<script>
	$(document).ready(function(){
		getExes();
		
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
						catContent += '<li class="list-group-item"><button type="button" class="btn btn-default btn-block">'+ exe.name +'</button></li>';
					});
					catContent += '</ul>';
					$("#cat" + category.ex_category_id).html(catContent);
				});
			});
		});
	}
	</script>
</head>
<body>
	<div class="row row-same-height">
		<div class="col-sm-4 col-same-height col-top">
			<div class="panel-group" id="exList"></div>
		</div>
		<div class="col-sm-2 col-same-height col-top">
		</div>
		<div class="col-sm-2 col-same-height col-top">
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
		</div>
	</div>
</body>
</html>
