<?php
require("functions.php");

if(isset($_SESSION['username'])) {
    header("location: call_me.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
   $all_errors = registerUser($_POST["username"], $_POST["email"], $_POST["password"], $_POST["confirm_password"]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <!-- Reander All Elemants Norlamlly -->
  <link rel="stylesheet" href="CSS/normalize.css">
  <!-- Font Awesome Library -->
  <link rel="stylesheet" href="CSS/all.min.css">
  <!-- Main Template CSS File -->
  <link rel="stylesheet" href="./CSS/login.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<section>
    <div class="container">
        <div class="login-box">
            <div class="error-message">
            <?php 
                        foreach($all_errors as $error) {
                        echo "<p class='prgph error'> $error</p>";
                        }
                    ?>
            </div>
            <form action ="<?php  echo $_SERVER['PHP_SELF']; ?>" method = "POST" >
                <h2>Register</h2>
                <div class="input-box">
                    <label for="user">Username</label>
                    <input type="text" name= "username" autocomplete="off" required value = "<?php echo @$_POST["username"];?>">
                </div>
                <div class="input-box">
                <label for="mail">Email</label>
                <input type="email" name= "email" autocomplete="off" required value = "<?php echo @$_POST["email"];?>">
                </div>
                <div class="input-box">
                    <label for="pass">Password</label>
                    <input type="password" name= "password" autocomplete="new-password" required>
                </div> 
                <div class="input-box">
                    <label for="pass">Confirm Password</label>
                    <input type="password" name= "confirm_password" autocomplete="new-password">
                </div>
            
                <button type="submit" name = "submit">Register</button>
                <div class="register-link">
                    <p>Already I Have An Account!</p>
                    <a href="index.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</section>

</body>

</html>