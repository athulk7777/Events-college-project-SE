<?php
session_start();

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
    die("Connection failed: " . $error['message']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_POST['userid'];
    $pwd = $_POST['pwd'];
    $designation = $_POST['designation'];

    // Query to check user credentials
    $query = "SELECT * FROM Co_ord WHERE UserId = :userid AND Pwd = :pwd AND Designation = :designation";
    $stmt = oci_parse($connection, $query);

    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':pwd', $pwd);
    oci_bind_by_name($stmt, ':designation', $designation);

    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        // Valid login
        $_SESSION['userid'] = $userid;
        $_SESSION['designation'] = $designation;

        // Redirect based on designation
        if ($designation === 'ADMIN') {
            header('Location: adminpanel.php');
        } else {
            header('Location: co_ord_main.php');
        }
        exit();
    } else {
        // Invalid login
        echo "<p style='color:red;'>Invalid credentials. Please try again.</p>";
    }

    oci_free_statement($stmt);
}

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: hidden;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #000; /* Ensure background color for visibility */
        }
        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #000;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }
        .login-container h2 {
            color: #ffffff;
            margin-bottom: 15px;
            font-size: 30px;
        }
        .login-container label {
            color: #ffffff;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }
        .login-container input, .login-container select, .login-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            background-color: white;
            color: #000;
            margin-top: 20px;
            font-size: 15px;
            border: none;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #000;
            color: white;
            font-size: 20px;
            transition-duration: 0.5s;
            box-shadow: 0 4px 8px 0 white, 0 6px 20px 0 white;   
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post" action="">
            <label for="userid">User ID:</label>
            <input type="text" id="userid" name="userid" required><br>

            <label for="pwd">Password:</label>
            <input type="password" id="pwd" name="pwd" required><br>

            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required>
                <option value="ADMIN">ADMIN</option>
                <option value="CO-ORD">CO-ORD</option>
                <option value="VOLUNTEER">VOLUNTEER</option>
            </select><br>

            <button type="submit">Login</button>
        </form>
    </div>

    <script src="js/particles.min.js"></script>
    <script>
        console.log("Particles.js loaded");
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
</body>
</html>
