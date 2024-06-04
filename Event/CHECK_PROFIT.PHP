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

// Retrieve total expense and detailed expenses
$query = "SELECT * FROM Expense WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$expenses = [];
$total_expense = 0;
while ($row = oci_fetch_assoc($stmt)) {
    $expenses[] = $row;
    $total_expense += $row['EXPENSE'];
}
oci_free_statement($stmt);

// Retrieve total registrations
$query = "SELECT COUNT(*) AS total_registrations FROM Registration WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$total_registrations = 0;
if ($row = oci_fetch_assoc($stmt)) {
    $total_registrations = $row['TOTAL_REGISTRATIONS'];
}
oci_free_statement($stmt);

// Calculate total earned based on total registrations
$query = "SELECT Entry_fees FROM Events WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$entry_fees = 0;
if ($row = oci_fetch_assoc($stmt)) {
    $entry_fees = $row['ENTRY_FEES'];
}
oci_free_statement($stmt);

$total_earned = $total_registrations * $entry_fees;

// Retrieve total sponsor amount for the given EID
$query = "SELECT SUM(Amount) AS total_sponsor_amount FROM Sponsors WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$total_sponsor_amount = 0;
if ($row = oci_fetch_assoc($stmt)) {
    $total_sponsor_amount = $row['TOTAL_SPONSOR_AMOUNT'];
}
oci_free_statement($stmt);

// Add sponsor amount to the total profit
$total_profit = $total_earned - $total_expense + $total_sponsor_amount;

// Insert or update profit in the Profit table
$query = "MERGE INTO Profit p
          USING (SELECT :eid AS Eid FROM dual) d
          ON (p.Eid = d.Eid)
          WHEN MATCHED THEN
            UPDATE SET p.Earned = :earned, p.Profit = :profit
          WHEN NOT MATCHED THEN
            INSERT (Eid, Earned, Profit)
            VALUES (:eid, :earned, :profit)";

$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_bind_by_name($stmt, ':earned', $total_earned);
oci_bind_by_name($stmt, ':profit', $total_profit);

if (!oci_execute($stmt)) {
    $error = oci_error($stmt);
    echo "<p style='color: red;'>Error updating profit: " . $error['message'] . "</p>";
}
oci_free_statement($stmt);

// Retrieve profit details for display
$query = "SELECT Earned, Profit FROM Profit WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$profit_details = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

// Retrieve sponsor details
$query = "SELECT Sponsor, Amount FROM Sponsors WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$sponsors = [];
while ($row = oci_fetch_assoc($stmt)) {
    $sponsors[] = $row;
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
    <title>Check Profit</title>
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
            z-index: -1; /* Set a higher z-index to ensure it's above other elements */
            background-color: #000; /* Ensure background color for visibility */
        }

        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
            color: white;
        }
        .details {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
            margin-bottom: 20px;
        }
        .details label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        .expenses {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        td{
            color: white;
        }
        th {
            background-color: #333;
            color: white;
        }
        h1, h2 {
            color: #fff;
        }
        .inline-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Check Profit</h1>

        <div class="inline-details">
            <div class="details">
                <h2>Expense Details</h2>
                <table>
                    <tr>
                        <th>Amount</th>
                        <th>Reason</th>
                    </tr>
                    <?php foreach ($expenses as $expense) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($expense['EXPENSE']); ?></td>
                        <td><?php echo htmlspecialchars($expense['REASON']); ?></td>
                    </tr>
                    <?php } ?>
                </table>
                <label>Total Expense: <?php echo htmlspecialchars($total_expense); ?></label>
            </div>

            <div class="details">
                <h2>Earnings Calculation</h2>
                <label>Total Registrations: <?php echo htmlspecialchars($total_registrations); ?></label>
                <label>Entry Fee: <?php echo htmlspecialchars($entry_fees); ?></label>
                <label>Earned: <?php echo htmlspecialchars($total_earned); ?></label>
            </div>

            <div class="details">
                <h2>Profit Details</h2>
                <label>Total Sponsor Amount: <?php echo htmlspecialchars($total_sponsor_amount); ?></label>
                <?php if ($profit_details) { ?>
                    <label>Earned: <?php echo htmlspecialchars($profit_details['EARNED']); ?></label>
                    <label>Profit: <?php echo htmlspecialchars($profit_details['PROFIT']); ?></label>
                <?php } else { ?>
                    <p>No profit details found.</p>
                <?php } ?>
            </div>

            <div class="details">
                <h2>Sponsor Details</h2>
                <table>
                    <tr>
                        <th>Sponsor</th>
                        <th>Amount</th>
                    </tr>
                    <?php foreach ($sponsors as $sponsor) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sponsor['SPONSOR']); ?></td>
                            <td><?php echo htmlspecialchars($sponsor['AMOUNT']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
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
