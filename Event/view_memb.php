<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Get the user's designation
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

if ($designation === 'CO-ORD') {
    include 'header.php';
}

// Retrieve RegId from GET parameters
$regId = $_GET['regId'];
if (!$regId) {
    die("RegId not provided.");
}

// Handle delete request
if (isset($_POST['delete'])) {
    $memberId = $_POST['memberId'];
    $query = "DELETE FROM Teams WHERE RegId = :regId AND Email = :memberId";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':regId', $regId);
    oci_bind_by_name($stmt, ':memberId', $memberId);
    oci_execute($stmt);
    oci_free_statement($stmt);
    header("Location: view_memb.php?regId=" . urlencode($regId));
    exit();
}

// Retrieve all members for the specific RegId
$query = "SELECT RName, College, Email FROM Teams WHERE RegId = :regId";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':regId', $regId);
oci_execute($stmt);
$members = [];
while ($row = oci_fetch_assoc($stmt)) {
    $members[] = $row;
}
oci_free_statement($stmt);

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Members</title>
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
        }
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
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
        td {
            color: white;
        }
        th {
            background-color: #333;
            color: white;
        }
        h1 {
            color: #fff;
        }
        .btn {
            background-color: white;
            border: none;
            color: #000;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #000;
            transition-duration: 0.5s;
            border: none;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Team Members</h1>
        <div class="details">
            <h2>Members for Registration ID: <?php echo htmlspecialchars($regId); ?></h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>College</th>
                    <th>Email</th>
                    <?php if ($designation !== 'VOLUNTEER') { ?>
                        <th>Action</th>
                    <?php } ?>
                </tr>
                <?php foreach ($members as $member) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['RNAME']); ?></td>
                    <td><?php echo htmlspecialchars($member['COLLEGE']); ?></td>
                    <td><?php echo htmlspecialchars($member['EMAIL']); ?></td>
                    <?php if ($designation !== 'VOLUNTEER') { ?>
                    <td>
                        <form method="post" style="margin: 0;">
                            <input type="hidden" name="memberId" value="<?php echo htmlspecialchars($member['EMAIL']); ?>">
                            <button type="submit" name="delete" class="btn">Remove</button>
                        </form>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
            </table>
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
