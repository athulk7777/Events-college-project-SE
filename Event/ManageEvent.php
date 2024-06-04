<?php
session_start();
$host = 'localhost';
$port = '1521';
$service_name = 'flight';
$oracleUsername = 'system';
$oraclePassword = 'abhinav2';

$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . $error['message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $ename = $_POST['ename'];
    $entry_fees = $_POST['entry_fees'];
    $etype = $_POST['etype'];
    $elocation = $_POST['elocation'];
    $edate = $_POST['edate'];
    $etime = $_POST['etime'];
    $slots = $_POST['slots'];
    $total_members = $_POST['total_members'];
    $eid = $_POST['eid'];

    // Prepare SQL statement
    $sql_check = "SELECT COUNT(*) FROM Events WHERE Eid = :eid";
    $stmt_check = oci_parse($connection, $sql_check);
    oci_bind_by_name($stmt_check, ':eid', $eid);
    oci_execute($stmt_check);
    $row = oci_fetch_array($stmt_check, OCI_RETURN_NULLS);
    $exists = ($row[0] > 0);

    if ($exists) {
        $sql = "UPDATE Events SET Ename = :ename, Entry_fees = :entry_fees, EType = :etype, ELocation = :elocation, EDate = :edate, ETime = :etime, Slots = :slots, Total_members = :total_members WHERE Eid = :eid";
    } else {
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

    // Free statement
    oci_free_statement($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-step Form with Local Storage</title>
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        form {
            display: none;
        }
        form.active {
            display: block;
        }
        .buttons {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Insert/Update Event</h2>
        <div id="forms-container">
            <?php for ($i = 0; $i < 26; $i++): ?>
                <form id="form<?= $i ?>" class="<?= ($i === 0) ? 'active' : '' ?>" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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
                    <button type="button" onclick="saveFormData(<?= $i ?>)">Save</button>
                </form>
            <?php endfor; ?>
        </div>
        <div class="buttons">
            <button type="button" onclick="prevForm()">Previous</button>
            <button type="button" onclick="nextForm()">Next</button>
            <button type="button" onclick="commitForm()">Commit</button>
        </div>
    </div>

    <script>
        const formCount = 26; // Number of forms
        var currentFormIndex = 0;
        var formsContainer = document.getElementById('forms-container');
        var forms = document.querySelectorAll('form');

        function showForm(index) {
            forms.forEach(function(form, i) {
                if (i === index) {
                    form.classList.add('active');
                } else {
                    form.classList.remove('active');
                }
            });
        }

        function nextForm() {
            if (currentFormIndex < formCount - 1) {
                currentFormIndex++;
                showForm(currentFormIndex);
            }
        }

        function prevForm() {
            if (currentFormIndex > 0) {
                currentFormIndex--;
                showForm(currentFormIndex);
            }
        }

        function saveFormData(index) {
            var currentForm = forms[currentFormIndex];
            var formData = {};
            currentForm.querySelectorAll('input').forEach(function(input) {
                formData[input.name] = input.value;
            });
            localStorage.setItem('formData' + index, JSON.stringify(formData));
            alert('Form data saved successfully!');
        }

        function commitForm() {
    var currentForm = forms[currentFormIndex];
    var formData = {};
    currentForm.querySelectorAll('input').forEach(function(input) {
        formData[input.name] = input.value;
    });
    var eid = formData['eid'];
    // Submit the form for insertion/update into database
    currentForm.submit();
}


        window.onload = function() {
            for (let i = 0; i < formCount; i++) {
                var savedData = localStorage.getItem('formData' + i);
                if (savedData) {
                    var formData = JSON.parse(savedData);
                    var currentForm = forms[i];
                    currentForm.querySelectorAll('input').forEach(function(input) {
                        if (formData[input.name]) {
                            input.value = formData[input.name];
                        }
                    });
                }
            }
        };
    </script>
</body>
</html>
