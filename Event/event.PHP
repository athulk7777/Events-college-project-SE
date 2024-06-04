<?php
session_start();

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

// Handle search query
$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = $_POST['search'];

    // Search query
    $query = "SELECT Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS FROM Events WHERE Ename LIKE :searchTerm";
    $stmt = oci_parse($connection, $query);

    $searchTerm = '%' . $searchTerm . '%';
    oci_bind_by_name($stmt, ':searchTerm', $searchTerm);

    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        $searchResults[] = $row;
    }

    oci_free_statement($stmt);
} else {
    // Default query to display all events
    $query = "SELECT Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS FROM Events";
    $stmt = oci_parse($connection, $query);
    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        $searchResults[] = $row;
    }

    oci_free_statement($stmt);
}

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: hidden;
            background-color: #f4f4f9;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #000; /* Ensure background color for visibility */
        }
        .container {
            position: relative;
            padding: 20px;
        }
        .search-container {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container h2 {
            background-color: #000;
            color: #fff;
            margin-bottom: 20px;
        }
        .search-container label {
            display: block;
            color: #000;
            margin-bottom: 5px;
            text-align: left;
        }
        .search-container input, .search-container button {
            width: 100%;
            padding: 10px;
            color: #000;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-container button {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container button:hover {
            color: #000;
            background-color: #fff;
            transition-duration: 0.5s;
        }
        .results-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .event-card {
            background-color: #000;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 10px;
            width: 300px;
            text-align: center;
        }
        .event-card h3 {
            margin-top: 0;
        }
        .event-card p {
            margin: 5px 0;
        }
        .event-card button {
        background-color: #fff;
        color: #000;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

        .event-card button:hover {
        background-color: #000;
        color: #fff;
    }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <div class="search-container">
            <h2>Search Events</h2>
            <form method="post" action="">
                <label for="search">Event Name:</label>
                <input type="text" id="search" name="search" required><br>
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="results-container">
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $event): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['ENAME']); ?></h3>
                        <p>Entry Fees: <?php echo htmlspecialchars($event['ENTRY_FEES']); ?></p>
                        <p>Type: <?php echo htmlspecialchars($event['ETYPE']); ?></p>
                        <p>Location: <?php echo htmlspecialchars($event['ELOCATION']); ?></p>
                        <p>Date: <?php echo htmlspecialchars($event['EDATE']); ?></p>
                        <p>Time: <?php echo htmlspecialchars($event['ETIME']); ?></p>
                        <p>Slots: <?php echo htmlspecialchars($event['SLOTS']); ?></p>
                        <p>Total Members: <?php echo htmlspecialchars($event['TOTAL_MEMBERS']); ?></p>
                        <form method="post" action="reg_event.php">
                            <input type="hidden" name="Eid" value="<?php echo htmlspecialchars($event['EID']); ?>">
                            <button type="submit">Register</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
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
</body>
</html>
