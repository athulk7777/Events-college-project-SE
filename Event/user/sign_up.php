<?php
session_start();
$config = include('../config.php');

$host = $config['host'];
$port = $config['port'];
$service_name = $config['service_name'];
$oracleUsername = $config['username'];
$oraclePassword = $config['password'];
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);
if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . $error['message']);
}

function handleSignUp($connection) {
    $username = $_POST['logname'];
    $email = $_POST['logemail'];
    $password = $_POST['logpass'];
    $college = $_POST['college'];
    $phoneno = $_POST['phoneno'];
    $query = "INSERT INTO USER_DETAILS (USID, UNAME, COLLEGE, PHONENO, EMAIL, PWD) VALUES (UID_SEQ.NEXTVAL, :username, :college, :phoneno, :email, :password)";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':username', $username);
    oci_bind_by_name($stmt, ':college', $college);
    oci_bind_by_name($stmt, ':phoneno', $phoneno);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':password', $password);
    if (oci_execute($stmt)) {
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        header('Location: index.php');
        exit();
    } else {
        return "Error occurred during sign-up.";
    }
    oci_free_statement($stmt);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    if (isset($_POST['logname']) && isset($_POST['logemail']) && isset($_POST['logpass']) && isset($_POST['college']) && isset($_POST['phoneno'])) {
        $signup_error = handleSignUp($connection);
    }
}

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700,800,900" rel="stylesheet">
  <style>
        /* Your CSS code here */
  </style>
</head>
<body>
    <a href="https://ptuniv.edu.in/" class="logo" target="_blank">
        <img src="ptu-logo.png" alt="">
    </a>
    <div class="section full-height">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-12 text-center py-5">
                    <div class="section pb-5 pt-5 pt-sm-2 text-center">
                        <h4 class="mb-4 pb-3">Sign Up</h4>
                        <?php if (isset($signup_error)) echo "<p style='color:red;'>$signup_error</p>"; ?>
                        <form method="post" action="signup.php">
                            <div class="form-group">
                                <input type="text" name="logname" class="form-style" placeholder="Your Full Name" id="logname" autocomplete="off" required>
                                <i class="input-icon uil uil-user"></i>
                            </div>
                            <div class="form-group mt-2">
                                <input type="email" name="logemail" class="form-style" placeholder="Your Email" id="logemail" autocomplete="off" required>
                                <i class="input-icon uil uil-at"></i>
                            </div>
                            <div class="form-group mt-2">
                                <input type="password" name="logpass" class="form-style" placeholder="Your Password" id="logpass" autocomplete="off" required>
                                <i class="input-icon uil uil-lock-alt"></i>
                            </div>
                            <div class="form-group mt-2">
                                <input type="text" name="college" class="form-style" placeholder="Your College" id="college" autocomplete="off" required>
                                <i class="input-icon uil uil-building"></i>
                            </div>
                            <div class="form-group mt-2">
                                <input type="text" name="phoneno" class="form-style" placeholder="Your Phone Number" id="phoneno" autocomplete="off" required>
                                <i class="input-icon uil uil-phone"></i>
                            </div>
                            <input type="hidden" name="action" value="signup">
                            <button type="submit" class="btn mt-4">submit</button>
                        </form>
                        <p class="mb-0 mt-4 text-center"><a href="login.php" class="link">Log In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>