<?php
include('conn.php');

$task_ID = $_GET['task_ID'];

$query = "DELETE FROM `task` WHERE `task_ID`='$task_ID'";

if (!$conn->query($query)) {
    $status = "Failed to delete task.";
} else {
    $status = "Successfully deleted task.";
}

header('Location:tasks.php?status=' . $status);