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

// Function to get the total number of forms
function getTotalForms() {
    $totalForms = isset($_SESSION['totalForms']) ? $_SESSION['totalForms'] : 5;
    return $totalForms;
}

// Function to update the total number of forms
function updateTotalForms($newTotal) {
    $_SESSION['totalForms'] = $newTotal;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form index as Eid
    $eid = $_POST['eid']+1;

    // Get form data
    $ename = $_POST['ename'];
    $entry_fees = $_POST['entry_fees'];
    $etype = $_POST['etype'];
    $elocation = $_POST['elocation'];
    $edate = $_POST['edate'];
    $etime = $_POST['etime'];
    $slots = $_POST['slots'];
    $total_members = $_POST['total_members'];

    // Check if Eid already exists
    $sql_check = "SELECT COUNT(*) FROM Events WHERE Eid = :eid";
    $stmt_check = oci_parse($connection, $sql_check);
    oci_bind_by_name($stmt_check, ':eid', $eid);
    oci_execute($stmt_check);
    $row = oci_fetch_assoc($stmt_check);
    if ($row['COUNT(*)'] > 0) {
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
        if ($row['COUNT(*)'] > 0) {
            echo "Data updated successfully.";
        } else {
            echo "Data inserted successfully.";
        }
    } else {
        $error = oci_error($stmt);
        echo "Error: " . $error['message'];
    }

    // Free statement and close connection
    oci_free_statement($stmt);
    oci_close($connection);
}

// Get total number of forms
$totalForms = getTotalForms();

// Add new form
if (isset($_POST['add_new_form'])) {
    $totalForms++;
    updateTotalForms($totalForms);
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
        <h2>Insert Event</h2>
        <div id="forms-container">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <form id="form<?= $i ?>" class="<?= ($i === 0) ? 'active' : '' ?>" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="eid" value="<?= $i ?>"> <!-- Hidden input for Eid -->
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
            <button type="button" onclick="commitForm(<?= $i ?>)">Commit</button>
            <button type="button" onclick="addNewForm()">Add New Form</button>
        </div>
    </div>

    <script>
        let formCount = 10 ;
 // Number of forms
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
            console.log("Current form index:", currentFormIndex);
            var currentForm = forms[currentFormIndex];
            console.log("Current form:", currentForm);
            var formData = {};
            currentForm.querySelectorAll('input').forEach(function(input) {
                formData[input.name] = input.value;
            });
            localStorage.setItem('formData' + index, JSON.stringify(formData));
            alert('Form data saved successfully!');
        }
        


        function commitForm(index) {
            console.log("Current form index:", currentFormIndex);
            var currentForm = forms[currentFormIndex];
            console.log("Current form:", currentForm);
            var formData = {};
            currentForm.querySelectorAll('input').forEach(function(input) {
                formData[input.name] = input.value;
            });
            localStorage.setItem('formData' + index, JSON.stringify(formData));
            alert('Form data saved to local storage.');

            currentForm.submit();
             // Submit the form for insertion into database
        }

        function addNewForm() {
    formCount++; // Increment the total number of forms by 1
    var newFormIndex = formCount - 1;
    var newForm = document.createElement('form');
    newForm.id = 'form' + newFormIndex;
    newForm.classList.add('active');
    newForm.method = 'post';
    newForm.action = '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>';
    newForm.innerHTML = `
    <input type="hidden" name="eid" value="${newFormIndex}">
        <label for="ename${newFormIndex}">Event Name:</label><br>
        <input type="text" id="ename${newFormIndex}" name="ename" value="" required><br><br>
        <label for="entry_fees${newFormIndex}">Entry Fees:</label><br>
        <input type="number" id="entry_fees${newFormIndex}" name="entry_fees" value="" required><br><br>
        <label for="etype${newFormIndex}">Event Type:</label><br>
        <input type="text" id="etype${newFormIndex}" name="etype" value="" required><br><br>
        <label for="elocation${newFormIndex}">Event Location:</label><br>
        <input type="text" id="elocation${newFormIndex}" name="elocation" value="" required><br><br>
        <label for="edate${newFormIndex}">Event Date:</label><br>
        <input type="text" id="edate${newFormIndex}" name="edate" value="" required><br><br>
        <label for="etime${newFormIndex}">Event Time:</label><br>
        <input type="text" id="etime${newFormIndex}" name="etime" value="" required><br><br>
        <label for="slots${newFormIndex}">Slots:</label><br>
        <input type="number" id="slots${newFormIndex}" name="slots" value="" required><br><br>
        <label for="total_members${newFormIndex}">Total Members:</label><br>
        <input type="number" id="total_members${newFormIndex}" name="total_members" value="" required><br><br>
        <button type="button" onclick="saveFormData(${newFormIndex})">Save</button>
    `;
    formsContainer.appendChild(newForm);

    // Update currentFormIndex
    currentFormIndex = newFormIndex;

    // Log for debugging
    console.log("New form appended:", newForm);
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
