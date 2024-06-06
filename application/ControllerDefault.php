<?php
include_once 'ModelLogin.php';

#Controller Default Class
class ControllerDefault{
    private $modelLogin;

    function __construct(){
        $modelLogin = new ModelLogin();
    }

    function doDefaultRouting(){

    }
}
?>