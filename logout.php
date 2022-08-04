<?php
    include "./utils.php";
    $_SESSION['authToken'] = NULL;
    session_destroy();
    route('index');
?>