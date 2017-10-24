<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login</title>

    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="bootstrap/css/signin.css" rel="stylesheet">
	
	<script src="bootstrap/js/ie10-viewport-bug-workaround.js"></script>
	<script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
	
	<script>
	$(document).ready(function(){
		$("#loginForm").submit(function(){				
			$.post(($("#userRadio").prop("checked")) ? "php/user_login.php" : "php/instructor_login.php",
			{
				email: $("#inputEmail").val(),
				password: $("#inputPassword").val()
			},
			function(data, status){
				if(data=="1"){
					window.location.assign(($("#userRadio").prop("checked")) ? "fysio_user.php" : "fysio.php");
				}
				else{
					alert("Väärä sähköposti tai salasana");
				}
			});
			return false;
		});
	});
	</script>
</head>

<body>
    <div class="container">
		<div class="row">
			<div class="col-xs-4">
				<div class="radio">
					<label><input type="radio" name="userTypeRadio" id="userRadio" checked>Käyttäjä</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="userTypeRadio" id="instructorRadio">Ohjaaja</label>
				</div>
			</div>
			<div class="col-xs-12">
				<form id="loginForm" class="form-signin" method="post">
					<label for="inputEmail" class="sr-only">Sähköposti</label>
					<input id="inputEmail" name="email" class="form-control" placeholder="Sähköposti" required="" autofocus="" type="email" value="test@test.com">
					<label for="inputPassword" class="sr-only">Salasana</label>
					<input id="inputPassword" name="password" class="form-control" placeholder="Salasana" required="" type="password" value="1">
					<button class="btn btn-lg btn-primary btn-block" type="submit">Kirjaudu sisään</button>
				</form>
			</div>
		</div>
    </div>
  
</body></html>