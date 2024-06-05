<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['userid']) || $_SESSION['designation'] !== 'ADMIN') {
    header('Location: CO-ORD_LOGIN.php'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];

// Include the configuration file
$config = include('config.php');

// Database connection details
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

// Get event names for the dropdown, excluding those already assigned to a coordinator
$event_query = "SELECT EID, ENAME FROM EVENTS WHERE EID NOT IN (SELECT EID FROM CO_ORD WHERE DESIGNATION='CO-ORD')";
$event_stmt = oci_parse($connection, $event_query);
oci_execute($event_stmt);

$events = [];
while ($row = oci_fetch_assoc($event_stmt)) {
    $events[] = $row;
}
oci_free_statement($event_stmt);

// Handle form submission for adding a new coordinator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regno']) && isset($_POST['eid'])) {
    $regno = $_POST['regno'];
    $eid = $_POST['eid'];
    $cid = time(); // Use current timestamp as Cid for uniqueness
    $password = bin2hex(random_bytes(4)); // Generate a random password (8 characters long)

    // Insert new coordinator
    $insert_query = "INSERT INTO Co_ord (Eid, Regno, Designation, UserId, Pwd, Prev_Pwd) 
                     VALUES (:eid, :regno, 'CO-ORD', :regno, :pwd, :prev_pwd)";
    $insert_stmt = oci_parse($connection, $insert_query);
    oci_bind_by_name($insert_stmt, ':eid', $eid);
    oci_bind_by_name($insert_stmt, ':regno', $regno);
    oci_bind_by_name($insert_stmt, ':pwd', $password);
    oci_bind_by_name($insert_stmt, ':prev_pwd', $password);

    if (oci_execute($insert_stmt)) {
        echo "<p style='color: green;'>Coordinator added successfully. Password: $password</p>";
    } else {
        $error = oci_error($insert_stmt);
        echo "<p style='color: red;'>Error adding coordinator: " . $error['message'] . "</p>";
    }

    oci_free_statement($insert_stmt);
}

// Handle deletion of a coordinator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_regno'])) {
    $delete_regno = $_POST['delete_regno'];

    // Delete coordinator
    $delete_query = "DELETE FROM Co_ord WHERE Regno = :regno AND Designation = 'CO-ORD'";
    $delete_stmt = oci_parse($connection, $delete_query);
    oci_bind_by_name($delete_stmt, ':regno', $delete_regno);

    if (oci_execute($delete_stmt)) {
        echo "<p style='color: green;'>Coordinator removed successfully.</p>";
    } else {
        $error = oci_error($delete_stmt);
        echo "<p style='color: red;'>Error removing coordinator: " . $error['message'] . "</p>";
    }

    oci_free_statement($delete_stmt);
}

// Retrieve coordinator details
$co_ord_query = "SELECT Regno, CName, Eid FROM Co_ord WHERE Designation='CO-ORD'";
$co_ord_stmt = oci_parse($connection, $co_ord_query);
oci_execute($co_ord_stmt);

$co_ords = [];
while ($row = oci_fetch_assoc($co_ord_stmt)) {
    $co_ords[] = $row;
}
oci_free_statement($co_ord_stmt);

// Fetch all event names to map EID to ENAME
$all_events_query = "SELECT EID, ENAME FROM EVENTS";
$all_events_stmt = oci_parse($connection, $all_events_query);
oci_execute($all_events_stmt);

$all_events = [];
while ($row = oci_fetch_assoc($all_events_stmt)) {
    $all_events[$row['EID']] = $row['ENAME'];
}
oci_free_statement($all_events_stmt);

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Coordinators</title>
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
        .co-form, .co-list {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
            margin-bottom: 20px;
        }
        .co-form label, .co-list label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        .co-form input, .co-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .co-form button, .co-list button {
            padding: 10px 20px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .co-form button:hover, .co-list button:hover {
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
        <h1>Manage Coordinators</h1>

        <div class="co-form">
            <h2>Add Coordinator</h2>
            <form method="post" action="">
                <label for="eid">Event Name:</label>
                <select id="eid" name="eid" required>
                    <?php foreach ($events as $event) { ?>
                        <option value="<?php echo htmlspecialchars($event['EID']); ?>">
                            <?php echo htmlspecialchars($event['ENAME']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="regno">Registration Number:</label>
                <input type="text" id="regno" name="regno" required>

                <button type="submit">Add Coordinator</button>
            </form>
        </div>

        <div class="co-list">
            <h2>Coordinator List</h2>
            <?php if (!empty($co_ords)) { ?>
                <ul>
                    <?php foreach ($co_ords as $co_ord) { ?>
                        <li>
                            <label>Registration Number: <?php echo htmlspecialchars($co_ord['REGNO']); ?></label>
                            <label>Name: <?php echo htmlspecialchars($co_ord['CNAME']); ?></label>
                            <label>Event Name: <?php echo htmlspecialchars($all_events[$co_ord['EID']]); ?></label>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_regno" value="<?php echo htmlspecialchars($co_ord['REGNO']); ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No coordinators found.</p>
            <?php } ?>
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
