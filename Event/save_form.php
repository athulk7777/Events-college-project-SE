<?php
header('Content-Type: application/json');

// Database connection
$connection = oci_connect('username', 'password', 'hostname/databasename');
if (!$connection) {
    $error = oci_error();
    echo json_encode(['error' => $error['message']]);
    exit;
}

// Get the JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Prepare and bind
    $sql = "INSERT INTO Events (Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS) VALUES (:Eid, :Ename, :Entry_fees, :EType, :ELocation, :EDate, :ETime, :SLOTS, :TOTAL_MEMBERS)";
    $stmt = oci_parse($connection, $sql);

    // Bind variables
    oci_bind_by_name($stmt, ':Eid', $data['Eid']);
    oci_bind_by_name($stmt, ':Ename', $data['Ename']);
    oci_bind_by_name($stmt, ':Entry_fees', $data['Entry_fees']);
    oci_bind_by_name($stmt, ':EType', $data['EType']);
    oci_bind_by_name($stmt, ':ELocation', $data['ELocation']);
    oci_bind_by_name($stmt, ':EDate', $data['EDate']);
    oci_bind_by_name($stmt, ':ETime', $data['ETime']);
    oci_bind_by_name($stmt, ':SLOTS', $data['SLOTS']);
    oci_bind_by_name($stmt, ':TOTAL_MEMBERS', $data['TOTAL_MEMBERS']);

    $result = oci_execute($stmt);
    if ($result) {
        echo json_encode(['success' => 'Data inserted successfully']);
    } else {
        $error = oci_error($stmt);
        echo json_encode(['error' => $error['message']]);
    }
} else {
    echo json_encode(['error' => 'Invalid input']);
}

oci_close($connection);
?>
