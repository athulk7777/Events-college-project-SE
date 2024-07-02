<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: CO-ORD_LOGIN.php'); 
    exit();
}

$userid = $_SESSION['userid'];
$designation = $_SESSION['designation'];

// Only include header for CO-ORD designation
if ($designation === 'CO-ORD') {
    include 'header.php';
}

// Database connection details
$host = 'localhost';
$port = '1521';
$service_name = 'orcl';
$oracleUsername = 'system';
$oraclePassword = '15461546';

// Connection string
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

// Establish connection
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . htmlspecialchars($error['message']));
}

// Get EID from Co_ord table based on UserId
$query = "SELECT Eid FROM Co_ord WHERE UserId = :userid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$eid = null;
if ($row = oci_fetch_assoc($stmt)) {
    $eid = $row['EID'];
}
oci_free_statement($stmt);

if (!$eid) {
    die("Event ID not found for the given user.");
}

// Handle form submission for adding event description
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_description'])) {
    $event_description = $_POST['event_description'];

    // Insert event description into EVENT_DESCRIP table
    $insert_query = "INSERT INTO EVENT_DESCRIP (EID, DESCRIP) VALUES (:eid, :event_description)";
    $insert_stmt = oci_parse($connection, $insert_query);
    oci_bind_by_name($insert_stmt, ':eid', $eid);
    oci_bind_by_name($insert_stmt, ':event_description', $event_description);

    if (oci_execute($insert_stmt)) {
        echo "<p style='color: green;'>Event description added successfully.</p>";
    } else {
        $error = oci_error($insert_stmt);
        echo "<p style='color: red;'>Error adding event description: " . htmlspecialchars($error['message']) . "</p>";
    }

    oci_free_statement($insert_stmt);
}

// Handle form submission for adding prizes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_prize = $_POST['first_prize'] !== '' ? $_POST['first_prize'] : null;
    $second_prize = $_POST['second_prize'] !== '' ? $_POST['second_prize'] : null;
    $third_prize = $_POST['third_prize'] !== '' ? $_POST['third_prize'] : null;

    // Insert prizes into PRIZE table
    $insert_query = "INSERT INTO PRIZE (EID, F_PRIZE, S_PRIZE, T_PRIZE) VALUES (:eid, :first_prize, :second_prize, :third_prize)";
    $insert_stmt = oci_parse($connection, $insert_query);
    oci_bind_by_name($insert_stmt, ':eid', $eid);
    oci_bind_by_name($insert_stmt, ':first_prize', $first_prize);
    oci_bind_by_name($insert_stmt, ':second_prize', $second_prize);
    oci_bind_by_name($insert_stmt, ':third_prize', $third_prize);

    if (oci_execute($insert_stmt)) {
        echo "<p style='color: green;'>Prizes added successfully.</p>";
    } else {
        $error = oci_error($insert_stmt);
        echo "<p style='color: red;'>Error adding prizes: " . htmlspecialchars($error['message']) . "</p>";
    }

    oci_free_statement($insert_stmt);
}



oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Description and Prizes</title>
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
        .description-form, .prizes-form {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
            margin-bottom: 20px;
        }
        .description-form label, .prizes-form label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        .description-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .description-form button, .prizes-form button {
            padding: 10px 20px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .description-form button:hover, .prizes-form button:hover {
            background-color: #000;
            color: #fff;
        }
        h1, h2, p {
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Add Description and Prizes</h1>

        <div class="description-form">
            <h2>Add Event Description</h2>
            <form method="post" action="">
                <label for="event_description">Event Description:</label>
                <textarea id="event_description" name="event_description" rows="4" required></textarea>
                <button type="submit">Add Description</button>
            </form>
        </div>

        <div class="prizes-form">
            <h2>Add Prizes</h2>
            <form method="post" action="">
                <label for="first_prize">First Prize Amount:</label>
                <input type="number" id="first_prize" name="first_prize">
                <label for="second_prize">Second Prize Amount:</label>
                <input type="number" id="second_prize" name="second_prize">
                <label for="third_prize">Third Prize Amount:</label>
                <input type="number" id="third_prize" name="third_prize">
                <button type="submit">Add Prizes</button>
            </form>
        </div>
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
