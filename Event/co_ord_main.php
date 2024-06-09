<?php
session_start();

if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
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
    die("Connection failed: " . $error['message']);
}

// Handle logout request
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: CO-ORD_LOGIN.PHP');
    exit();
}

if ($designation === 'CO-ORD') {
    include 'header.php';
}
// Additional database interactions can be added here

oci_close($connection); // Close the database connection when done
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Main Page</title>
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
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
        }
        .button-container {
            margin-top: 20px;
        }
        .button {
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
        .button:hover {
            background-color: #000;
            color: #fff;
        }
        h1, p {
            color: #fff;
        }
        .logout-form {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($userid); ?></h1>
        <p>Your designation: <?php echo htmlspecialchars($designation); ?></p>

        <div class="button-container">
            <?php if ($designation === 'CO-ORD') { ?>
                <a href="CO_ORD_EVENT.php" class="button">Manage Event</a>
                <a href="MANAGE_VOL.php" class="button">Manage Volunteer</a>
                <a href="ADD_EXPENSE.php" class="button">Add Expense</a>
                <a href="CHECK_PROFIT.php" class="button">Check Profit</a>
                <a href="ONSPOT_REG.php" class="button">On-Spot Registration</a>
                <a href="CO_SPON.php" class="button">Add Sponsors</a>
                <a href="ADD_FILTERS.php" class="button">Add Search Filters</a>
            <?php } ?>
            <a href="CHECK_REG.php" class="button">Check Registration</a>
        </div>

        <form method="post" class="logout-form">
            <button type="submit" name="logout" class="button">Logout</button>
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
