<?php
#Model Login Class
class ModelLogin{
    private string $email = "";
    private string $password = "";

    function __construct(string email, string password){
        $this->email = email;
        $this->password = password;
    }

    function isUserLoged(){
        #here should be asking DB if login data are right
        #also session checking if available against DB token

        #now user is always logged in system
        return true;
    }
}
?>