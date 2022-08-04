<?php
    include './utils.php';
	if(isset($_POST['username']))
		$username = $_POST['username'];
	
	if(isset($_POST['password']))
		$password = $_POST['password'];

    if(!isset($username) | !isset($password))
    {
        print ('<script>history.go(-1)</script>');
        return;
    }

	$con = mysqli_connect("localhost","root","","eboard");
	mysqli_set_charset($con, 'utf8');
	// Check connection
	if (mysqli_connect_errno())
	{
	    echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
        return;
	}

	
	$result = mysqli_query($con, "INSERT INTO users (username, password) VALUES ('".$username."','".md5($password)."')");
	
    
    if ($result) {
        $_SESSION['authToken'] = md5($username.''.$password.''.time());        
    }	
    
    route('home');
	?>