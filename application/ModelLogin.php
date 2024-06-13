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
        return true;

		#
		#	This is FULL IMPLEMENTATIONS
		#
		$useremail = $useremail ? $email : null;
		$password = $password ? $password : null;
		$usertoken = $usertoken ? $usertoken : null;
		
		$wasTokenUsed = false;

		#if user token is provided try to use it first
		if ($usertoken){
			$result = $db_conn->query('SELECT last_logged FROM active_users WHERE email=`$useremail` AND token=`$usertoken`;');

			#check if there is 1 line returned
			#check time between last login and now
			#regenerated token for next user request

			$wasTokenUsed = true;
		}
		
		#if token wasn't used and there is name and password provided
		if ($wasTokenUsed == false && $useremail && $password != null){
			$result = $db_conn->query('SELECT last_logged FROM active_users WHERE email=`$useremail` AND password=MD5(`$userpassword`);');

			#check if there is 1 line returned
			#regenerated token for next user request
		}


    }

	function getCurrentUserId(){
		return 2;	#fixed value for develop now
	}

	function getCurrentUserProjectId(){
		return 47;	#fixed value for develop now
	}
}
?>