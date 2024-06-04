<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];
$designation = $_SESSION['designation'];

// Database connection details
$host = 'localhost';
$port = '1521';
$service_name = 'flight';
$oracleUsername = 'system';
$oraclePassword = 'abhinav2';

// Connection string
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

// Establish connection
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . $error['message']);
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

// Handle form submission for updating event details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ename = $_POST['ename'];
    $entry_fees = $_POST['entry_fees'];
    $etype = $_POST['etype'];
    $elocation = $_POST['elocation'];
    $edate = $_POST['edate'];
    $etime = $_POST['etime'];
    $slots = $_POST['slots'];
    $total_members = $_POST['total_members'];

    // Update event details
    $update_query = "UPDATE Events SET 
        Ename = :ename,
        Entry_fees = :entry_fees,
        EType = :etype,
        ELocation = :elocation,
        EDate = :edate,
        ETime = :etime,
        SLOTS = :slots,
        TOTAL_MEMBERS = :total_members
        WHERE Eid = :eid";

    $update_stmt = oci_parse($connection, $update_query);
    oci_bind_by_name($update_stmt, ':ename', $ename);
    oci_bind_by_name($update_stmt, ':entry_fees', $entry_fees);
    oci_bind_by_name($update_stmt, ':etype', $etype);
    oci_bind_by_name($update_stmt, ':elocation', $elocation);
    oci_bind_by_name($update_stmt, ':edate', $edate);
    oci_bind_by_name($update_stmt, ':etime', $etime);
    oci_bind_by_name($update_stmt, ':slots', $slots);
    oci_bind_by_name($update_stmt, ':total_members', $total_members);
    oci_bind_by_name($update_stmt, ':eid', $eid);

    if (oci_execute($update_stmt)) {
        echo "<p style='color: green;'>Event updated successfully.</p>";
    } else {
        $error = oci_error($update_stmt);
        echo "<p style='color: red;'>Error updating event: " . $error['message'] . "</p>";
    }

    oci_free_statement($update_stmt);
}

// Retrieve event details
$query = "SELECT * FROM Events WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$event = oci_fetch_assoc($stmt);
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
    <title>Manage Event</title>
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
            padding-bottom: 0px;
        }
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
        }
        .event-form {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
        }
        .event-form label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        .event-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 0px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .event-form button {
            padding: 10px 20px;
            font-size: 16px;
            margin-top: 5px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .event-form button:hover {
            background-color: #000;
            color: #fff;
        }
        h1, p {
            margin-top: 0px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Manage Event</h1>
        <?php if ($event) { ?>
            <form method="post" action="" class="event-form">
                <label for="ename">Event Name:</label>
                <input type="text" id="ename" name="ename" value="<?php echo htmlspecialchars($event['ENAME']); ?>" required>

                <label for="entry_fees">Entry Fees:</label>
                <input type="number" id="entry_fees" name="entry_fees" value="<?php echo htmlspecialchars($event['ENTRY_FEES']); ?>" required>

                <label for="etype">Event Type:</label>
                <input type="text" id="etype" name="etype" value="<?php echo htmlspecialchars($event['ETYPE']); ?>" required>

                <label for="elocation">Event Location:</label>
                <input type="text" id="elocation" name="elocation" value="<?php echo htmlspecialchars($event['ELOCATION']); ?>" required>

                <label for="edate">Event Date:</label>
                <input type="text" id="edate" name="edate" value="<?php echo htmlspecialchars($event['EDATE']); ?>" required>

                <label for="etime">Event Time:</label>
                <input type="text" id="etime" name="etime" value="<?php echo htmlspecialchars($event['ETIME']); ?>" required>

                <label for="slots">Slots:</label>
                <input type="number" id="slots" name="slots" value="<?php echo htmlspecialchars($event['SLOTS']); ?>" required>

                <label for="total_members">Total Members:</label>
                <input type="number" id="total_members" name="total_members" value="<?php echo htmlspecialchars($event['TOTAL_MEMBERS']); ?>" required>

                <button type="submit">Update Event</button>
            </form>
        <?php } else { ?>
            <p style="color: red;">Event not found.</p>
        <?php } ?>
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
