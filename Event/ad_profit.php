<?php
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['userid']) || $_SESSION['designation'] != 'ADMIN') {
    header('Location: CO-ORD_LOGIN.php'); // Redirect to login page if not logged in as admin
    exit();
}
$designation = $_SESSION['designation'];
$config = include('config.php');
if ($designation === 'ADMIN') {
    include 'ADD_HEADER.php';
}

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


// Retrieve total admin expense for EID 101
$query = "SELECT SUM(EXPENSE) AS total_admin_expense FROM Expense WHERE EID = 101";
$stmt = oci_parse($connection, $query);
oci_execute($stmt);
$total_admin_expense = 0;
if ($row = oci_fetch_assoc($stmt)) {
    $total_admin_expense = $row['TOTAL_ADMIN_EXPENSE'];
}
oci_free_statement($stmt);

// Retrieve total profit from all events
$query = "SELECT SUM(PROFIT) AS total_profit FROM Profit";
$stmt = oci_parse($connection, $query);
oci_execute($stmt);
$total_profit = 0;
if ($row = oci_fetch_assoc($stmt)) {
    $total_profit = $row['TOTAL_PROFIT'];
}
oci_free_statement($stmt);

// Retrieve all profit details along with event names
$query = "SELECT p.EID, p.EARNED, p.PROFIT, e.ENAME 
          FROM Profit p 
          JOIN Events e ON p.EID = e.EID";
$stmt = oci_parse($connection, $query);
oci_execute($stmt);
$profit_details = [];
while ($row = oci_fetch_assoc($stmt)) {
    $profit_details[] = $row;
}
oci_free_statement($stmt);

// Retrieve total sponsor amount for EID 101
$query = "SELECT SUM(Amount) AS total_sponsor_amount FROM Sponsors WHERE Eid = 101";
$stmt = oci_parse($connection, $query);
oci_execute($stmt);
$total_sponsor_amount = 0;
if ($row = oci_fetch_assoc($stmt)) {
    $total_sponsor_amount = $row['TOTAL_SPONSOR_AMOUNT'];
}
oci_free_statement($stmt);

oci_close($connection); // Close the database connection when done

// Calculate admin profit
$admin_profit = $total_profit + $total_sponsor_amount - $total_admin_expense;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profit Calculation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
            overflow: auto;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }
        h1, h2 {
            text-align: center;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #333;
        }
        .summary {
            margin-top: 20px;
        }
        .summary label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .calc-format {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Admin Profit Calculation</h1>
        
        <h2>Expense Details for Admin</h2>
        <div class="summary">
            <label>Total Admin Expense: <?php echo htmlspecialchars($total_admin_expense); ?></label>
        </div>

        <h2>Total Profit from All Events</h2>
        <div class="summary">
            <label>Total Profit: <?php echo htmlspecialchars($total_profit); ?></label>
        </div>

        <h2>Profit Details by Event</h2>
        <table>
            <tr>
                <th>Event Name</th>
                <th>Earned</th>
                <th>Profit</th>
            </tr>
            <?php foreach ($profit_details as $profit) { ?>
            <tr>
                <td><?php echo htmlspecialchars($profit['ENAME']); ?></td>
                <td><?php echo htmlspecialchars($profit['EARNED']); ?></td>
                <td><?php echo htmlspecialchars($profit['PROFIT']); ?></td>
            </tr>
            <?php } ?>
        </table>

        <h2>Sponsor Amount from Admin</h2>
        <div class="summary">
            <label>Total Sponsor Amount: <?php echo htmlspecialchars($total_sponsor_amount); ?></label>
        </div>

        <h2>Admin Profit Calculation</h2>
        <div class="summary calc-format">
            <label>+ Events Profit: <?php echo htmlspecialchars($total_profit); ?></label>
            <label>+ Total Sponsor Amount: <?php echo htmlspecialchars($total_sponsor_amount); ?></label>
            <label>- Total Admin Expense: <?php echo htmlspecialchars($total_admin_expense); ?></label>
        </div>
        <div class="summary">
            <label><strong>Admin Profit: <?php echo htmlspecialchars($admin_profit); ?></strong></label>
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
