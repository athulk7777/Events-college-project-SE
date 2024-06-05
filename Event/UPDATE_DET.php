<?php
session_start();

// Check if the user is logged in
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

$updateSuccess = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated details from the form
    $regno = $_POST['regno'];
    $cname = $_POST['cname'];
    $phoneno = $_POST['phoneno'];
    $designation = $_POST['designation'];

    // Update the coordinator details in the database
    $updateQuery = "UPDATE Co_ord SET Regno = :regno, CName = :cname, PHONENO = :phoneno, Designation = :designation WHERE UserId = :userid";
    $updateStmt = oci_parse($connection, $updateQuery);
    oci_bind_by_name($updateStmt, ':regno', $regno);
    oci_bind_by_name($updateStmt, ':cname', $cname);
    oci_bind_by_name($updateStmt, ':phoneno', $phoneno);
    oci_bind_by_name($updateStmt, ':designation', $designation);
    oci_bind_by_name($updateStmt, ':userid', $userid);

    if (oci_execute($updateStmt)) {
        $updateSuccess = true;
    } else {
        $error = oci_error($updateStmt);
        $errorMsg = "Update failed: " . $error['message'];
    }
    oci_free_statement($updateStmt);
}

// Fetch coordinator details based on UserId
$query = "SELECT * FROM Co_ord WHERE UserId = :userid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$coordinatorDetails = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if ($designation === 'CO-ORD') {
    include 'header.php';
}

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Details</title>
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
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
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
        h1, p, label {
            color: #fff;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        button:hover {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Update Details</h1>
        <?php if ($updateSuccess): ?>
            <p style="color: green;">Details updated successfully!</p>
        <?php elseif ($errorMsg): ?>
            <p style="color: red;"><?php echo htmlspecialchars($errorMsg); ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="regno">Registration Number:</label>
                <input type="text" id="regno" name="regno" value="<?php echo htmlspecialchars($coordinatorDetails['REGNO']); ?>" required>
            </div>
            <div class="form-group">
                <label for="cname">Coordinator Name:</label>
                <input type="text" id="cname" name="cname" value="<?php echo htmlspecialchars($coordinatorDetails['CNAME']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phoneno">Phone Number:</label>
                <input type="text" id="phoneno" name="phoneno" value="<?php echo htmlspecialchars($coordinatorDetails['PHONENO']); ?>" required>
            </div>
            <div class="form-group">
                <label for="designation">Designation:</label>
                <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($coordinatorDetails['DESIGNATION']); ?>" required>
            </div>
            <button type="submit">Update Details</button>
        </form>
        <form method="get" action="MANAGE_ACC.php">
            <button type="submit" class="button">Back</button>
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
