<?php
    session_start();
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

	
	$result = mysqli_query($con, "SELECT * FROM users WHERE username ='".$username."' AND password = '".md5($password)."'");
	
    
    if ($result->num_rows > 0) {
        $_SESSION['authToken'] = md5($username.''.$password.''.time());        
    }	
    print ("<script>history.go(-1)</script>");
	?>