<?php
include("functions.php");

if(isset($_SESSION['new_group_id'])) {
    $stmt = $conn->prepare("DELETE FROM groups WHERE group_id = ?");
    $stmt = execute(array($_SESSION['new_group_id']));
    if($stmt->rowCount() > 0) {
        header("location: call_me.php");
        exit();
    }
    else {
        $error_user_is_added = "Error, Please Try Again.";
        header("location: new_group.php");
        exit();
    }
}

