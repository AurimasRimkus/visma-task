<?php
include 'config.php'; // File for DB configuration

if ($argc == 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This is a client registration system. 

  Usage:
  <?php echo $argv[0]; ?> <option>

  <option> can be (inputs between [] are optional):
  -add [FirstName LastName Email PhoneNumber [PhoneNumber2] [Comment]], if you want to add a new client
  -edit [Email], if you want to edit a existing client
  -delete, if you want to delete a client
  -help, to display this help message
  -import, if you want to import data from .csv file

<?php
} else {
    $handle = fopen ("php://stdin","r");
    switch($argv[1]){
        case "-add":
		    if($argc==2){
		        echo "Adding a new client \nEnter first name of client: ";
		        $firstname = trim(fgets($handle));
		        echo "\nEnter last name of client: ";
		        $lastname = trim(fgets($handle));
		        echo "\nEnter email: ";
		        $email = trim(fgets($handle));
		        while(!filter_var($email, FILTER_VALIDATE_EMAIL) && $email!=-1){
		        	echo "Email not valid. Please try again (-1 to exit)";
		        	$email = trim(fgets($handle));
		        }
		        echo "Enter a new primary phone number (format: 860000000): ";
				$phone1 = trim(fgets($handle));
				$phone1 = preg_replace("[^0-9]", "", $phone1); // delete non-numbers
				while(strlen($phone1) != 9){ // numbers are 9 digits long, so check if this one is
					echo "Wrong number, try again: ";
					$phone1 = trim(fgets($handle));
					$phone1 = preg_replace("[^0-9]", "", $phone1);
				}

		        echo "Enter a secondary phone number (optional): ";
				$phone2 = trim(fgets($handle));
				$phone2 = preg_replace("[^0-9]", "", $phone2);
				//secondary phone can either be empty or 9 digits long
				while(!(strlen($phone2) == 9 || empty($phone2))){
					echo "\nWrong number, try again: ";
					$phone2 = trim(fgets($handle));
					$phone2 = preg_replace("[^0-9]", "", $phone2);
				}

		        echo "\nEnter comment(optional): ";
		        $comment = trim(fgets($handle));

		        if(addClient($firstname, $lastname, $email, $phone1, $phone2, $comment)){
		        	echo "Client successfully added.\n";
		        }else{
		        	echo "E-mail is already in use.\n";
		        	break;
		        }

		    }elseif($argc >=6 && $argc <=8){
		    	//user input all the arguments into the command
		    	$firstname = $argv[2];
		    	$lastname = $argv[3];
		    	$email = $argv[4];
		    	$phone1 = preg_replace("[^0-9]", "", trim($argv[5]));
		    	$phone2 = null;
		    	$comment = null;
		    	if($argc>6){
		    		$phone2 = preg_replace("[^0-9]", "", trim($argv[6]));
		    		if($argc>7){
		    			$comment = $argv[7];
		    		}
		    	}

		    	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		    		echo "Email not valid, please try again with a valid e-mail.";
		    		break;
		    	}else if(strlen($phone1) != 9){
		    		echo "Primary cellphone number is not valid";
		    		break;
		    	}else if(!empty($phone2) && strlen($phone2) != 9){
		    		echo "Secondary cellphone number is not valid";
		    		break;
		    	}

		        if(addClient($firstname, $lastname, $email, $phone1, $phone2, $comment)){
		        	echo "Client successfully added.\n";
		        }else{
		        	echo "E-mail is already in use.\n";
		        	break;
		        }

		    }else{
		    	echo "Wrong argument count\n";
		    }
		    break;
		case "-edit":
		    if($argc == 2){ // two arguments - user wrote "php file.php -edit"
		    	echo "Input email of a user that you want to edit: ";
		    	$email = trim(fgets($handle));
		    	$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
		    	$stmt->execute([$email]);
		    	$user = $stmt->fetch();
		    	if($user){//this email exists in the database
		    		echo "\nEditing user with e-mail " . $user['email'] . "\n";
		    		echo "Current information: \n";
		    		echo "1. First name: " . $user['firstname'] . "\n";
		    		echo "2. Last name: " . $user['lastname'] . "\n";
		    		echo "3. Primary phone number: " . $user['phonenumber1'] . "\n";
		    		echo "4. Secondary phone number(optional): " . $user['phonenumber2'] . "\n";
		    		echo "5. Comment(optional): " . $user['comment'] . "\n";
		    		$choice = 0;
		    		while($choice != -1){
		    			echo "Which part do you want to edit? (enter number; -1 for exiting)\n";
		    			// I was thinking whether I should take all the edits and then make changes with one query to the database, or I should make a new query with each change.
		    			//I decided to stick with one change = one query, because if it was done the other way, if user accidentaly closes the program etc before exiting, all changes are lost.
		    			$choice = trim(fgets($handle));
		    			switch($choice){
		    				case 1:
		    					echo "Enter a new first name: ";
		    					$firstname = trim(fgets($handle));
		    					$sql = "UPDATE users SET firstname = ? WHERE email = ?";
								$pdo->prepare($sql)->execute([$firstname, $user['email']]);
		    					break;
		    				case 2:
								echo "Enter a new last name: ";
		    					$lastname = trim(fgets($handle));
		    					$sql = "UPDATE users SET lastname = ? WHERE email = ?";
								$pdo->prepare($sql)->execute([$lastname, $user['email']]);
		    					break;
		    				case 3:
		    					echo "Enter a new primary phone number (format: 860000000): ";
		    					$phone1 = trim(fgets($handle));
		    					$phone1 = preg_replace("[^0-9]", "", $phone1);
		    					while(strlen($phone1) != 9){
		    						echo "Wrong number, try again: ";
		    						$phone1 = trim(fgets($handle));
		    						$phone1 = preg_replace("[^0-9]", "", $phone1);
		    					}
		    					$sql = "UPDATE users SET phonenumber1 = ? WHERE email = ?";
								$pdo->prepare($sql)->execute([$phone1, $user['email']]);
		    					break;
		    				case 4:
		    					echo "Enter a new secondary phone number: ";
		    					$phone2 = trim(fgets($handle));
		    					$phone2 = preg_replace("[^0-9]", "", $phone2);
		    					while(strlen($phone2) != 9){
		    						echo "Wrong number, try again: ";
		    						$phone2 = trim(fgets($handle));
		    						$phone2 = preg_replace("[^0-9]", "", $phone2);
		    					}
		    					$sql = "UPDATE users SET phonenumber2 = ? WHERE email = ?";
								$pdo->prepare($sql)->execute([$phone2, $user['email']]);
		    					break;
		    				case 5:
		    					echo "Enter a new comment: ";
		    					$comment = trim(fgets($handle));
		    					$sql = "UPDATE users SET comment = ? WHERE email = ?";
								$pdo->prepare($sql)->execute([$comment, $user['email']]);
		    					break;
		    				case -1:
		    					break;
		    				default:
		    					echo "Wrong selection, try again (-1 for exiting)";
		    					break;
		    			}
		    		}
		    	}else{//there is no such user
		    		echo "\nUser with this email doesn't exist, maybe you should try adding it?\n";
		    	}
		    }
		    break;
		case "-delete":
		    echo "Deleting a client \n";
		    echo "Enter email of the user you want to delete: ";
		    $email = trim(fgets($handle));
		    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		    	echo "You entered a invalid email, please try again";
		    	break;
		    }
			$stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
			$stmt->execute([$email]);
			$deletedCount = $stmt->rowCount();
			if($deletedCount == 0){
				echo "There is no user associated with this e-mail.\n";
			}else{
				echo "User deleted.\n";
			}
		    break;
		case "-import":
			$filename = -1;
			if($argc>3){
				echo "To import a .csv file, use following syntax: \n";
				echo $argv[0] . " -import fileToImport.csv\n";
			}elseif($argc==3){
				$filename = $argv[2];
			}else{
				echo "Input filename of a CSV file to import: (-1 to cancel)";
				$filename = trim(fgets($handle));
			}
			if($filename == -1) break;
			if(file_exists($filename)){	
				$file = fopen($filename, "r") or die("Error!");
			}else{
				echo "Error when opening the file, try again (maybe there is no such file?)\n";
				break;
			}
			while(!feof($file)){
				$arrayOfData = explode(";", fgets($file));
				if(sizeof($arrayOfData)!=6){
					echo "Bad file format\n";
					break;
				}
				if(addClient($arrayOfData[0], $arrayOfData[1], $arrayOfData[2], $arrayOfData[3], $arrayOfData[4], $arrayOfData[5])){
					//I don't know if I need this - it can flood the console with messages if .csv file is large
		        	echo "Client successfully added.\n";
		        }else{
		        	//This can flood console, too, but it's useful information - maybe output to error file would be a better solution?
		        	echo "E-mail is already in use, client not added (". $arrayOfData[2] . ")\n";
		        	continue;
		        }
			}
			fclose($file);
			break;
		default:
		    echo "No such function exists, use \"" . $argv[0] . " --help\" for help\n";
		    break;
    }
}

function addClient($firstname, $lastname, $email, $phone1, $phone2="", $comment=""){
	include 'config.php';
	try{
	    $stmt = $pdo->prepare('INSERT INTO users VALUES(?, ?, ?, ?, ?, ?)');
	    $stmt->execute([$firstname, $lastname, $email, $phone1, empty($phone2)?NULL:$phone2, empty($comment)?NULL:$comment]);
	}catch(PDOException $e){
		$existingkey = "Integrity constraint violation: 1062 Duplicate entry";
		// If exception is thrown because e-mail is already in use by other client
		if (strpos($e->getMessage(), $existingkey) !== FALSE) {
			return false;
		} else {
			throw $e;
		}
	}
	return true;
}
?>