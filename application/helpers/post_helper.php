<?php
if(!function_exists("post")){
    function post($key = "", $default = ""){	
        if(isset($_POST[$key]) && $_POST[$key] != ""){
            return $_POST[$key];
        }else{
            if($default != ""){
                return $default;
            }else{
                return "";
            }
        }
    }
}
?>
