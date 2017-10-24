<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$con = mysqli_connect('mydb.tamk.fi','t9tleino','DataBase4540','dbt9tleino1'); 
	mysqli_query($con, "SET NAMES utf8");
	
	$password = substr(str_shuffle(MD5(microtime())), 0, 8);
	
	$stmt = mysqli_prepare($con, "UPDATE users
		SET password = ?, is_active = 1
		WHERE user_id = ?");
	mysqli_stmt_bind_param($stmt, 'si', $password, $_POST['userId']);
	mysqli_stmt_execute($stmt);
	
	
	mail($_POST['email'], "Tunnus luotu", "Uusi tunnus luotu. Salasana: ".$password);
	
	echo $password;
	
	mysqli_close($con);
}
?>