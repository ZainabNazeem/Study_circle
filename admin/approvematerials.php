<?php
    session_start();
    include '/config/db.php'

    if (!isset($_SESSION['user_id'])||$_SESSION['ROLE']!='ADMIN'){
        header("Location:C:\xampp\htdocs\studycircle\auth\login.php");
        exit;
    }
?>
