<!--used in adding new subjects-->
<!--To be worked on by Mico and Jett-->

<?php
    include("conn.php");

    $sImg = $_POST ['subjectBanner'];
    $sName = $_POST ["subjectName"];
    $sType = $_POST ["subjectType"];
    $sInstructor = $_POST ["subjectInstructor"];
    $sDesc = $_POST ["subjectDesc"];
    $sTimeStart = $_POST ["subjectTimeStart"];
    $sTimeEnd = $_POST ["subjectTimeEnd"];
    $uID = $_POST['user_ID'];

    
    //For the subject day (M,T,W,Th,F,Sa,Su --> Respectively)
    if (!empty($_POST['subjectDay'])){
        $sDayCode = "";
        $sDay = "";
        foreach ($_POST['subjectDay'] as $subjectDay){
            
            #this day code allows for placeholders in subjects update
            #It's not THAT elegant, but its the best way I can think of (• ▽ •;)
            $sDayCode = $sDayCode.$subjectDay;
            switch ($subjectDay){
                case "1":
                    $sDay = $sDay."MON, ";
                    break;
                case "2":
                    $sDay = $sDay."TUE, ";
                    break;
                case "3":
                    $sDay = $sDay."WED, ";
                    break;
                case "4":
                    $sDay = $sDay."THU, ";
                    break;
                case "5":
                    $sDay = $sDay."FRI, ";
                    break;
                case "6":
                    $sDay = $sDay."SAT, ";
                    break;
                default:
                    $sDay = $sDay."SUN, ";
                    break;

            }
        }
        $sDay = substr_replace($sDay ,"", -1);	
        $sDay = substr_replace($sDay ,"", -1);

        if ($sDayCode == "1234567"){
            $sDay = "EVERYDAY";
        }
        else if ($sDayCode == "1"){
            $sDay = "MONDAY";
        }
        else if ($sDayCode == "2"){
            $sDay = "TUESDAY";
        }
        else if ($sDayCode == "3"){
            $sDay = "WEDNESDAY";
        }
        else if ($sDayCode == "4"){
            $sDay = "THURSDAY";
        }
        else if ($sDayCode == "5"){
            $sDay = "FRIDAY";
        }
        else if ($sDayCode == "6"){
            $sDay = "SATURDAY";
        }
        else if ($sDayCode == "7"){
            $sDay = "SUNDAY";
        }
    }
    else{
        $sDay = null;
        $sDayCode = null;
    }

    $subjectsql = "INSERT INTO subject (subject_Image, subject_Name, subject_Type, subject_Instructor, subject_Desc, 
                    subject_Day, subject_DayCode, subject_Time_Start, subject_Time_End, user_ID)
                    VALUES ('$sImg', '$sName', '$sType', '$sInstructor', '$sDesc', '$sDay', '$sDayCode', '$sTimeStart', '$sTimeEnd', '$uID')";


    $status_heading = "Add Subject";
    $subjectlist = "SELECT `subject_Name` FROM `subject` WHERE `subject_name`='$sName' AND `user_ID`='$u_ID'";
    $result = $conn->query($subjectlist);

    #Check if subject has duplicate name
    if ($result->num_rows > 0){
        $status = "Failed to add subject " . $sName . ". Subject name already exists";
    }
    else{
        $status = "Successfully added subject " . $sName . ".";
        $conn->query($subjectsql);
        

        #Case for if user decides not to select a time
        #apparently mysql doesn't like empty values so after thorough research, it had to be forced into NULL
        if (strlen($sTimeStart) == 0 || strlen($sTimeEnd) == 0){
            echo "hello";

            $nullsql = "UPDATE subject SET subject_Time_Start=null, subject_Time_End=null WHERE `subject_name`='$sName'";
            $conn->query($nullsql);
        }
    }
    
    header("Location:subjects.php?status_heading=".$status_heading."&status=". $status. "&type=notif");
?>