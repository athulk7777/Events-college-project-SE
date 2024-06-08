<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid'])) {
    header('Location: CO-ORD_LOGIN.PHP'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];
$designation = $_SESSION['designation'];

$config = include('config.php');

$host = $config['host'];
$port = $config['port'];
$service_name = $config['service_name'];
$oracleUsername = $config['username'];
$oraclePassword = $config['password'];

// Connection string
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

// Establish connection
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . htmlspecialchars($error['message']));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newUserId = $_POST['new_userid'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if the current password is correct
    $query = "SELECT Pwd FROM Co_ord WHERE UserId = :userid";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);

    $row = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);

    if ($row && $row['PWD'] === $currentPassword) {
        if ($newPassword === $confirmPassword) {
            // Update the user ID and password
            $query = "UPDATE Co_ord SET UserId = :new_userid, Pwd = :new_password WHERE UserId = :userid";
            $stmt = oci_parse($connection, $query);
            oci_bind_by_name($stmt, ':new_userid', $newUserId);
            oci_bind_by_name($stmt, ':new_password', $newPassword);
            oci_bind_by_name($stmt, ':userid', $userid);
            oci_execute($stmt);

            if (oci_num_rows($stmt) > 0) {
                $_SESSION['userid'] = $newUserId; // Update session with new user ID
                echo "User ID and password updated successfully.";
            } else {
                echo "Failed to update User ID and password.";
            }
            oci_free_statement($stmt);
        } else {
            echo "New password and confirm password do not match.";
        }
    } else {
        echo "Current password is incorrect.";
    }
}

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change UserID and Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: auto;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #000; /* Ensure background color for visibility */
        }
        .form-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            z-index: 1;
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: #fff;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container form input {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #222;
            color: #fff;
        }
        .form-container form button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        .form-container form button:hover {
            background-color: #000;
            color: #fff;
        }
        .user-id {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #fff;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?php  
    if ($designation === 'CO-ORD') {
        include 'header.php';
    } elseif ($designation === 'ADMIN') {
        include 'ADD_HEADER.php';
    }
    ?>
    <div class="form-container">
        <h2>Change UserID and Password</h2>
        <form method="post">
            <input type="text" name="new_userid" value="<?php echo htmlspecialchars($userid); ?>" placeholder="New UserID" required>
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Update</button>
        </form>
    </div>

    <script src="js/particles.min.js"></script>
    <script>
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#ffffff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": false
                },
                "size": {
                    "value": 3,
                    "random": true
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 6,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 400,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },
                    "push": {
                        "particles_nb": 4
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
