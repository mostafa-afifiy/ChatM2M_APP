<?php
session_start();
include("functions.php");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $all_errors = info($_POST['birthday'], $_POST['gender'], $_FILES['photo']);
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
                    foreach($all_errors as $error) {
                    echo "<p class='prgph error'> $error</p>";
                    }
                ?>
            </div>
            <form action="<?php $_SERVER['PHP_SELF'];?>" method = "POST" enctype="multipart/form-data">
                <h2>Your Information</h2>
                <div class="add-image">
                    <div class="image">
                        <img src='' alt=''>
                    </div>
                    <input type="file" name = "photo" id="file">
                    <label for="file">Add Photo</label>
                </div>

                <div class="input-box">
                    <label for="">Birthday</label>
                    <input type="date" name = "birthday">
                </div>
                <select name="gender">
                        <option value="" style="display:none;">Gender</option >
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>

                <button type="submit" name = "submit">Save</button>
                <div class="register-link">
                    <a href="register.php">Back</a>
                </div>
            </form>
        </div>
    </div>
</section>
</body>

</html>