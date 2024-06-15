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
    $designation = $_POST['designation'];
    $prev_pwd = $_POST['prev_pwd'];
    $userid = null;

    if ($designation === 'ADMIN' || $designation === 'VOLUNTEER') {
        $userid = $_POST['userid'];
        $query = "SELECT * FROM Co_ord WHERE UserId = :userid AND prev_pwd = :prev_pwd AND Designation = :designation";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
    } elseif ($designation === 'CO-ORD') {
        $regno = $_POST['regno'];
        $query = "SELECT * FROM Co_ord WHERE Regno = :regno AND prev_pwd = :prev_pwd AND Designation = :designation";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':regno', $regno);
    }

    oci_bind_by_name($stmt, ':prev_pwd', $prev_pwd);
    oci_bind_by_name($stmt, ':designation', $designation);
    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        // Valid credentials
        if ($designation === 'CO-ORD') {
            $userid = $row['USERID']; // Retrieve the UserID for CO-ORD
        }
        $_SESSION['userid'] = $userid;
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
            <select id="designation" name="designation" required>
                <option value="ADMIN">ADMIN</option>
                <option value="CO-ORD">CO-ORD</option>
                <option value="VOLUNTEER">VOLUNTEER</option>
            </select><br>

            <div id="userid-section">
                <label for="userid">User ID:</label>
                <input type="text" id="userid" name="userid"><br>
            </div>

            <div id="regno-section" style="display: none;">
                <label for="regno">Reg No:</label>
                <input type="text" id="regno" name="regno"><br>
            </div>

            <label for="prev_pwd">Previous Password:</label>
            <input type="password" id="prev_pwd" name="prev_pwd" required><br>

            <button type="submit">Verify</button>
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

        // Show/Hide input fields based on designation selection
        const designationSelect = document.getElementById('designation');
        const userIdSection = document.getElementById('userid-section');
        const regnoSection = document.getElementById('regno-section');

        designationSelect.addEventListener('change', function() {
            if (this.value === 'CO-ORD') {
                userIdSection.style.display = 'none';
                regnoSection.style.display = 'block';
                document.getElementById('userid').required = false;
                document.getElementById('regno').required = true;
            } else {
                userIdSection.style.display = 'block';
                regnoSection.style.display = 'none';
                document.getElementById('userid').required = true;
                document.getElementById('regno').required = false;
            }
        });
    </script>
</body>
</html>
