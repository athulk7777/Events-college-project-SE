<?php
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

// Initialize variables
$message = '';

// Handle form submission
if (isset($_POST['add_expense'])) {
    $expense = $_POST['expense'];
    $reason = $_POST['reason'];
    $eid = 101; // Set EID to 101 for admin expenses
    $expid = rand(1000, 9999); // Generate a random EXPID

    // Insert expense into the database
    $query = "INSERT INTO Expense (EID, EXPID, EXPENSE, REASON) VALUES (:eid, :expid, :expense, :reason)";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':eid', $eid);
    oci_bind_by_name($stmt, ':expid', $expid);
    oci_bind_by_name($stmt, ':expense', $expense);
    oci_bind_by_name($stmt, ':reason', $reason);
    
    if (oci_execute($stmt)) {
        $message = "Expense added successfully.";
    } else {
        $error = oci_error($stmt);
        $message = "Failed to add expense: " . $error['message'];
    }

    oci_free_statement($stmt);
}

// Handle expense removal
if (isset($_POST['remove_expense'])) {
    $expid = $_POST['expid'];

    // Delete expense from the database
    $query = "DELETE FROM Expense WHERE EXPID = :expid";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':expid', $expid);
    
    if (oci_execute($stmt)) {
        $message = "Expense removed successfully.";
    } else {
        $error = oci_error($stmt);
        $message = "Failed to remove expense: " . $error['message'];
    }

    oci_free_statement($stmt);
}

// Fetch all admin expenses
$queryAdmin = "SELECT * FROM Expense WHERE EID = 101";
$stmtAdmin = oci_parse($connection, $queryAdmin);
oci_execute($stmtAdmin);

$expensesAdmin = [];
while ($row = oci_fetch_assoc($stmtAdmin)) {
    $expensesAdmin[] = $row;
}
oci_free_statement($stmtAdmin);

// Fetch total earned
$queryTotalEarned = "SELECT SUM(PROFIT) AS TOTAL_EARNED FROM PROFIT WHERE EID = 101";
$stmtTotalEarned = oci_parse($connection, $queryTotalEarned);
oci_execute($stmtTotalEarned);
$totalEarnedRow = oci_fetch_assoc($stmtTotalEarned);
$totalEarned = $totalEarnedRow['TOTAL_EARNED'];
oci_free_statement($stmtTotalEarned);

// Calculate total admin expenses
$queryTotalExpenseAdmin = "SELECT SUM(EXPENSE) AS TOTAL_EXPENSE FROM Expense WHERE EID = 101";
$stmtTotalExpenseAdmin = oci_parse($connection, $queryTotalExpenseAdmin);
oci_execute($stmtTotalExpenseAdmin);
$totalExpenseAdminRow = oci_fetch_assoc($stmtTotalExpenseAdmin);
$totalExpenseAdmin = $totalExpenseAdminRow['TOTAL_EXPENSE'];
oci_free_statement($stmtTotalExpenseAdmin);

// Calculate profit
$profit = $totalEarned - $totalExpenseAdmin;

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <style>
        body {
            margin: 0;
            background: url('background_image.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: white;
            overflow: hidden;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #000;
        }
        .container {
            display: flex;
            height: 100vh;
            overflow: auto;
        }
        .form-container, .list-container {
            flex: 1;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            margin: 10px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .form-container {
            max-width: 400px;
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            color: #ccc;
        }
        input, textarea, button {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        button {
            background-color: #444;
            border: none;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #666;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            color: white;
        }
        .profit-details {
            margin-top: 20px;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 5px;
        }
        .expense-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .expense-list th, .expense-list td {
            border: 1px solid #444;
            padding: 8px;
            text-align: left;
        }
        .expense-list th {
            background-color: #555;
            color: white;
        }
        .expense-list td {
            background-color: #333;
        }
        .particle-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="particle-container">
        <div id="particles-js"></div>
    </div>
    <div class="container">
        <div class="form-container">
            <h1>Add Expense</h1>
            <?php if (isset($message)) { ?>
                <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>
            <form method="post" action="">
                <label for="expense">Expense Amount:</label>
                <input type="number" id="expense" name="expense" step="0.01" required>
                
                <label for="reason">Reason:</label>
                <textarea id="reason" name="reason" required></textarea>
                
                <button type="submit" name="add_expense">Add Expense</button>
            </form>
            <div class="profit-details">
                <h2>Expense Details</h2>
                <p><strong>Total Expenses (Admin):</strong> <?php echo htmlspecialchars(number_format($totalExpenseAdmin, 2)); ?></p>
            </div>
        </div>
        <div class="list-container">
            <h2>Admin Expenses</h2>
            <div class="expense-list">
                <table>
                    <tr>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($expensesAdmin as $expense) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars(number_format($expense['EXPENSE'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($expense['REASON']); ?></td>
                            <td>
                                <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="expid" value="<?php echo htmlspecialchars($expense['EXPID']); ?>">
                                    <button type="submit" name="remove_expense">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>

    <script src="js/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
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
                    "random": false,
                    "anim": {
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
                        "size_min": 0.1,
                        "sync": false
                    }
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
                    "bounce": false,
                    "attract": {
                        "enable": false,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
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
