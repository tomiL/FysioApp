<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$con = mysqli_connect('mydb.tamk.fi','t9tleino','DataBase4540','dbt9tleino1');
	mysqli_query($con, "SET NAMES utf8");

	$stmt = mysqli_prepare($con, "SELECT * FROM users
		WHERE email = ?
		AND password = ?");
	mysqli_stmt_bind_param($stmt, 'ss', $_POST['email'], $_POST['password']);
		
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	
	if($result){
		$output = mysqli_fetch_object($result);
		session_start();
		$_SESSION['login']="2";
		$_SESSION['id']=$output->user_id;
		echo 1;
	}
	else{
		echo 0;
	}
	mysqli_close($con);
}
?>