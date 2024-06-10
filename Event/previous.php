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

$host = htmlspecialchars($config['host']);
$port = htmlspecialchars($config['port']);
$service_name = htmlspecialchars($config['service_name']);
$oracleUsername = htmlspecialchars($config['username']);
$oraclePassword = htmlspecialchars($config['password']);

// Connection string
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

// Establish connection
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . htmlspecialchars($error['message']));
}

$prevPwd = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regno = htmlspecialchars($_POST['regno']);

    if ($designation === 'ADMIN') {
        // Admin case
        $query = "SELECT Prev_pwd FROM Co_ord WHERE Designation = 'CO-ORD' AND Regno = :regno";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':regno', $regno);
    } else if ($designation === 'CO-ORD') {
        // Co-ord case
        $query = "SELECT Eid FROM Co_ord WHERE UserId = :userid";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_execute($stmt);

        $eid = null;
        if ($row = oci_fetch_assoc($stmt)) {
            $eid = $row['EID'];
        }

        oci_free_statement($stmt);

        if ($eid !== null) {
            $query = "SELECT Prev_pwd FROM Co_ord WHERE Eid = :eid AND Regno = :regno";
            $stmt = oci_parse($connection, $query);
            oci_bind_by_name($stmt, ':eid', $eid);
            oci_bind_by_name($stmt, ':regno', $regno);
        }
    }

    if (isset($stmt)) {
        oci_execute($stmt);

        if ($row = oci_fetch_assoc($stmt)) {
            $prevPwd = $row['PREV_PWD'];
        } else {
            echo "<p style='color:red;'>No previous password found for the given registration number.</p>";
        }

        oci_free_statement($stmt);
    }
    
}

if ($designation === 'CO-ORD') {
    include 'header.php';
}
if ($designation === 'ADMIN') {
    include 'ADD_HEADER.php';
}


oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Previous Password</title>
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
            background-color: #000;
        }
        .form-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            z-index: 1;
            margin: auto;
            margin-top: 100px;
            display: flex;
            flex-direction: row;
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
        .prev-password {
            color: #fff;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="form-container">
        <div>
            <h2>Display Previous Password</h2>
            <form method="post">
                <input type="text" name="regno" placeholder="Enter Registration Number" required>
                <button type="submit">Submit</button>
            </form>
        </div>
        <?php if ($prevPwd): ?>
            <div class="prev-password">
                <p>Previous Password: <?php echo htmlspecialchars($prevPwd); ?></p>
            </div>
        <?php endif; ?>
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
