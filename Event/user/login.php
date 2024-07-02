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

function handleLogin($connection) {
    $email = $_POST['logemail'];
    $password = $_POST['logpass'];
    $query = "SELECT USID, UNAME, EMAIL FROM USER_DETAILS WHERE EMAIL = :email AND PWD = :password";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':password', $password);
    oci_execute($stmt);
    if ($row = oci_fetch_assoc($stmt)) {
        $_SESSION['userid'] = $row['USID'];
        $_SESSION['username'] = $row['UNAME'];
        $_SESSION['email'] = $row['EMAIL'];
        header('Location: index.php');
        exit();
    } else {
        return "Invalid email or password.";
    }
    oci_free_statement($stmt);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (isset($_POST['logemail']) && isset($_POST['logpass'])) {
        $login_error = handleLogin($connection);
    }
}

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
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
                        <h4 class="mb-4 pb-3">Log In</h4>
                        <?php if (isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
                        <form method="post" action="login.php">
                            <div class="form-group">
                                <input type="email" name="logemail" class="form-style" placeholder="Your Email" id="logemail" autocomplete="off" required>
                                <i class="input-icon uil uil-at"></i>
                            </div>
                            <div class="form-group mt-2">
                                <input type="password" name="logpass" class="form-style" placeholder="Your Password" id="logpass" autocomplete="off" required>
                                <i class="input-icon uil uil-lock-alt"></i>
                            </div>
                            <input type="hidden" name="action" value="login">
                            <button type="submit" class="btn mt-4">submit</button>
                        </form>
                        <p class="mb-0 mt-4 text-center"><a href="signup.php" class="link">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
