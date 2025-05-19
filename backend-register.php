<?php
require_once 'db.php';

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Only POST method is allowed", 405);
}

// Get input data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
    sendError("Missing required registration fields");
}

$name = sanitizeInput($data['name']);
$email = sanitizeInput($data['email']);
$password = $data['password'];
$role = sanitizeInput($data['role']);

// Validate email
if (!validateEmail($email)) {
    sendError("Invalid email format");
}

// Check password strength
if (strlen($password) < 8) {
    sendError("Password must be at least 8 characters long");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$email]);
    
    if ($checkStmt->rowCount() > 0) {
        sendError("Email already registered");
    }

    // Insert new user
    $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$name, $email, $hashedPassword, $role]);

    // Return success response
    echo json_encode([
        "message" => "User registered successfully",
        "user_id" => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    sendError("Registration failed: " . $e->getMessage(), 500);
}
