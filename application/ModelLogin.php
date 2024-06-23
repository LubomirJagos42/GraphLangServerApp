<?php
#Model Login Class
class ModelLogin{
    private string $email = "";
    private string $password = "";
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
            $result = $this->db_conn->query($queryStr);

			#check if there is 1 line returned
            if ($result->num_rows > 0){
			    $wasTokenUsed = true;
            }
		}
		
		#if token wasn't used and there is name and password provided
		if ($wasTokenUsed == false && $useremail != "" && $password != ""){
			$queryStr = "SELECT internal_id, last_logged, TIME_TO_SEC(TIMEDIFF(current_timestamp, last_logged)) FROM active_users WHERE email='$useremail' AND password='$password';";
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
}
?>