<?php
include("functions.php");

if(isset($_SESSION['username'])) {
    header("location: call_me.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $all_errors = loginUser($_POST['username'], $_POST['password']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
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
                        foreach($all_errors as $key => $error) {
                        echo "<p class='prgph error'> $error</p>";
                        }
                    ?>
            </div>
            <form action="<?php $_SERVER['PHP_SELF'];?>" method = "POST" >
                <h2>Login</h2>
                <div class="input-box">
                    <label for="">Username</label>
                    <input type="text" name = "username" autocomplete="off" required>
                </div>
                <div class="input-box">
                    <label for="">Password</label>
                    <input type="password" name = "password" autocomplete="new-password" required>
                </div>
                <button type="submit" name = "submit">Login</button>
                <div class="register-link">
                    <p>Don't have an account?</p>
                    <a href="register.php">Register</a>
                </div>
            </form>
        </div>
    </div>
</section>

</body>

</html>