<?php
include "functions.php";

    if(isset($_GET['add_id'])) {
        $stmt = $conn->prepare("SELECT user_id FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->execute(array($_SESSION['new_group_id'], $_GET['add_id']));
        $data = $stmt->fetch();

        if($data == NULL) {
            $stmt = $conn->prepare("INSERT INTO group_members(group_id, user_id) VALUES(?, ?)");
            $stmt->execute(array($_SESSION['new_group_id'], $_GET['add_id']));
        }
        else $error_user_is_added = "Already Was Added";

    } 
    
    if(isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $stmt = $conn->prepare("DELETE FROM group_members WHERE user_id = ?");
        $stmt->execute(array($delete_id));
        
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/new_group.css">

    <title>Add New Group</title>
</head>
<body>

<header>
        <div class="container">
            <h1>Add New Group</h1>
            <ul class="main-link">
                    <li class="big"><a href="#"><?php echo $_SESSION['username']; ?></a></li>
            </ul>
        </div>
</header>

<div class="new-group">
            <?php 
                echo "<p>$error_user_is_added</p>";
            ?>
    <div class="container">
        <form action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post">
                <div class='friends-room'>
                    <?php
                        $stmt = $conn->prepare("SELECT username, user_id, image
                                                FROM users
                                                WHERE username <> '$_SESSION[username]'
                                                AND (user_id IN (SELECT friend_id FROM friends WHERE user_id = $_SESSION[user_id])
                                                OR user_id IN (SELECT user_id FROM friends WHERE friend_id = $_SESSION[user_id]))
                                                ORDER BY date_time DESC
                                                ");
                        $stmt->execute();
                        
                    
                        while ($result = $stmt->fetch()) {
                            echo "
                                <div class='username-box'>
                                    <div class='box'>
                                    <div class='image'>
                                    <img src='$result[image]' alt='Photo'>
                                    </div>
                                    <a href='#' >$result[username]</a>
                                    </div>
                                    <div class='username-info'>
                                    <a href='?add_id=$result[user_id]' >Add</a>
                                
                                    <a href='?delete_id=$result[user_id]' >Delete</a>
                                    </div>
                                </div>
                                ";
                        }
                    ?>
                    
                </div>

                <button type="submit" name="save_new_group"><a href="call_me.php">Save</a></button>
                <button type="submit"><a href="call_me.php">Cancel</a></button>
            </form>
    </div>
</div>
</body>
</html>