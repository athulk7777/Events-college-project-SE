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
    die("Connection failed: " . htmlspecialchars($error['message']));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designation = $_POST['designation'];
    $prev_pwd = $_POST['prev_pwd'];

    if ($designation === 'CO-ORD') {
        $regid = $_POST['regid'];
        $query = "SELECT UserId FROM Co_ord WHERE Regno = :regid AND prev_pwd = :prev_pwd AND Designation = 'CO-ORD'";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':regid', $regid);
        oci_bind_by_name($stmt, ':prev_pwd', $prev_pwd);

    } elseif ($designation === 'ADMIN') {
        $userid = $_POST['userid'];
        $query = "SELECT UserId FROM Co_ord WHERE UserId = :userid AND prev_pwd = :prev_pwd AND Designation = 'ADMIN'";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_bind_by_name($stmt, ':prev_pwd', $prev_pwd);

    } elseif ($designation === 'VOLUNTEER') {
        $userid = $_POST['userid'];
        $query = "SELECT UserId FROM Co_ord WHERE UserId = :userid AND prev_pwd = :prev_pwd AND Designation = 'VOLUNTEER'";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_bind_by_name($stmt, ':prev_pwd', $prev_pwd);
    }

    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        // Valid credentials
        $_SESSION['userid'] = $row['USERID'];
        $_SESSION['designation'] = $designation;
        header('Location: change_acc.php');
        exit();
    } else {
        // Invalid credentials
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
    <title>Forgot Password</title>
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
        .forgot-password-container {
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
        .forgot-password-container h2 {
            color: #ffffff;
            margin-bottom: 15px;
            font-size: 30px;
        }
        .forgot-password-container label {
            color: #ffffff;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }
        .forgot-password-container input, .forgot-password-container select, .forgot-password-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .forgot-password-container button {
            background-color: white;
            color: #000;
            margin-top: 20px;
            font-size: 15px;
            border: none;
            cursor: pointer;
        }
        .forgot-password-container button:hover {
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
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <form method="post" action="">
            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required onchange="toggleFields()">
                <option value="ADMIN">ADMIN</option>
                <option value="CO-ORD">CO-ORD</option>
                <option value="VOLUNTEER">VOLUNTEER</option>
            </select><br>

            <div id="admin-fields" style="display:none;">
                <label for="userid">User ID:</label>
                <input type="text" id="userid" name="userid"><br>

                <label for="prev_pwd">Previous Password:</label>
                <input type="password" id="prev_pwd" name="prev_pwd"><br>
            </div>

            <div id="coord-fields" style="display:none;">
                <label for="regid">Registration ID:</label>
                <input type="text" id="regid" name="regid"><br>

                <label for="prev_pwd_coord">Previous Password:</label>
                <input type="password" id="prev_pwd_coord" name="prev_pwd"><br>
            </div>

            <div id="volunteer-fields" style="display:none;">
                <label for="userid_volunteer">User ID:</label>
                <input type="text" id="userid_volunteer" name="userid"><br>

                <label for="prev_pwd_volunteer">Previous Password:</label>
                <input type="password" id="prev_pwd_volunteer" name="prev_pwd"><br>
            </div>

            <button type="submit">Verify</button>
        </form>
    </div>

    <script>
        function toggleFields() {
            var designation = document.getElementById('designation').value;
            var adminFields = document.getElementById('admin-fields');
            var coordFields = document.getElementById('coord-fields');
            var volunteerFields = document.getElementById('volunteer-fields');

            if (designation === 'ADMIN') {
                adminFields.style.display = 'block';
                coordFields.style.display = 'none';
                volunteerFields.style.display = 'none';
            } else if (designation === 'CO-ORD') {
                adminFields.style.display = 'none';
                coordFields.style.display = 'block';
                volunteerFields.style.display = 'none';
            } else if (designation === 'VOLUNTEER') {
                adminFields.style.display = 'none';
                coordFields.style.display = 'none';
                volunteerFields.style.display = 'block';
            }
        }

        // Call the function initially to set the correct fields visibility
        toggleFields();
    </script>

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
