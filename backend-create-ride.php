<?php
require_once 'db.php';
session_start();

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Only POST method is allowed", 405);
}

// Get input data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
$requiredFields = [
    'passenger_id', 'driver_id', 'pickup_location', 
    'dropoff_location', 'fare'
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        sendError("Missing required field: $field");
    }
}

// Sanitize inputs
$passenger_id = filter_var($data['passenger_id'], FILTER_VALIDATE_INT);
$driver_id = filter_var($data['driver_id'], FILTER_VALIDATE_INT);
$pickup_location = sanitizeInput($data['pickup_location']);
$dropoff_location = sanitizeInput($data['dropoff_location']);
$fare = filter_var($data['fare'], FILTER_VALIDATE_FLOAT);

// Validate sanitized inputs
if ($passenger_id === false || $driver_id === false || $fare === false) {
    sendError("Invalid numeric input");
}

// Validate location lengths
if (strlen($pickup_location) > 255 || strlen($dropoff_location) > 255) {
    sendError("Location names are too long");
}

try {
    // Check if passenger and driver exist
    $userCheckQuery = "SELECT COUNT(*) as count FROM users WHERE id IN (?, ?)";
    $userCheckStmt = $pdo->prepare($userCheckQuery);
    $userCheckStmt->execute([$passenger_id, $driver_id]);
    $userCount = $userCheckStmt->fetch()['count'];

    if ($userCount != 2) {
        sendError("Invalid passenger or driver ID");
    }

    // Insert ride
    $query = "INSERT INTO rides (passenger_id, driver_id, pickup_location, dropoff_location, fare) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $passenger_id, 
        $driver_id, 
        $pickup_location, 
        $dropoff_location, 
        $fare
    ]);

    // Return success response with ride details
    echo json_encode([
        "message" => "Ride created successfully",
        "ride_id" => $pdo->lastInsertId(),
        "details" => [
            "passenger_id" => $passenger_id,
            "driver_id" => $driver_id,
            "pickup" => $pickup_location,
            "dropoff" => $dropoff_location,
            "fare" => $fare
        ]
    ]);
} catch (PDOException $e) {
    sendError("Ride creation failed: " . $e->getMessage(), 500);
}
