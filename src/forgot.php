<?php
    include("conn.php");

    $Name = $_GET["userName"];
    $Email = $_GET["userEmail"];
    $NewPassword = $_GET["userNewPassword"];
    $RePassword = $_GET["userRePassword"];
    $Code = $_GET["code"];
    $Agree = $_GET['agree'];

    $user = $conn->query("SELECT * FROM user WHERE user_Name='$Name'")->fetch_assoc();
    $user_ID = $user['user_ID'];
    $user_Password = $user['user_Password'];

    if (is_null($user_ID)){
        header('location:help.php?help=forgot');
    }
    else{
        if ($Code == "smwm" and $Agree == "on"){
            if ($NewPassword == $RePassword){
                $sqlUpdate = "UPDATE user SET user_Password='$NewPassword' WHERE user_ID='$user_ID'";
                $conn->query($sqlUpdate);
                header('location:landing.php');
            }else{
                header('location:help.php?help=forgot');
            }
        }else{
            header('location:help.php?help=forgot');
        }
    }
?>