<?php
#Model Login Class
class ModelLogin{
    private $email = "";
    private $password = "";
	private $db_conn;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
    }

    function isUserLogged($useremail="", $password="", $usertoken=""){
        #here should be asking DB if login data are right
        #also session checking if available against DB token

        #now user is always logged in system
        #return true;

		$useremail = $useremail ? $useremail : null;
        $username = $useremail;
		$password = $password ? $password : null;
		$usertoken = $usertoken ? $usertoken : null;
		
		$wasTokenUsed = false;
        $isTimeout = false;
        $timeoutSecondsLimit = 20*60;   #for token login check timeout, this is in seconds!

        $outputArray = array(
            "isLogged" => 0,
            "token" => "",
            "timeout" => 0
        );

        $result = null;

        #if user token is provided try to use it first
		if ($usertoken != ""){
            $queryStr = "SELECT internal_id, last_logged, TIME_TO_SEC(TIMEDIFF(current_timestamp, last_logged)) FROM active_users WHERE";
            $queryStr .= " email='$useremail' AND";
            $queryStr .= " '$usertoken'=MD5(CONCAT(password, token))";
//            echo("TOKEN LOGIN: ".$queryStr."\n");
            $result = $this->db_conn->query($queryStr);

			#check if there is 1 line returned
            if ($result->num_rows > 0){
			    $wasTokenUsed = true;
            }
		}
		
		#if token wasn't used and there is name and password provided
		if ($wasTokenUsed == false && $useremail != "" && $password != ""){
			$queryStr = "SELECT internal_id, last_logged, TIME_TO_SEC(TIMEDIFF(current_timestamp, last_logged)) FROM active_users WHERE email='$useremail' AND password='$password';";
//            echo("PASS LOGIN: ".$queryStr."\n");
            $result = $this->db_conn->query($queryStr);
		}

        if ($result != null && $result->num_rows > 0){
            $row = $result->fetch_row();
            $userId = $row[0];
            $last_logged = $row[1];
            $timeDifferenceSeconds = $row[2];

            #token was not used, create new one using NOW()
            if ($wasTokenUsed == false){
                $outputArray['isLogged'] = true;
                $queryStr = "UPDATE active_users SET token=MD5(CONCAT(password, MD5(NOW()))), last_logged=NOW() WHERE internal_id=$userId;";
                $result = $this->db_conn->query($queryStr);
            }else{

                #
                # check timeout for token login
                #
                if ($timeDifferenceSeconds > $timeoutSecondsLimit){
                    $outputArray['isLogged'] = 0;
                    $outputArray['token'] = "";
                    $outputArray['timeout'] = 1;

                    #set session on SERVER
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;
                    $_SESSION['usertoken'] = '';

                    return $outputArray;
                }

                $outputArray['isLogged'] = true;
                $queryStr = "UPDATE active_users SET token=MD5(CONCAT(password, '$usertoken')), last_logged=NOW() WHERE internal_id=$userId;";
                $result = $this->db_conn->query($queryStr);
            }

            #
            #   return new token for user
            #
            $queryStr = "SELECT token, email, password FROM active_users WHERE internal_id=$userId;";
            $result = $this->db_conn->query($queryStr);
            $row = $result->fetch_row();
            $usertoken = $row[0];
            $username = $row[1];
            $password = $row[2];
            $outputArray['token'] = $usertoken;

            #set session on SERVER
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['usertoken'] = $usertoken;
        }

        return $outputArray;
    }

	function getUserIdUsingToken($username, $usertoken){
        $queryStr = "SELECT internal_id FROM active_users WHERE email='$username' AND token='$usertoken' AND TIME_TO_SEC(TIMEDIFF(current_timestamp, last_logged)) < 1200";
        $result = $this->db_conn->query($queryStr);
        if ($result->num_rows > 0) {
            $row = $result->fetch_row();
            return $row[0];
        }else{
            return null;
        }
    }

    /**
     * @return mixed|null
     * @description Method return currently logged user, it's looking into session for its username (email) and token.
     */
    function getCurrentUserId(){
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
        $usertoken = isset($_SESSION['usertoken']) ? $_SESSION['usertoken'] : "";
        return $this->getUserIdUsingToken($username, $usertoken);
	}

	function getCurrentUserProjectId(){
        $projectId = -1;
        if (isset($_SESSION["projectId"])) $projectId = $_SESSION["projectId"];
        else if(isset($_GET["projectId"])) $projectId = $_GET["projectId"];
        else if(isset($_POST["projectId"])) $projectId = $_POST["projectId"];

		return $projectId;
	}

    function getCurrentUserToken(){
        $usertoken = "";
        if (isset($_SESSION["usertoken"])) $usertoken = $_SESSION["usertoken"];

        return $usertoken;
    }

    function getCurrentUsername(){
        $username = "";
        if (isset($_SESSION["username"])) $username = $_SESSION["username"];

        return $username;
    }

    /**
     * @description This method is used to send email, it is used in registration process to send confirmation email to user.
     * @param {string} $receiver - email of receiver
     * @param {string} $subject - subject of email
     * @param {string} $body - body of email
     * @return array - {"status": 1 if success, -1 if error, "error": error message if error}
     */
    function sendMail($receiver, $subject, $body){
        $output = array(
            "status" => 0,
            "error" => ""
        );    

        try{
            $result = @mail($receiver, $subject, $body); //this method will be used, but now commented to disable it
            if ($result == false){
                $phpError = error_get_last();
                
                $output['status'] = -1;
                $output['error'] = "Failed to send email!<br/>\n".$phpError['message'];
            }else{
                $output['status'] = 1;
            }
        }catch (Exception $e){
            $output['status'] = -1;
            $output['error'] = $e->getMessage();
        }

        return $output;
    }

    /**
     * @description This method is used to add new user registration, it is called when user submit registration form.
     * It adds user to registration_waiting_users table and send email with confirmation link to user email.
     * @param {string} $username - username from registration form
     * @param {string} $password - password from registration form
     * @param {string} $email - email from registration form
     * @param {string} $token - token for email confirmation, it is generated on frontend using MD5 of email and password
     * @return array - {"status": 1 if success, -1 if error, "error": error message if error}
     */
    function addNewUserRegistration($username, $password, $email, $token){
        $output = array(
            "status" => 0,
            "error" => ""
        );
        $queryStr = "";

        /*
         *  Check that user email is not used in active users
         */
        $queryStr = "SELECT internal_id FROM active_users WHERE email='$email'";
        $result = $this->db_conn->query($queryStr);
        if ($result->num_rows > 0){
            $output['status'] = -1;
            $output['error'] = "This email is already used, this is already registered user!.";
            return $output;
        }

        /*
         *  Check that user mail is not used or if yes if it was used less than 24hours ago
         */
        $queryStr = "SELECT TIMESTAMPDIFF(HOUR, `last_update`, NOW()) AS hours_since_update FROM registration_waiting_users WHERE email='$email'";
        $result = $this->db_conn->query($queryStr);
        if ($result->num_rows > 0){
            $row = $result->fetch_row();
            $hoursSinceUpdate = $row[0];
            if ($hoursSinceUpdate < 24){
                $output['status'] = -1;
                $output['error'] = "This email is already used for registration, please check your email and click on confirmation link or wait till 24 hours to try again.";
                return $output;
            }
        }

        $queryStr = "INSERT INTO registration_waiting_users (name, email, password, token, last_update) VALUES ('$username', '$email', '$password', '$token', NOW());\n";
        $result = $this->db_conn->query($queryStr);
        if ($this->db_conn->error){
            $output['status'] = -1;
            $output['error'] = $this->db_conn->error;
        }else{
            $output['status'] = 1;
        }

        return $output;
    }

    /**
     * @description This method is used to confirm user registration, it is called when user click on link in email.
     * It moves user from registration_waiting_users table to active_users table and deletes
     * it from registration_waiting_users table.
     * @param {string} $token - token from email link, it is used to find user in registration_waiting_users table.
     * @return array - {"status": 1 if success, -1 if error, "error": error message if error}
     */
    function confirmRegistration($token){
        $output = array(
            "status" => 0,
            "error" => ""
        );

        $queryStr = "SELECT * FROM registration_waiting_users WHERE token='$token';";
        $result = $this->db_conn->query($queryStr);
        if ($result->num_rows == 1){

            $row = $result->fetch_assoc();
            $name = $row["name"];
            $email = $row["email"];
            $password = $row["password"];

            $queryStr = "INSERT INTO active_users (name, email, password) VALUES ('$name', '$email', '$password');";
            $result = $this->db_conn->query($queryStr);

            $queryStr = "DELETE FROM registration_waiting_users WHERE email='$email' AND token='$token';";
            $result = $this->db_conn->query($queryStr);

            $output['status'] = 1;
        }else{
            $output['status'] = -1;
            $output['error'] = "There is some error.";
        }
        return $output;
    }

}
?>