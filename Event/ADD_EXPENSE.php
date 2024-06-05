<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: CO-ORD_LOGIN.php'); 
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

// Handle form submission for adding a new expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expense']) && isset($_POST['reason'])) {
    $expense = $_POST['expense'];
    $reason = $_POST['reason'];
    $expid = time(); // Use current timestamp as EXPID for uniqueness

    // Insert new expense
    $insert_query = "INSERT INTO Expense (EID, EXPID, EXPENSE, REASON) VALUES (:eid, :expid, :expense, :reason)";
    $insert_stmt = oci_parse($connection, $insert_query);
    oci_bind_by_name($insert_stmt, ':eid', $eid);
    oci_bind_by_name($insert_stmt, ':expid', $expid);
    oci_bind_by_name($insert_stmt, ':expense', $expense);
    oci_bind_by_name($insert_stmt, ':reason', $reason);

    if (oci_execute($insert_stmt)) {
        echo "<p style='color: green;'>Expense added successfully.</p>";
    } else {
        $error = oci_error($insert_stmt);
        echo "<p style='color: red;'>Error adding expense: " . $error['message'] . "</p>";
    }

    oci_free_statement($insert_stmt);
}

// Handle deletion of an expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_expid'])) {
    $delete_expid = $_POST['delete_expid'];

    // Delete expense
    $delete_query = "DELETE FROM Expense WHERE EID = :eid AND EXPID = :expid";
    $delete_stmt = oci_parse($connection, $delete_query);
    oci_bind_by_name($delete_stmt, ':eid', $eid);
    oci_bind_by_name($delete_stmt, ':expid', $delete_expid);

    if (oci_execute($delete_stmt)) {
        echo "<p style='color: green;'>Expense removed successfully.</p>";
    } else {
        $error = oci_error($delete_stmt);
        echo "<p style='color: red;'>Error removing expense: " . $error['message'] . "</p>";
    }

    oci_free_statement($delete_stmt);
}

// Retrieve expense details
$query = "SELECT * FROM Expense WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);

$expenses = [];
while ($row = oci_fetch_assoc($stmt)) {
    $expenses[] = $row;
}
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
    <title>Manage Expenses</title>
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
        <h1>Manage Expenses</h1>

        <div class="expense-form">
            <h2>Add Expense</h2>
            <form method="post" action="">
                <label for="expense">Expense Amount:</label>
                <input type="number" id="expense" name="expense" required>

                <label for="reason">Reason:</label>
                <input type="text" id="reason" name="reason" required>

                <button type="submit">Add Expense</button>
            </form>
        </div>

        <div class="expense-list">
            <h2>Expense List</h2>
            <?php if (!empty($expenses)) { ?>
                <ul>
                    <?php foreach ($expenses as $expense) { ?>
                        <li>
                            <label>Expense ID: <?php echo htmlspecialchars($expense['EXPID']); ?></label>
                            <label>Amount: <?php echo htmlspecialchars($expense['EXPENSE']); ?></label>
                            <label>Reason: <?php echo htmlspecialchars($expense['REASON']); ?></label>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_expid" value="<?php echo htmlspecialchars($expense['EXPID']); ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No expenses found.</p>
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
