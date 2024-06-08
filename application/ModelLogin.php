<?php
#Model Login Class
class ModelLogin{
    private string $email = "";
    private string $password = "";
	private $db_conn;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
    }

    function isUserLogged(){
        #here should be asking DB if login data are right
        #also session checking if available against DB token

        #now user is always logged in system
        return true;
    }

	function getCurrentUserId(){
		return 2;	#fixed value for develop now
	}

	function getCurrentUserProjectId(){
		return 47;	#fixed value for develop now
	}
}
?>