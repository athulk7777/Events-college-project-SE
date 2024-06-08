<?php
session_start();

if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: CO-ORD_LOGIN.php'); 
    exit();
}


$userid = $_SESSION['userid'];
$designation = $_SESSION['designation'];

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

// Handle form submission for adding a new filter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $filter = $_POST['filter'];

    // Insert new filter
    $insert_query = "INSERT INTO Filter (EID, Fname) VALUES (:eid, :fname)";
    $insert_stmt = oci_parse($connection, $insert_query);
    oci_bind_by_name($insert_stmt, ':eid', $eid);
    oci_bind_by_name($insert_stmt, ':fname', $filter);

    if (oci_execute($insert_stmt)) {
        echo "<p style='color: green;'>Filter added successfully.</p>";
    } else {
        $error = oci_error($insert_stmt);
        echo "<p style='color: red;'>Error adding filter: " . $error['message'] . "</p>";
    }

    oci_free_statement($insert_stmt);
}

// Handle deletion of a filter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fname'])) {
    $delete_fname = $_POST['delete_fname'];

    // Delete filter
    $delete_query = "DELETE FROM Filter WHERE EID = :eid AND Fname = :fname";
    $delete_stmt = oci_parse($connection, $delete_query);
    oci_bind_by_name($delete_stmt, ':eid', $eid);
    oci_bind_by_name($delete_stmt, ':fname', $delete_fname);

    if (oci_execute($delete_stmt)) {
        echo "<p style='color: green;'>Filter removed successfully.</p>";
    } else {
        $error = oci_error($delete_stmt);
        echo "<p style='color: red;'>Error removing filter: " . $error['message'] . "</p>";
    }

    oci_free_statement($delete_stmt);
}

// Retrieve filter details
$query = "SELECT * FROM Filter WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);

$filters = [];
while ($row = oci_fetch_assoc($stmt)) {
    $filters[] = $row;
}
oci_free_statement($stmt);

include 'header.php';

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Filters</title>
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
        .expense-form, .expense-list {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
            margin-bottom: 20px;
        }
        .expense-form label, .expense-list label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        .expense-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .expense-form button {
            padding: 10px 20px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .expense-form button:hover {
            background-color: #000;
            color: #fff;
        }
        .expense-list button {
            padding: 5px 10px;
            font-size: 14px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .expense-list button:hover {
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
        <h1>Manage Filters</h1>

        <div class="expense-form">
            <h2>Add Filters</h2>
            <form method="post" action="">
                <label for="filter">Filter for this event:</label>
                <input type="text" id="filter" name="filter" required>
                <button type="submit">Add Filter</button>
            </form>
        </div>

        <div class="expense-list">
            <h2>Filter List</h2>
            <?php if (!empty($filters)) { ?>
                <ul>
                    <?php foreach ($filters as $filter) { ?>
                        <li>
                            <label>Filter: <?php echo htmlspecialchars($filter['FNAME']); ?></label>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_fname" value="<?php echo htmlspecialchars($filter['FNAME']); ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No filters found.</p>
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
