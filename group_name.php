<?php
include "functions.php";
if($_SERVER['REQUEST_METHOD'] == "POST") {
    $group_errors = add_new_group($_POST['group_name'], $_FILES['photo'], $_POST['add_new_friend']);
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
                <li class="big"><a href="call_me.php">chat</a></li>
                    <li class="big"><a href="#"><?php echo $_SESSION['username']; ?></a></li>
            </ul>
        </div>
</header>

<div class="new-group">
    <div class="container">
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
            <div class="group">

            <div class="add-image">
                <div class="image">
                    <img src='' alt=''>
                </div>
                <input type="file" name = "photo" id="file">
                <label for="file">Add Photo</label>
            </div>

            <!-- <label>Group Name</label> -->
                <input type="text" name="group_name" placeholder="Enter Group Name" >
            </div> 
                <div class='friends-room'>
                    <?php
                    global $conn;
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
                                        <label for='$result[user_id]'>$result[username]</label>
                                        <input type='checkbox' name='add_new_friend[]' id='$result[user_id]' value='$result[user_id]'> 
                                    </div>
                                </div>
                                ";
                        }
                    ?>
                    
                </div>

            <button type="submit" name="submit">Save</button>
            <button type="submit"><a href="call_me.php">Cancel</a></button>
        </form>
        <div class="group-error">
            <?php
                foreach($group_errors as $error) {
                    echo "<p>$error</p>";
                }
            ?>
        </div>
    </div>
</div>
</body>
</html>