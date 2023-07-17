<?php
ob_start();
include "functions.php";

if(isset($_SESSION['username'])) {
    if (isset($_POST['send_message'])) {
        $message = $_POST['enter_message'];
        if ($message != "") {
            if (isset($_GET['u_id'])) {
               $check =  check_value("user_id", "users", "user_id", $_GET['u_id']);
               if(! empty($check)) {
                    $stmt = $conn->prepare("INSERT INTO messages(message_info, from_id, to_id) VALUES(?,?,?)");
                    $stmt->execute(array($message, $_SESSION['user_id'], $_GET['u_id']));
                    if ($stmt->rowCount() > 0) {
                        $stmt = $conn->prepare("UPDATE users SET date_time = NOW() WHERE user_id = ? OR user_id = ?");
                        $stmt->execute(array($_SESSION['user_id'], $_GET['u_id']));
                    }
                }
            }

            if (isset($_GET['g_id'])) {
                $check =  check_value("group_id", "groups", "group_id", $_GET['g_id']);
                if(! empty($check)) {
                    $stmt = $conn->prepare("INSERT INTO messages(message_info, from_id, to_id) VALUES(?, ?, ?)");
                    $stmt->execute(array($message, $_SESSION['user_id'],$_GET['g_id']));
                    if ($stmt->rowCount() >= 1) {
                        $stmt = $conn->prepare("UPDATE groups SET g_date_time = NOW() WHERE group_id = ?");
                        $stmt->execute(array($_GET['g_id']));
                    }
                }
            }
        }
    }

    if (isset($_POST['send_file'])) {
    //    $chat_files_error =  add_chat_files($_FILES['files']);
        $files = $_FILES['files'];
        $files_errors = array();
        $array_extension = array("jpg", "gif", "jpeg", "png");

        for($i=0; $i < count($files['name']); $i++) {

            if($files['error'][$i] == 0) {
                
                $file_extension = strtolower(end(explode(".", $files['name'][$i])));

                if(in_array($file_extension, $array_extension)) {
                    $file_location = $files['tmp_name'][$i];

                    $random  = rand(1, 10000000);
                    $file_upload = "/backend_project/faceyou/upload/chat_imgs/" . $random . "_" . $files['name'][$i];
                    $file_upload_to_database = "./upload/chat_imgs/" . $random . "_" . $files['name'][$i];

                    if(move_uploaded_file($file_location, $_SERVER['DOCUMENT_ROOT'] .  $file_upload)) {
                        
                        if (isset($_GET['u_id'])) {
                            $check =  check_value("user_id", "users", "user_id", $_GET['u_id']);
                            if(! empty($check)) {
                                $stmt = $conn->prepare("INSERT INTO pictures(image_name, image_from_id, image_to_id)
                                                        VALUES(? ,?, ?)");
                                $stmt->execute(array($file_upload_to_database, $_SESSION['user_id'], $_GET['u_id']));
                                if ($stmt->rowCount() > 0) {
                                    $stmt = $conn->prepare("UPDATE users SET date_time = NOW() WHERE user_id = ? OR user_id = ?");
                                    $stmt->execute(array($_SESSION['user_id'], $_GET['u_id']));
                                }
                            }
                        }

                        if (isset($_GET['g_id'])) {
                            $check =  check_value("group_id", "groups", "group_id", $_GET['g_id']);
                            if(! empty($check)) {
                                $stmt = $conn->prepare("INSERT INTO pictures(image_name, image_from_id, image_to_id)
                                                        VALUES(?,?,?)");
                                $stmt->execute(array($file_upload_to_database, $_SESSION['user_id'], $_GET['g_id']));
                                if ($stmt->rowCount() > 0) {
                                    $stmt = $conn->prepare("UPDATE groups SET g_date_time = NOW() WHERE group_id = ?");
                                    $stmt->execute(array($_GET['g_id']));
                                }
                            }
                        }
                    }
                    else {
                        $files_errors[] = "Error, File " . ($i + 1) . " Not Add, Please Try Again!";
                    }
                }
                else {
                    $files_errors[] = "Extension File " . ($i + 1) . " Not Exist";
                }
            }
            elseif ($files['error'][$i] == 4) {
                $files_errors[] = "File " . ($i + 1) . " Is Empty, Please Try Again!";
            }
        }
    }

    if (isset($_POST['add_new_friend'])) {
        if ($_POST['search_about_new_friend'] != "") {
            $stmt = $conn->prepare("SELECT user_id FROM users
                                    WHERE username <> :username
                                    AND username = :search_about_new_friend
                                    AND user_id NOT IN (SELECT friend_id FROM friends WHERE user_id = :user_id)
                                    AND user_id NOT IN (SELECT user_id FROM friends WHERE friend_id = :friend_id)
                                    ");
            $stmt->execute(array(
                                "username" => $_SESSION['username'], 
                                "search_about_new_friend" => $_POST['search_about_new_friend'], 
                                "user_id" => $_SESSION['user_id'], 
                                "friend_id" => $_SESSION['user_id']));
            $result = $stmt->fetch();

            // echo "<pre>";
            // print_r($result);
            // echo "</pre>";
            // echo $_SESSION['username'] . "<br>";
            // echo $_SESSION['user_id'] . "_" . "user_id";

            if ($result != null) {
                if(isset($_SESSION['user_id'])) {
                    $stmt2 = $conn->prepare("INSERT INTO friends(user_id, friend_id) VALUES(:user_id, :friend_id)");
                    $stmt2->execute(array(
                                        "user_id" => $_SESSION['user_id'], 
                                        "friend_id" => $result['user_id']));
                }
            } else {
                $_SESSION['the_friend_not_found'] = "Not Found!";
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./CSS/call_me_style.css">

        <title>ChatM2M</title>
    </head>
    <body>
    <header>
        <div class="container">
            <div class="search-box">
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                    <button type="submit" name="send_name">Find</button>
                    <input type="text" name="search_about_name" placeholder="Friend">
                </form>
            </div>
                <?php
                    if (isset($_GET['u_id'])) {
                        $check =  check_value("user_id", "users", "user_id", $_GET['u_id']);
                         if(! empty($check)) {
                            $data = get_user_name($_GET['u_id']);
                            echo "
                                <div class='username-box'>
                                    <div class='image'>
                                        <img src='$data[image]' alt='Photo'>
                                    </div>
                                    <div class='username-info'>
                                        <h3>$data[username]</h3>
                                        <p>$data[date_time]</p>
                                    </div>
                                </div>
                                ";
                         }
                    }

                    if (isset($_GET['g_id'])) {
                        $check =  check_value("group_id", "groups", "group_id", $_GET['g_id']);
                        if(! empty($check)) {
                            $data = get_group_name($_GET['g_id']);
                            echo "
                                <div class='username-box'>
                                    <div class='image'>
                                        <img src='$data[group_image]' alt='Photo'>
                                    </div>
                                    <div class='username-info'>
                                        <h3>$data[group_name]</h3>
                                        <p>$data[g_date_time]</p>
                                    </div>
                                </div>
                                ";
                        }
                    }

                ?>
            <div class="search-box">
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                    <button type="submit" name="add_new_friend">Add</button>
                                <?php
                    if (isset($_SESSION['the_friend_not_found'])) {
                        echo "<input type='text' name='search_about_new_friend' placeholder='$_SESSION[the_friend_not_found]'>";
                        unset($_SESSION['the_friend_not_found']);
                    } else {
                        echo "<input type='text' name='search_about_new_friend' placeholder='New Friend'>";
                    }
                    ?>
                </form>
            </div>
            <div class="container-header">
                <ul class="main-link">
                    <li class="big"><a href="group_name.php">New Group</a></li>
                    <li class="big"><a href="#"><?php echo $username = $_SESSION['username'] ?  $_SESSION['username'] :  '';?></a></li>
                    <li class="big"><a href="logout.php">Logout </a></li>
                </ul>
            </div>
        </div>
    </header>

    <section class="landing">
        <div class="container">
            <div class="friends">
                <?php

                if (isset($_POST['send_name'])) {
                    $search = $_POST['search_about_name'];

                    if (! empty($search)) {
                        $like ="%$search%";
                        $stmt = $conn->prepare("SELECT username, user_id, image, date_time
                                                FROM users
                                                WHERE username <> :username
                                                AND (user_id IN (SELECT friend_id FROM friends WHERE user_id = :user_id)
                                                OR user_id IN (SELECT user_id FROM friends WHERE friend_id = :friend_id))
                                                AND username LIKE :likes
                                                ORDER BY date_time DESC
                                                ");
                $stmt->execute(array(
                                    "username" => $_SESSION['username'], 
                                    "user_id" => $_SESSION['user_id'], 
                                    "friend_id" => $_SESSION['user_id'], 
                                    "likes" => $like));
                        
                        $arr = [];
                        while ($result = $stmt->fetch()) {
                            $arr[$result['date_time']] = $result;
                        }

                        $stmt = $conn->prepare("SELECT group_name, group_id, group_image, g_date_time
                                                FROM groups
                                                WHERE group_id IN(SELECT group_id FROM group_members WHERE user_id = :user_id)
                                                AND group_name LIKE :likes
                                                ORDER BY g_date_time DESC
                                                ");
                        $stmt->execute(array(
                                        "user_id" => $_SESSION['user_id'], 
                                        "likes" => $like));
                        while ($result = $stmt->fetch()) {
                            $arr[$result['g_date_time']] = $result;
                        }

                        krsort($arr);

                        foreach ($arr as $values) {
                            foreach ($values as $key => $val) {
                                if ($key == "username") {
                                    echo "
                                        <div class='friends-room'>
                                            <div class='username-box'>
                                                <div class='image'>
                                                    <img src='$values[image]' alt='Photo'>
                                                </div>
                                                <div class='username-info'>
                                                <a href='?u_id=$values[user_id]'>$values[username]</a>
                                                </div>
                                            </div>
                                        </div>
                                        ";
                                    break;
                                } else if ($key == "group_name") {
                                    echo "
                                        <div class='friends-room'>
                                            <div class='username-box'>
                                                <div class='image'>
                                                    <img src='$values[group_image]' alt='Photo'>
                                                </div>
                                                <div class='username-info'>
                                                <a href='?g_id=$values[group_id]'>$values[group_name]</a>
                                                </div>
                                            </div>
                                        </div>
                                        ";
                                    break;
                                }
                            }
                        }

                        $stmt = $conn->prepare("SELECT username, user_id, image, date_time
                                                FROM users
                                                WHERE username <> ?
                                                AND (user_id IN (SELECT friend_id FROM friends WHERE user_id = ?)
                                                OR user_id IN (SELECT user_id FROM friends WHERE friend_id = ?))
                                                ORDER BY date_time DESC
                                                ");
                        $stmt->execute(array($_SESSION['username'], $_SESSION['user_id'], $_SESSION['user_id']));
                        
                        $arr = [];
                        while ($result = $stmt->fetch()) {
                            $arr[$result['date_time']] = $result;
                        }

                        $stmt = $conn->prepare("SELECT group_name, group_id, group_image, g_date_time
                                                FROM groups
                                                WHERE group_id IN(SELECT group_id FROM group_members WHERE user_id = ?)
                                                ORDER BY g_date_time DESC
                                                ");
                        $stmt->execute(array($_SESSION['user_id']));
                        
                        while ($result = $stmt->fetch()) {
                            $arr[$result['g_date_time']] = $result;
                        }

                        krsort($arr);

                        foreach ($arr as $values) {
                            foreach ($values as $key => $val) {
                                if ($key == "username") {
                                    echo "
                                        <div class='friends-room'>
                                            <div class='username-box'>
                                                <div class='image'>
                                                    <img src='$values[image]' alt='Photo'>
                                                </div>
                                                <div class='username-info'>
                                                <a href='?u_id=$values[user_id]'>$values[username]</a>
                                                </div>
                                            </div>
                                        </div>
                                        ";
                                    break;
                                } else if ($key == "group_name") {
                                    echo "
                                        <div class='friends-room'>
                                            <div class='username-box'>
                                                <div class='image'>
                                                    <img src='$values[group_image]' alt='Photo'>
                                                </div>
                                                <div class='username-info'>
                                                <a href='?g_id=$values[group_id]'>$values[group_name]</a>
                                                </div>
                                            </div>
                                        </div>
                                        ";
                                    break;
                                }
                            }
                        }

                    }
                } else {
                    $stmt = $conn->prepare("SELECT username, user_id, image, date_time
                                            FROM users
                                            WHERE username <> ?
                                            AND (user_id IN (SELECT friend_id FROM friends WHERE user_id = ?)
                                            OR user_id IN (SELECT user_id FROM friends WHERE friend_id = ?))
                                            ORDER BY date_time DESC
                                            ");
                        $stmt->execute(array($_SESSION['username'], $_SESSION['user_id'], $_SESSION['user_id']));
                    
                    $arr = [];
                    while ($result = $stmt->fetch()) {
                        $arr[$result['date_time']] = $result;
                    }

                    $stmt = $conn->prepare("SELECT group_name, group_id, group_image, g_date_time
                                            FROM groups
                                            WHERE group_id IN(SELECT group_id FROM group_members WHERE user_id = ?)
                                            ORDER BY g_date_time DESC
                                            ");
                    $stmt->execute(array($_SESSION['user_id']));
                    while ($result = $stmt->fetch()) {
                        $arr[$result['g_date_time']] = $result;
                    }

                    krsort($arr);

                    foreach ($arr as $values) {
                        foreach ($values as $key => $val) {
                            if ($key == "username") {
                                echo "
                                    <div class='friends-room'>
                                        <div class='username-box'>
                                            <div class='image'>
                                                <img src='$values[image]' alt='Photo'>
                                            </div>
                                            <div class='username-info'>
                                            <a href='?u_id=$values[user_id]'>$values[username]</a>
                                            </div>
                                        </div>
                                    </div>
                                    ";
                                break;
                            } else if ($key == "group_name") {
                                echo "
                                    <div class='friends-room'>
                                        <div class='username-box'>
                                            <div class='image'>
                                                <img src='$values[group_image]' alt='Photo'>
                                            </div>
                                            <div class='username-info'>
                                            <a href='?g_id=$values[group_id]'>$values[group_name]</a>
                                            </div>
                                        </div>
                                    </div>
                                    ";
                                break;
                            }
                        }
                    }
                }

                ?>
            </div>
            <div class="message-box">
                <div class="output-box">
                    <?php
                        //===================================================================
                        if (isset($_GET['u_id'])) {
                            $check =  check_value("user_id", "users", "user_id", $_GET['u_id']);
                            if(! empty($check)) {
                                $stmt = $conn->prepare("SELECT message_info, from_id, date_time
                                                        FROM messages
                                                        WHERE (from_id = ? AND to_id = ?)
                                                        OR (from_id = ? AND to_id = ?)
                                                        ORDER BY date_time DESC
                                                        ");
                                $stmt->execute(array($_SESSION['user_id'], $_GET['u_id'], $_GET['u_id'], $_SESSION['user_id']));
                                
                                $arr = [];
                                while ($result = $stmt->fetch()) {
                                    $arr[$result['date_time']] = $result;
                                }

                                $stmt = $conn->prepare("SELECT image_name, image_from_id, image_date_time
                                                        FROM pictures
                                                        WHERE (image_from_id = ? AND image_to_id = ?)
                                                        OR (image_from_id = ? AND image_to_id = ?)
                                                        ORDER BY image_date_time
                                                        ");
                                $stmt->execute(array($_SESSION['user_id'], $_GET['u_id'], $_GET['u_id'], $_SESSION['user_id']));
                                while ($result = $stmt->fetch()) {
                                        $arr[$result['image_date_time']] = $result;
                                    }

                                ksort($arr);
                                $last_message = end($arr)[2];

                                foreach ($arr as $value) {
                                    foreach ($value as $key => $val) {
                                        if ($key == "message_info") {
                                            if ($value['from_id'] != $_SESSION['user_id']) {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='my' $id>
                                                    <div class='my-messages'>
                                                        <p>$value[message_info]</p>
                                                    </div>
                                                </div>
                                                ";
                                            } else {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='him' $id>
                                                    <div class='him-messages'>
                                                        <p>$value[message_info]</p>
                                                    </div>
                                                </div>
                                                ";
                                            }
                                            break;
                                        } else if ($key == "image_name") {
                                            if ($value['image_from_id'] != $_SESSION['user_id']) {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='my' $id>
                                                    <div class='my-messages'>
                                                        <div class='image'>
                                                            <img src='$value[image_name]' alt='Photo'>
                                                        </div>
                                                    </div>
                                                </div>
                                                ";
                                            } else {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='him' $id>
                                                    <div class='him-messages'>
                                                        <div class='image'>
                                                            <img src='$value[image_name]' alt='Photo'>
                                                        </div>
                                                    </div>
                                                </div>
                                                ";
                                            }
                                            break;
                                        }
                                    }}
                                }
                        }
                        // if(isset($files_errors)) {
                        //     foreach ($files_errors as $error) {
                        //         echo "
                        //             <div class='my'>
                        //                 <div class='my-messages'>
                        //                     <p>$error</p>
                        //                 </div>
                        //             </div>
                        //             ";
                        //     }
                        // }
                        if (isset($_GET['g_id'])) {
                            $check =  check_value("group_id", "groups", "group_id", $_GET['g_id']);
                            if(! empty($check)) {
                                $stmt = $conn->prepare("SELECT m.message_info, m.from_id, m.date_time, u.username
                                                        FROM messages m
                                                        INNER JOIN
                                                        users u
                                                        ON  m.to_id = ? AND u.user_id = m.from_id
                                                        ORDER BY m.date_time
                                                        ");
                                $stmt->execute(array($_GET['g_id']));

                                $arr = [];
                                while ($result = $stmt->fetch()) {
                                    $arr[$result['date_time']] = $result;
                                }

                                $stmt = $conn->prepare("SELECT p.image_name, p.image_from_id, p.image_date_time, u.username
                                                        FROM pictures p INNER JOIN users u
                                                        ON p.image_to_id = ?
                                                        AND u.user_id = p.image_from_id
                                                        ORDER BY p.image_date_time
                                                        ");
                                $stmt->execute(array($_GET['g_id']));
                                while ($result = $stmt->fetch()) {
                                    $arr[$result['image_date_time']] = $result;
                                }

                                ksort($arr);
                                $last_message = end($arr)[2];

                                foreach ($arr as $value) {
                                    foreach ($value as $key => $val) {
                                        if ($key == "message_info") {
                                            if ($value['from_id'] != $_SESSION['user_id']) {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='my' $id>
                                                    <div class='my-messages'>
                                                        <a href=''>$value[username]</a>
                                                        <p>$value[message_info]</p>
                                                    </div>
                                                </div>
                                                ";
                                            } else {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='him' $id>
                                                    <div class='him-messages'>
                                                        <a href=''>$value[username]</a>
                                                        <p>$value[message_info]</p>
                                                    </div>
                                                </div>
                                                ";
                                            }
                                            break;
                                        } else if ($key == "image_name") {
                                            if ($value['image_from_id'] != $_SESSION['user_id']) {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='my' $id>
                                                    <div class='my-messages'>
                                                        <a href=''>$value[username]</a>
                                                        <img src='$value[image_name]' alt='Photo'>
                                                    </div>
                                                </div>
                                                ";
                                            } else {
                                                $id =  $last_message == $value[2] ? "id='last-message'" : '' ;
                                                echo "
                                                <div class='him' $id>
                                                    <div class='him-messages'>
                                                        <a href=''>$value[username]</a>
                                                        <img src='$value[image_name]' alt='Photo'>
                                                    </div>
                                                </div>
                                                ";
                                            }
                                            break;
                                        }
                                    }}
                                }
                        }
                    ?>

                </div>
            </div>
            <?php
                if (isset($_GET['u_id']) || isset($_GET['g_id'])) {
                    if(isset($_GET['u_id'])) {
                        $check =  check_value("user_id", "users", "user_id", $_GET['u_id']);
                    }
                    elseif(isset($_GET['g_id'])) {
                        $check =  check_value("group_id", "groups", "group_id", $_GET['g_id']);
                    }
                    if(! empty($check)) {
                        echo "
                            <div class='input-box'>
                            <form class='send-message' action='' method='post'>
                                <input type='text' name='enter_message' placeholder='Enter'>
                                <button type='submit' name='send_message'>Send</button>
                            </form>
                            <form class='send-image' action='' method='post' enctype='multipart/form-data'>
                                    <input type='file' name = 'files[]' id='file' multiple='multiple'>
                                    <label for='file' >Images</label>
                                <button type='submit' name='send_file'>Send</button>
                            </form>

                        </div>
                            ";
                    }
                }
            ?>
        </div>

    </section>
    <script>
        // Scroll to the bottom of the page
        window.onload = function() {
            window.scrollTo(0, document.body.scrollHeight);
        };

        // Scroll to the last message
        // const lastMessage = document.getElementById('last-message');
        // lastMessage.scrollIntoView({block: 'end' });
        // window.scrollTo(0, document.body.scrollHeight);

    //     const messageContainer = document.getElementById('last-message');
    //   messageContainer.scrollTop = messageContainer.scrollHeight;
    </script>
    <?php
    }
 else{
    header("location:index.php");
    exit();
 }
 ?>
</body>
</html>
<?php ob_end_flush(); // Release The Output?>