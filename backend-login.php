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
if (!isset($data['email']) || !isset($data['password'])) {
    sendError("Missing email or password");
}

$email = sanitizeInput($data['email']);
$password = $data['password'];

try {
    // Prepare and execute query to find user
    $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Store user information in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Return user information (excluding sensitive data)
        echo json_encode([
            "message" => "Login successful",
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "role" => $user['role']
            ]
        ]);
    } else {
        sendError("Invalid email or password", 401);
    }
} catch (PDOException $e) {
    sendError("Login failed: " . $e->getMessage(), 500);
}
