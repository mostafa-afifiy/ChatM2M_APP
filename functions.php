<?php
session_start();
require "config.php";

function check_value($select, $table, $where, $value)
{
    global $conn;
    $stmt = $conn->prepare("SELECT $select FROM $table WHERE $where = ?");
    $stmt->execute(array($value));
    $result = $stmt->fetch();

    return $result;
}



function registerUser($username, $email, $password, $confirm_password)
{
    global $conn;
    $filter_username = filter_var($username, FILTER_SANITIZE_STRING);
    $filter_email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $error_array = array();

    if(empty($filter_username)){
        $error_array[] = "Username Is Required";
    }
    
    if(strlen($filter_username) <= 4 && !empty($filter_username)){
        $error_array[] = "Username Must Be Larger Than 4 Characters";
    }

    if(filter_var($filter_email, FILTER_VALIDATE_EMAIL) != true){
        $error_array[] = "Email is not valid";
    }

    if(empty($password)){
        $error_array[] = "Password Is Required";
    } 
    
    if(strlen($password) <= 4 && !empty($password)){
        $error_array[] = "Password Must Be Larger Than 4 Characters";
    }

     if(sha1($password) != sha1($confirm_password) && !empty($password)){
        $error_array[] = "Password Not Match";
    }


    if(empty($error_array)) {
        $check_user = check_value("username", "users", "username", $filter_username);
        $check_email = check_value("email", "users", "email", $filter_email);
        
        if ($check_user != NULL) {
            $error_array[] = "Username already exists, Please use a different username";
        }

        if($check_email != NULL) {
            $error_array[] = "Email already exists, Please use a different email";
        }

        if(empty($error_array)) {

            $stmt = $conn->prepare("INSERT INTO users(username, email, pass) VALUES(?, ?, ?)");
            $stmt->execute(array($filter_username, $filter_email, sha1($password)));
            $count = $stmt->rowCount();
            if($count > 0) {
                $_SESSION['username'] = $filter_username;
                header("location: info.php");
                exit();
            }
            else {
                $error_array[] = "An error occurred. Please try again";
                return $error_array;
            }
        }else return $error_array;
    }else return $error_array;
}


function info($birthday, $gender, $photo)
{
    global $conn;

    $info_error = array();
    
    if(empty($photo['name'])) {
        $info_error[] = "Photo is required";
    }
    
    $types_array = array("jpg", "png", "gif");
    $type = end(explode(".", $photo['name']));
    
    if(!in_array($type, $types_array) && !empty($photo['name'])) {
        $info_error[] = "Unknown type";
    }
    
    
    $random = rand(0,1000000000);
    $photo_name =  $random . "_" . $photo['name'];
    $photo_upload = "./upload/avatar/$photo_name";
    
    if(empty($birthday)) {
        $info_error[] = "Birthday is required";
    }
    
    if(empty($gender)) {
        $info_error[] = "Gender is required";
    }
    
    if(empty($info_error)) {
        $photo_location = $photo['tmp_name'];
        if(move_uploaded_file($photo_location, $photo_upload)) {
            $username = $_SESSION['username'];
            unset($_SESSION['username']);
            $stmt = $conn->prepare("UPDATE users SET birthday = '$birthday', gender = '$gender', image = '$photo_upload' WHERE username = '$username'");
            $stmt->execute();
             
            if ($stmt->rowCount() > 0) {
                header("location: index.php");
                    exit();
            } else {
                $info_error[] = "Error Please try again";
                return $info_error;  
                } 
        }else {
            $info_error[] = "Image not uploaded, Please try again";
            return $info_error;
        }     
        // } else {
        //     return "Image Wasn't Uploaded, Please try again!";
        // }
    } else return $info_error;
}

function loginUser($username, $password)
{
    global $conn;
    $filter_user = filter_var($username, FILTER_SANITIZE_STRING);

    $form_error = array();

    if(empty($filter_user)){
        $form_error[] = "Username Is Required";
    }

    if(strlen($filter_user) <= 4 && !empty($filter_user)){
        $form_error[] = "Username Must Be Larger Than 4 Characters";
    }
   
    if(empty($password)){
        $form_error[] = "Password Is Required";
    } 

    if(strlen($password) <= 4 && !empty($password)) {
        $form_error[] = "Password Must Be Larger Than 4 Characters";
    }

    if(empty($form_error)) {
        $result = check_value("user_id, pass", "users", "username", $filter_user);
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        

        if (empty($result)) {
            $form_error[] = "Wrong Username or Password";
            return $form_error;
        }
        else {
            if($result['pass'] != sha1($password)){
                $form_error[] = "Wrong Password!";
                return $form_error;
            }
            else{
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $filter_user;
                // echo "i am here";
                // print_r($_SESSION);
                header("location: call_me.php");
                exit();
            }
        }
    }else return $form_error;
}

function logoutUser()
{
    session_unset();
    session_destroy();
    header("location: index.php");
    exit();
}


function get_user_name($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT username, date_time, image FROM users WHERE  user_id = ?");
    $stmt->execute(array($id));
    $data = $stmt->fetch();
    return $data;
}

function get_group_name($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT group_name, g_date_time, group_image FROM groups WHERE  group_id = ?");
    $stmt->execute(array($id));
    $data = $stmt->fetch();
    return $data;
}

function add_new_group($group_name, $photo, $friends_array)
{
    global $conn;
    $filter_group_name = filter_var($group_name, FILTER_SANITIZE_STRING);
    $group_error = array();

    if(empty($photo['name'])) {
        $group_error[] = "Image Is Required";
    }

    if(empty($filter_group_name)) {
        $group_error[] = "Group Name Is Required";
    }
    if(strlen($filter_group_name) < 4 && !empty($filter_group_name)) {
        $group_error[] = "Group Name Must Larger Than 4 Character";
    }


    if(empty($friends_array)) {
        $group_error[] = "You Must Add At Least One Friend to Group";
    }

    if(empty($group_error)){
       
        if(isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("SELECT g.group_name 
                                    FROM groups g 
                                    INNER JOIN group_members gm 
                                    ON g.group_id = gm.group_id 
                                    WHERE gm.user_id 
                                    IN (SELECT user_id FROM group_members WHERE user_id = ?);
                                    ");
            $stmt->execute(array($_SESSION['user_id']));
            $array_group_names = array();
            // echo "<pre>";
            // print_r($data);
            // echo "</pre>";
            while ($data = $stmt->fetch()){
                $array_group_names[] = $data['group_name'];
            }
           
            // $group_error[] = " $data";
            // return $group_error;
            if(! in_array($filter_group_name, $array_group_names)) {

               
                $location = $photo['tmp_name'];

                $photo_name = $photo['name'];

                $array_of_type_images = array("jpg", "png", "gif");

                $type_image = end(explode('.', $photo_name));

                if(in_array($type_image, $array_of_type_images)){

                    $random = rand(1, 100000000);
                    $photo_new_name = $random . "_" . $photo_name;
                    $photo_upload = "./upload/avatar/$photo_new_name";

                    if(move_uploaded_file($location, $photo_upload)) {

                        $stmt = $conn->prepare("INSERT INTO groups(group_name, user_id, group_image) VALUES(?, ?, ?)");
                        $stmt->execute(array($filter_group_name, $_SESSION['user_id'], $photo_upload));


                        if ($stmt->rowCount() > 0) {
                            $stmt = $conn->prepare("SELECT group_id FROM groups WHERE group_name = ?");
                            $stmt->execute(array($filter_group_name));
                            $data = $stmt->fetch();
                            if ($data != NULL) {
                                $_SESSION["new_group_id"] = $data['group_id'];


                                foreach($friends_array as $friend) {
                                    $stmt = $conn->prepare("INSERT INTO group_members(group_id, user_id) VALUES(?,?)");
                                    $stmt->execute(array($data['group_id'], $friend));
                                }
                                $stmt = $conn->prepare("INSERT INTO group_members(group_id, user_id) VALUES(?,?)");
                                $stmt->execute(array($data['group_id'], $_SESSION['user_id']));
                                if ($stmt->rowCount() > 0) {
                                    header("location: call_me.php");
                                    exit();
                                }
                            }else {
                                $group_error[] = "Error, Please Try Again!";
                                return $group_error;
                            }
                        }else {
                            $group_error[] = "Group Wasn't Added";
                            return $group_error;
                        }
                    }else {
                        $group_error[] = "Image Wasn't Upload, Please try again";
                        return $group_error;
                    }
                }else {
                    $group_error[] = "image extension not allow";
                    return $group_error;
                }

            }else {
                $group_error[] = "Group name is already exist";
                return $group_error;
            }
        }
    }else return $group_error;
}