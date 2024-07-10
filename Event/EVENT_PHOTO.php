<?php
session_start();

if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: CO-ORD_LOGIN.PHP'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];
$designation = $_SESSION['designation'];
$message = "";
$existingPhoto = null;

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

// Get Eid using userid
$query = "SELECT EID FROM CO_ORD WHERE USERID = :userid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':userid', $userid);

if (oci_execute($stmt)) {
    $row = oci_fetch_assoc($stmt);
    if ($row) {
        $eid = $row['EID'];

        // Check if a photo already exists for this Eid
        $query = "SELECT photo FROM event_photos WHERE Eid = :eid";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':eid', $eid);

        if (oci_execute($stmt)) {
            $row = oci_fetch_assoc($stmt);
            if ($row && $row['PHOTO']) {
                $existingPhoto = $row['PHOTO']->load();
            }
        }
    } else {
        $message = "Eid not found for the given userid.";
    }
} else {
    $error = oci_error($stmt);
    $message = "Error: " . $error['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['photo'])) {
        $photo = $_FILES['photo']['tmp_name'];
        $photoData = file_get_contents($photo);

        // Insert photo into event_photos table
        $query = "INSERT INTO event_photos (Eid, photo) VALUES (:eid, EMPTY_BLOB()) RETURNING photo INTO :photo";
        $stmt = oci_parse($connection, $query);

        $blob = oci_new_descriptor($connection, OCI_D_LOB);
        oci_bind_by_name($stmt, ':eid', $eid);
        oci_bind_by_name($stmt, ':photo', $blob, -1, OCI_B_BLOB);

        if (oci_execute($stmt, OCI_DEFAULT)) {
            if ($blob->save($photoData)) {
                oci_commit($connection);
                $message = "Photo uploaded successfully.";
            } else {
                $message = "Failed to upload photo.";
            }
        } else {
            $error = oci_error($stmt);
            $message = "Error: " . $error['message'];
        }

        $blob->free();
    } elseif (isset($_POST['remove'])) {
        // Remove photo from event_photos table
        $query = "DELETE FROM event_photos WHERE Eid = :eid";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':eid', $eid);

        if (oci_execute($stmt)) {
            oci_commit($connection);
            $message = "Photo removed successfully.";
            $existingPhoto = null;
        } else {
            $error = oci_error($stmt);
            $message = "Error: " . $error['message'];
        }
    }
}

oci_free_statement($stmt);
oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Event Photo</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            padding: 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        .button:hover {
            background-color: #000;
            color: #fff;
        }
        .button-container {
            display: flex;
            justify-content: center;
        }
        .message {
            margin-bottom: 15px;
            color: #ff0000;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="card">
            <h1>Upload Event Photo</h1>
            <?php if (!empty($message)): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($existingPhoto): ?>
                <h2>Existing Photo</h2>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($existingPhoto); ?>" alt="Existing Photo" />
                <form method="post" action="">
                    <button type="submit" name="remove" class="button">Remove Photo</button>
                </form>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <label for="photo">Choose Photo:</label>
                <input type="file" name="photo" id="photo" required><br><br>
                <button type="submit" class="button">Upload</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
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
</body>
</html>
