<?php
session_start();

// Function to read events from JSON file
function read_events_from_file($filename) {
    if (file_exists($filename)) {
        $json_data = file_get_contents($filename);
        return json_decode($json_data, true);
    }
    return [];
}

// Function to save events to JSON file
function save_events_to_file($filename, $events) {
    file_put_contents($filename, json_encode(array_values($events), JSON_PRETTY_PRINT));
}

$filename = 'events.json';

// Read events from JSON file
$events = read_events_from_file($filename);

if (!isset($_SESSION['current_event_id']) || isset($_GET['reset'])) {
    $_SESSION['current_event_id'] = 1;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventId = $_SESSION['current_event_id'];
    
    // Update event data regardless if it exists or not
    $eventData = [
        'Eid' => $eventId,
        'ename' => $_POST['ename'] ?? '',
        'entry_fees' => $_POST['entry_fees'] ?? '',
        'etype' => $_POST['etype'] ?? '',
        'elocation' => $_POST['elocation'] ?? '',
        'edate' => $_POST['edate'] ?? '',
        'etime' => $_POST['etime'] ?? '',
        'slots' => $_POST['slots'] ?? '',
        'total_members' => $_POST['total_members'] ?? ''
    ];
    $events[$eventId] = $eventData;

    // Save data to JSON file
    save_events_to_file($filename, $events);

    // Handle Next button
    if (isset($_POST['next'])) {
        $_SESSION['current_event_id']++;
    }

    // Handle Previous button
    if (isset($_POST['previous']) && $_SESSION['current_event_id'] > 1) {
        $_SESSION['current_event_id']--;
    }
}

// Get the current event ID
$currentEventId = $_SESSION['current_event_id'];

// Check if current event exists in JSON file
$currentEventData = $events[$currentEventId] ?? [
    'Eid' => $currentEventId,
    'ename' => '',
    'entry_fees' => '',
    'etype' => '',
    'elocation' => '',
    'edate' => '',
    'etime' => '',
    'slots' => '',
    'total_members' => ''
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Form</title>
    <style>
        .event-card {
            border: 1px solid #ccc;
            padding: 16px;
            margin-top: 16px;
            border-radius: 8px;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <h1>Event Form - Event No <?= $currentEventId ?></h1>
    <form method="post" action="">
        <input type="text" name="ename" placeholder="Event Name" value="<?= htmlspecialchars($currentEventData['ename']) ?>"><br>
        <input type="number" name="entry_fees" placeholder="Entry Fees" value="<?= htmlspecialchars($currentEventData['entry_fees']) ?>"><br>
        <input type="text" name="etype" placeholder="Event Type" value="<?= htmlspecialchars($currentEventData['etype']) ?>"><br>
        <input type="text" name="elocation" placeholder="Event Location" value="<?= htmlspecialchars($currentEventData['elocation']) ?>"><br>
        <input type="date" name="edate" placeholder="Event Date" value="<?= htmlspecialchars($currentEventData['edate']) ?>"><br>
        <input type="time" name="etime" placeholder="Event Time" value="<?= htmlspecialchars($currentEventData['etime']) ?>"><br>
        <input type="number" name="slots" placeholder="Slots" value="<?= htmlspecialchars($currentEventData['slots']) ?>"><br>
        <input type="number" name="total_members" placeholder="Total Members" value="<?= htmlspecialchars($currentEventData['total_members']) ?>"><br>
        <button type="submit" name="save">Save</button>
        <button type="submit" name="previous">Previous</button>
        <button type="submit" name="next">Next</button>
    </form>
    <br>
    <a href="?reset=true">Start Over</a>

    <div class="event-card">
        <h2>Current Event Details</h2>
        <p><strong>Eid:</strong> <?= htmlspecialchars($currentEventData['Eid']) ?></p>
        <p><strong>Event Name:</strong> <?= htmlspecialchars($currentEventData['ename']) ?></p>
        <p><strong>Entry Fees:</strong> <?= htmlspecialchars($currentEventData['entry_fees']) ?></p>
        <p><strong>Event Type:</strong> <?= htmlspecialchars($currentEventData['etype']) ?></p>
        <p><strong>Event Location:</strong> <?= htmlspecialchars($currentEventData['elocation']) ?></p>
        <p><strong>Event Date:</strong> <?= htmlspecialchars($currentEventData['edate']) ?></p>
        <p><strong>Event Time:</strong> <?= htmlspecialchars($currentEventData['etime']) ?></p>
        <p><strong>Slots:</strong> <?= htmlspecialchars($currentEventData['slots']) ?></p>
        <p><strong>Total Members:</strong> <?= htmlspecialchars($currentEventData['total_members']) ?></p>
    </div>
</body>
</html>
