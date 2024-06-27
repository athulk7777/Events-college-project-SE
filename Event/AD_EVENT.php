<?php
session_start();

// Check if the user is logged in and if the designation is ADMIN
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation']) || $_SESSION['designation'] !== 'ADMIN') {
    header('Location: CO-ORD_LOGIN.PHP'); // Redirect to login page if not logged in or not an admin
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

// Handle logout request
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: CO-ORD_LOGIN.PHP');
    exit();
}

if ($designation === 'ADMIN') {
    include 'ADD_HEADER.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $eid = $_POST['eid']; // Eid from form index
    $ename = $_POST['ename'];
    $entry_fees = $_POST['entry_fees'];
    $etype = $_POST['etype'];
    $elocation = $_POST['elocation'];
    $edate = $_POST['edate'];
    $etime = $_POST['etime'];
    $slots = $_POST['slots'];
    $total_members = $_POST['total_members'];

    // Check if Eid exists in the table
    $query = "SELECT COUNT(*) AS count FROM Events WHERE Eid = $eid";
    $stmt = oci_parse($connection, $query);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $count = $row['COUNT'];

    if ($count > 0) {
        // Update existing record
        $sql = "UPDATE Events SET Ename = :ename, Entry_fees = :entry_fees, EType = :etype, ELocation = :elocation, EDate = :edate, ETime = :etime, Slots = :slots, Total_members = :total_members WHERE Eid = :eid";
    } else {
        // Insert new record
        $sql = "INSERT INTO Events (Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, Slots, Total_members) VALUES (:eid, :ename, :entry_fees, :etype, :elocation, :edate, :etime, :slots, :total_members)";
    }

    $stmt = oci_parse($connection, $sql);

    // Bind parameters
    oci_bind_by_name($stmt, ':eid', $eid);
    oci_bind_by_name($stmt, ':ename', $ename);
    oci_bind_by_name($stmt, ':entry_fees', $entry_fees);
    oci_bind_by_name($stmt, ':etype', $etype);
    oci_bind_by_name($stmt, ':elocation', $elocation);
    oci_bind_by_name($stmt, ':edate', $edate);
    oci_bind_by_name($stmt, ':etime', $etime);
    oci_bind_by_name($stmt, ':slots', $slots);
    oci_bind_by_name($stmt, ':total_members', $total_members);

    // Execute SQL
    $result = oci_execute($stmt);
    if ($result) {
        echo "Data inserted/updated successfully.";
    } else {
        $error = oci_error($stmt);
        echo "Error: " . $error['message'];
    }

    // Free statement and close connection
    oci_free_statement($stmt);
    oci_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-step Form with Local Storage</title>
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
            position: relative;
            z-index: 1;
            color: white;
        }
        form {
            display: none;
        }
        form.active {
            display: block;
        }

        


        .btn {
            background-color: white;
            border: none;
            color: #000;
            padding: 15px 30px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 5px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #000;
            color: white;
            transition-duration: 0.5s;
            box-shadow: 0 4px 8px 0 white, 0 6px 20px 0 white;   
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h2>Admin Event Updation Form</h2>
        <div id="forms-container">
            <?php for ($i = 1; $i <= 25; $i++): ?>
                <form id="form<?= $i ?>" class="<?= ($i === 1) ? 'active' : '' ?>" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="eid" value="<?= $i ?>">
                    <label for="ename<?= $i ?>">Event Name:</label><br>
                    <input type="text" id="ename<?= $i ?>" name="ename" value="<?= isset($_POST['ename']) ? $_POST['ename'] : '' ?>" required><br><br>
                    <label for="entry_fees<?= $i ?>">Entry Fees:</label><br>
                    <input type="number" id="entry_fees<?= $i ?>" name="entry_fees" value="<?= isset($_POST['entry_fees']) ? $_POST['entry_fees'] : '' ?>" required><br><br>
                    <label for="etype<?= $i ?>">Event Type:</label><br>
                    <input type="text" id="etype<?= $i ?>" name="etype" value="<?= isset($_POST['etype']) ? $_POST['etype'] : '' ?>" required><br><br>
                    <label for="elocation<?= $i ?>">Event Location:</label><br>
                    <input type="text" id="elocation<?= $i ?>" name="elocation" value="<?= isset($_POST['elocation']) ? $_POST['elocation'] : '' ?>" required><br><br>
                    <label for="edate<?= $i ?>">Event Date:</label><br>
                    <input type="text" id="edate<?= $i ?>" name="edate" value="<?= isset($_POST['edate']) ? $_POST['edate'] : '' ?>" required><br><br>
                    <label for="etime<?= $i ?>">Event Time:</label><br>
                    <input type="text" id="etime<?= $i ?>" name="etime" value="<?= isset($_POST['etime']) ? $_POST['etime'] : '' ?>" required><br><br>
                    <label for="slots<?= $i ?>">Slots:</label><br>
                    <input type="number" id="slots<?= $i ?>" name="slots" value="<?= isset($_POST['slots']) ? $_POST['slots'] : '' ?>" required><br><br>
                    <label for="total_members<?= $i ?>">Total Members:</label><br>
                    <input type="number" id="total_members<?= $i ?>" name="total_members" value="<?= isset($_POST['total_members']) ? $_POST['total_members'] : '' ?>" required><br><br>
                    <button type="button" class="btn" onclick="saveFormData(<?= $i ?>)">Save</button>
                </form>
            <?php endfor; ?>
        </div>
        <div class="buttons">
            <button type="button" class="btn" onclick="prevForm()">Previous</button>
            <button type="button" class="btn" onclick="nextForm()">Next</button>
            <button type="button" class="btn" onclick="commitForm()">Commit</button>
        </div>
    </div>

    <script>
        const formCount = 25; // Number of forms
        var currentFormIndex = 1; // Start from index 1
        var formsContainer = document.getElementById('forms-container');
        var forms = document.querySelectorAll('form');

        function showForm(index) {
            forms.forEach(function(form, i) {
                if (i + 1 === index) {
                    form.classList.add('active');
                } else {
                    form.classList.remove('active');
                }
            });
        }

        function saveFormData(index) {
            const form = forms[index - 1];
            const formData = new FormData(form);

            // Save data to local storage
            localStorage.setItem(`formData${index}`, JSON.stringify(Object.fromEntries(formData.entries())));
        }

        function loadFormData(index) {
            const form = forms[index - 1];
            const savedData = JSON.parse(localStorage.getItem(`formData${index}`));

            if (savedData) {
                for (let key in savedData) {
                    if (savedData.hasOwnProperty(key)) {
                        form.elements[key].value = savedData[key];
                    }
                }
            }
        }

        function prevForm() {
            if (currentFormIndex > 1) {
                currentFormIndex--;
                showForm(currentFormIndex);
                loadFormData(currentFormIndex);
            }
        }

        function nextForm() {
            if (currentFormIndex < formCount) {
                currentFormIndex++;
                showForm(currentFormIndex);
                loadFormData(currentFormIndex);
            }
        }

        function commitForm() {
            const currentForm = forms[currentFormIndex - 1];
            currentForm.submit();
        }

        window.addEventListener('load', function() {
            showForm(currentFormIndex);
            loadFormData(currentFormIndex);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
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
                    },
                    "image": {
                        "src": "img/github.svg",
                        "width": 100,
                        "height": 100
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
