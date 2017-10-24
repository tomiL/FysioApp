<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$con = mysqli_connect('mydb.tamk.fi','t9tleino','DataBase4540','dbt9tleino1'); 
	mysqli_query($con, "SET NAMES utf8");

	switch($_POST['request']){
		//return all data of single instructor
		//params: instructorId
		case 'getInstructor':
		$stmt = mysqli_prepare($con, "SELECT * FROM instructors 
			WHERE instructor_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['instructorId']);
		break;
		
		//return all data of all categories
		//params: none
		case 'getExCategories':
		$stmt = mysqli_prepare($con, "SELECT * FROM exercise_categories
			ORDER BY position ASC");
		break;
		
		//return all data of listed exercises instructor has access to in single category
		//params: categoryId, instructorId
		case 'getExesInCategory':
		$stmt = mysqli_prepare($con, "SELECT * FROM exercises 
			WHERE ex_category_id = ?
			AND is_listed = 1
			AND (is_private = 0 OR author_id = ?)");
		mysqli_stmt_bind_param($stmt, 'ii', $_POST['categoryId'], $_POST['instructorId']);
		break;
		
		//return all data of single exercise set
		//params: exSetId
		case 'getExSet':
		$stmt = mysqli_prepare($con, "SELECT * FROM exercise_sets 
			WHERE ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['exSetId']);
		break;
		
		//return id, name and position of all exercises in single set
		//params: exSetId
		case 'getExesInSet':
		$stmt = mysqli_prepare($con, "SELECT ex1.ex_id AS ex_id, ex2.ex_in_ex_set_id AS ex_in_ex_set_id, name, position FROM exercises AS ex1
			INNER JOIN exercise_sets_has_exercises AS ex2 ON ex1.ex_id = ex2.ex_id
			WHERE ex_set_id = ?
			ORDER BY position ASC");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['exSetId']);
		break;
		
		//return all data of all exercise sets of instructor
		//params: instructorId
		case 'getExSets':
		$stmt = mysqli_prepare($con, "SELECT * FROM exercise_sets 
			WHERE author_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['instructorId']);
		break;
		
		//return all data of single exercise
		//params: exId
		case 'getExe':
		$stmt = mysqli_prepare($con, "SELECT * FROM exercises
			WHERE ex_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['exId']);
		break;
		
		//get all data of single exercise set specific exercise
		//params exInSetId
		case 'getExeInSet':
		$stmt = mysqli_prepare($con, "SELECT * FROM exercises AS ex1
			INNER JOIN exercise_sets_has_exercises AS ex2 ON ex1.ex_id = ex2.ex_id
			WHERE ex2.ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['exInSetId']);
		break;
		
		//return all data of single video
		//params: videoId
		case 'getVideo':
		$stmt = mysqli_prepare($con, "SELECT * FROM videos
			WHERE video_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['videoId']);
		break;
		
		//add exercise to exercise set
		//params: exId, exSetId, pos
		case 'addExeToSet':
		$stmt = mysqli_prepare($con, "INSERT INTO exercise_sets_has_exercises (ex_set_id, ex_id, position, is_timed, duration, instruction_text)
			VALUES (?, ?, ?, 
			(SELECT is_timed_def FROM exercises WHERE ex_id = ?),
			(SELECT duration_def FROM exercises WHERE ex_id = ?),
			(SELECT instruction_text_def FROM exercises WHERE ex_id = ?))");
		mysqli_stmt_bind_param($stmt, 'iiiiii', $_POST['exSetId'], $_POST['exId'], $_POST['pos'], $_POST['exId'], $_POST['exId'], $_POST['exId']);
		break;
		
		//remove exercise from exercise set
		//params: exInSetId
		case 'deleteExeFromSet':
		$preStmt1 = mysqli_prepare($con,"SELECT position, ex_set_id FROM exercise_sets_has_exercises WHERE ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($preStmt1, 'i', $_POST['exInSetId']);
		mysqli_stmt_execute($preStmt1);
		mysqli_stmt_bind_result($preStmt1, $pos, $exSetId);
		mysqli_stmt_fetch($preStmt1);
		mysqli_stmt_close($preStmt1);
		
		$preStmt2 = mysqli_prepare($con, "UPDATE exercise_sets_has_exercises
			SET position = position - 1
			WHERE position > ?
			AND ex_set_id = ?");
		mysqli_stmt_bind_param($preStmt2, 'ii', $pos, $exSetId);
		mysqli_stmt_execute($preStmt2);
		mysqli_stmt_close($preStmt2);
		
		$stmt = mysqli_prepare($con, "DELETE FROM exercise_sets_has_exercises
			WHERE ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['exInSetId']);
		break;
		
		//swap position of exercise in set with another exercise
		//params: exInSetId, posSwitch (relative position of exercise to switch with)
		case 'swapExePosition':
		$preStmt1 = mysqli_prepare($con, "SELECT position, ex_set_id FROM exercise_sets_has_exercises WHERE ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($preStmt1, 'i', $_POST['exInSetId']);
		mysqli_stmt_execute($preStmt1);
		mysqli_stmt_bind_result($preStmt1, $pos, $exSetId);
		mysqli_stmt_fetch($preStmt1);
		mysqli_stmt_close($preStmt1);
		
		$preStmt2 = mysqli_prepare($con, "UPDATE exercise_sets_has_exercises
			SET position = position - ?
			WHERE position = ? + ?
			AND ex_set_id = ?");
		mysqli_stmt_bind_param($preStmt2, 'iiii', $_POST['posSwitch'], $pos, $_POST['posSwitch'], $exSetId);
		mysqli_stmt_execute($preStmt2);
		mysqli_stmt_close($preStmt2);
		
		$stmt = mysqli_prepare($con, "UPDATE exercise_sets_has_exercises
			SET position = position + ?
			WHERE ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'ii', $_POST['posSwitch'], $_POST['exInSetId']);
		break;
			
		case 'saveDuration':
		$stmt = mysqli_prepare($con, "UPDATE exercise_sets_has_exercises
			SET duration = ?, is_timed = ?
			WHERE ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'iii', $_POST['duration'], $_POST['isTimed'], $_POST['exInSetId']);
		break;
		
		case 'saveInstructionText':
		$stmt = mysqli_prepare($con, "UPDATE exercise_sets_has_exercises
			SET instruction_text = ?
			WHERE ex_in_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'si', $_POST['instructionText'], $_POST['exInSetId']);
		break;
		
		case 'saveExSetName':
		$stmt = mysqli_prepare($con, "UPDATE exercise_sets
			SET name = ?
			WHERE ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'si', $_POST['exSetName'], $_POST['exSetId']);
		break;
		
		case 'newExSet':
		$stmt = mysqli_prepare($con, "INSERT INTO exercise_sets (author_id, name)
			VALUES (?, ?)");
		mysqli_stmt_bind_param($stmt, 'is', $_POST['instructorId'], $_POST['exSetName']);
		break;
		
		case 'deleteExSet':
		$stmt = mysqli_prepare($con, "DELETE FROM exercise_sets
			WHERE ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['exSetId']);
		break;
		
		case 'getUsersForInstructor':
		$stmt = mysqli_prepare($con, "SELECT * FROM users 
			INNER JOIN instructors_has_users ON users.user_id = instructors_has_users.user_id
			WHERE instructor_id = ?
			ORDER BY users.name ASC");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['instructorId']);
		break;
		
		case 'getUser':
		$stmt = mysqli_prepare($con, "SELECT * FROM users 
			WHERE user_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['userId']);
		break;
		
		case 'getScheduledSets':
		$stmt = mysqli_prepare($con, "SELECT * FROM scheduled_exercise_sets
			INNER JOIN exercise_sets ON scheduled_exercise_sets.ex_set_id = exercise_sets.ex_set_id
			WHERE user_id = ?
			ORDER BY is_done DESC");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['userId']);
		break;
		
		case 'getScheduledSet':
		$stmt = mysqli_prepare($con, "SELECT * FROM scheduled_exercise_sets
			INNER JOIN exercise_sets ON scheduled_exercise_sets.ex_set_id = exercise_sets.ex_set_id
			WHERE scheduled_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['dayExSetId']);
		break;
		
		case 'getExSetsForDay':
		$stmt = mysqli_prepare($con, "SELECT * FROM scheduled_exercise_sets
			INNER JOIN exercise_sets ON scheduled_exercise_sets.ex_set_id = exercise_sets.ex_set_id
			WHERE user_id = ?
			AND time = ?");
		mysqli_stmt_bind_param($stmt, 'is', $_POST['userId'], $_POST['date']);
		break;
		
		case 'addScheduledExSet':
		$stmt = mysqli_prepare($con, "INSERT INTO scheduled_exercise_sets (ex_set_id, user_id, time)
			VALUES (?, ?, ?)");
		mysqli_stmt_bind_param($stmt, 'iis', $_POST['exSetId'], $_POST['userId'], $_POST['date']);
		break;
		
		case 'deleteScheduledExSet':
		$stmt = mysqli_prepare($con, "DELETE FROM scheduled_exercise_sets
			WHERE scheduled_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['dayExSetId']);
		break;
		
		case 'saveUserData':
		$stmt = mysqli_prepare($con, "UPDATE users
			SET name = ?, email = ?
			WHERE user_id = ?");
		mysqli_stmt_bind_param($stmt, 'ssi', $_POST['name'], $_POST['email'], $_POST['userId']);
		break;
		
		case 'newUser':
		$preStmt1 = mysqli_prepare($con, "INSERT INTO users (name)
			VALUES (?)");
		mysqli_stmt_bind_param($preStmt1, 's', $_POST['userName']);
		mysqli_stmt_execute($preStmt1);
		$id = mysqli_insert_id($con);
		mysqli_stmt_close($preStmt1);

		
		$stmt = mysqli_prepare($con, "INSERT INTO instructors_has_users (instructor_id, user_id, edit_user_data_right, set_exercises_right)
			VALUES (?, ?, 1, 1)");
		mysqli_stmt_bind_param($stmt, 'ii', $_POST['instructorId'], $id);
		break;
		
		case 'deleteUser':
		$stmt = mysqli_prepare($con, "DELETE FROM users
			WHERE user_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['userId']);
		break;
		
		case 'setExeDone':
		$stmt = mysqli_prepare($con, "UPDATE scheduled_exercise_sets
			SET is_done = 1
			WHERE scheduled_ex_set_id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $_POST['dayExSetId']);
		break;
		
		default:
	}

	mysqli_stmt_execute($stmt);
	
	if(array_key_exists('returnId', $_POST)){
		if($_POST['returnId'] == true){
			if(isset($id)){
				echo $id;
			}
			else{
				echo(mysqli_insert_id($con));
			}
		}
	}
	else{
		$result = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($result) == 0){
			$output = "";
		}
		
		while($row = mysqli_fetch_array($result)){
			$output[] = $row;
		}
		echo(json_encode($output, JSON_FORCE_OBJECT));
	}
	mysqli_close($con);
}
?>