<?php

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

// Fetch events from the database
$eventsQuery = "SELECT Eid, Ename FROM Events";
$eventsStmt = oci_parse($connection, $eventsQuery);

if (!oci_execute($eventsStmt)) {
    $error = oci_error($eventsStmt);
    die("Query failed: " . $error['message']);
}

$events = [];
while ($row = oci_fetch_assoc($eventsStmt)) {
    $events[] = $row;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $regno = $_POST['regno'];
    $cname = $_POST['cname'];
    $userid = $_POST['userid'];
    $pwd = $_POST['pwd'];
    $phoneno = $_POST['phoneno'];
    $Eid = $_POST['Eid'];

    // Insert data into the database
    $insertQuery = "INSERT INTO Volunteer (Regno, Cname, UserId, Pwd, PHONENO, Eid) VALUES (:regno, :cname, :userid, :pwd, :phoneno, :eid)";
    $insertStmt = oci_parse($connection, $insertQuery);
    oci_bind_by_name($insertStmt, ':regno', $regno);
    oci_bind_by_name($insertStmt, ':cname', $cname);
    oci_bind_by_name($insertStmt, ':userid', $userid);
    oci_bind_by_name($insertStmt, ':pwd', $pwd);
    oci_bind_by_name($insertStmt, ':phoneno', $phoneno);
    oci_bind_by_name($insertStmt, ':eid', $Eid);

    if (!oci_execute($insertStmt)) {
        $error = oci_error($insertStmt);
        die("Insertion failed: " . $error['message']);
    }

    oci_free_statement($insertStmt);
}

oci_free_statement($eventsStmt);
oci_close($connection); // Close the database connection when done
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
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
        .form-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .form-section {
            margin-right: 20px;
        }
        .events-section {
            width: 300px;
            background-color: #222;
            padding: 20px;
            border-radius: 5px;
            color: #fff;
            text-align: left;
        }
        .button, button {
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
        .button:hover, button:hover {
            background-color: #000;
            color: #fff;
        }
        h1, h2, p, label {
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
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        /* Table Styles */
        .events-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .events-section th,
        .events-section td {
            padding: 8px;
            border: 1px solid #fff;
            text-align: left;
        }

        .events-section th {
            background-color: #333;
            color: #fff;
        }

        .events-section tr:nth-child(even) {
            background-color: #444;
        }

        .events-section tr:nth-child(odd) {
            background-color: #555;
        }

        .events-section tr:hover {
            background-color: #666;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Volunteer Registration</h1>
        <div class="form-container">
            <div class="form-section">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="regno">Registration Number:</label>
                        <input type="text" id="regno" name="regno" required>
                    </div>
                    <div class="form-group">
                        <label for="cname">Full Name:</label>
                        <input type="text" id="cname" name="cname" required>
                    </div>
                    <div class="form-group">
                        <label for="userid">User ID:</label>
                        <input type="text" id="userid" name="userid" required>
                    </div>
                    <div class="form-group">
                        <label for="pwd">Password:</label>
                        <input type="text" id="pwd" name="pwd" required>
                    </div>
                    <div class="form-group">
                        <label for="phoneno">Phone Number:</label>
                        <input type="text" id="phoneno" name="phoneno" required>
                    </div>
                    <div class="form-group">
                        <label for="Eid">Event id:</label>
                        <input type="number" id="Eid" name="Eid" required>
                    </div>
                    <button type="submit">Register</button>
                </form>
            </div>
            <div class="events-section">
                <h2>Available Events</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['EID']); ?></td>
                                <td><?php echo htmlspecialchars($event['ENAME']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include particles.js script file manually here -->
    <!-- <script src="js/particles.min.js"></script> -->
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
</body>
</html>
