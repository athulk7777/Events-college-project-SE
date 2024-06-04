<?php
session_start();

// Check if user is logged in and their designation is set
if (!isset($_SESSION['designation'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

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

// Initialize variables
$message = '';

// Get the appropriate EID based on user designation
if ($_SESSION['designation'] == 'CO-ORD') {
    $userid = $_SESSION['userid']; // Assuming userid is stored in session
    $queryEID = "SELECT EID FROM CO_ORD WHERE USERID = :userid";
    $stmtEID = oci_parse($connection, $queryEID);
    oci_bind_by_name($stmtEID, ':userid', $userid);
    oci_execute($stmtEID);
    $rowEID = oci_fetch_assoc($stmtEID);
    $eid = $rowEID['EID'];
    oci_free_statement($stmtEID);
} elseif ($_SESSION['designation'] == 'ADMIN') {
    $eid = 101; // Admin's EID is always 101
}

// Handle form submission to insert sponsor
if (isset($_POST['add_sponsor'])) {
    $amount = $_POST['amount'];
    $sponsor = $_POST['sponsor'];
    $sponsorid = rand(1000, 9999); // Generate a random sponsorid

    // Insert sponsor into the database
    $queryInsert = "INSERT INTO SPONSORS (EID, SPONSORID, AMOUNT, SPONSOR) VALUES (:eid, :sponsorid, :amount, :sponsor)";
    $stmtInsert = oci_parse($connection, $queryInsert);
    oci_bind_by_name($stmtInsert, ':eid', $eid);
    oci_bind_by_name($stmtInsert, ':sponsorid', $sponsorid);
    oci_bind_by_name($stmtInsert, ':amount', $amount);
    oci_bind_by_name($stmtInsert, ':sponsor', $sponsor);
    
    if (oci_execute($stmtInsert)) {
        $message = "Sponsor added successfully.";
    } else {
        $error = oci_error($stmtInsert);
        $message = "Failed to add sponsor: " . $error['message'];
    }

    oci_free_statement($stmtInsert);
}

// Handle sponsor removal
if (isset($_POST['remove_sponsor'])) {
    $sponsorid = $_POST['sponsorid'];

    // Delete sponsor from the database
    $queryDelete = "DELETE FROM SPONSORS WHERE SPONSORID = :sponsorid";
    $stmtDelete = oci_parse($connection, $queryDelete);
    oci_bind_by_name($stmtDelete, ':sponsorid', $sponsorid);
    
    if (oci_execute($stmtDelete)) {
        $message = "Sponsor removed successfully.";
    } else {
        $error = oci_error($stmtDelete);
        $message = "Failed to remove sponsor: " . $error['message'];
    }

    oci_free_statement($stmtDelete);
}

// Fetch all sponsors
$querySponsors = "SELECT SPONSORID, AMOUNT, SPONSOR FROM SPONSORS WHERE EID = :eid";
$stmtSponsors = oci_parse($connection, $querySponsors);
oci_bind_by_name($stmtSponsors, ':eid', $eid);
oci_execute($stmtSponsors);

$sponsors = [];
while ($row = oci_fetch_assoc($stmtSponsors)) {
    $sponsors[] = $row;
}
oci_free_statement($stmtSponsors);

include 'header.php';

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sponsors</title>
    <style>
        body {
            margin: 0;
            background: url('background_image.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: white;
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
        .sponsor-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .sponsor-list th, .sponsor-list td {
            border: 1px solid #444;
            padding: 8px;
            text-align: left;
        }
        .sponsor-list th {
            background-color: #555;
            color: white;
        }
        .sponsor-list td {
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
            <h1>Add Sponsor</h1>
            <?php if (isset($message)) { ?>
                <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>
            <form method="post" action="">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" required>
                
                <label for="sponsor">Sponsor:</label>
                <input type="text" id="sponsor" name="sponsor" required>
                
                <button type="submit" name="add_sponsor">Add Sponsor</button>
            </form>
        </div>
        <div class="list-container">
            <h2>Sponsors</h2>
            <div class="sponsor-list">
                <table>
                    <tr>
                        <th>Amount</th>
                        <th>Sponsor</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($sponsors as $sponsor) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars(number_format($sponsor['AMOUNT'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($sponsor['SPONSOR']); ?></td>
                            <td>
                                <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="sponsorid" value="<?php echo htmlspecialchars($sponsor['SPONSORID']); ?>">
                                    <button type="submit" name="remove_sponsor">Remove</button>
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
    <?php include 'footer.php'; ?>
</body>
</html>

