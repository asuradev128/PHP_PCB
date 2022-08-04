<?php
    if(!isset($_SESSION["authToken"])){
        route('index');
    }
?>