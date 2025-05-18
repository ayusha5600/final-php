<?php
$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "book";

// Connect to MySQL
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database Connection Failed"]));
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents("php://input"), true);

    $username = $conn->real_escape_string($input['Username']);
    $userPassword = $conn->real_escape_string($input['UserPassword']);

    // Authenticate User
    $stmt = $conn->prepare("SELECT ID, Password FROM users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo json_encode(["error" => "Username not found"]);
        exit;
    }

    $user = $result->fetch_assoc();
    $userId = $user['ID'];

    if (!password_verify($userPassword, $user['Password'])) {
        echo json_encode(["error" => "Incorrect password"]);
        exit;
    }

    $action = $input['Action'] ?? '';

    switch ($action) {
        case 'Save':
            handleSave($conn, $input, $userId);
            break;
        case 'Update':
            handleUpdate($conn, $input, $userId);
            break;
        case 'Delete':
            handleDelete($conn, $input, $userId);
            break;
        default:
            echo json_encode(["error" => "Unknown action"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Server error", "message" => $e->getMessage()]);
}

$conn->close();

// Function to save password
function handleSave($conn, $data, $userId) {
    $title = $conn->real_escape_string($data['PassTitle']);
    $password = $conn->real_escape_string($data['Password']);
    $encrypted = encryptPassword($password, $data['UserPassword']);

    $stmt = $conn->prepare("INSERT INTO passwords (UserID, PassTitle, Pass) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $title, $encrypted);
    $stmt->execute();

    echo json_encode($stmt->affected_rows > 0
        ? ["message" => "Password saved"]
        : ["error" => "Save failed"]);
}

// Function to update password
function handleUpdate($conn, $data, $userId) {
    $title = $conn->real_escape_string($data['PassTitle']);
    $password = $conn->real_escape_string($data['Password']);
    $encrypted = encryptPassword($password, $data['UserPassword']);

    $stmt = $conn->prepare("UPDATE passwords SET Pass = ? WHERE UserID = ? AND PassTitle = ?");
    $stmt->bind_param("sis", $encrypted, $userId, $title);
    $stmt->execute();

    echo json_encode($stmt->affected_rows > 0
        ? ["message" => "Password updated"]
        : ["error" => "Update failed"]);
}

// Function to delete password
function handleDelete($conn, $data, $userId) {
    $title = $conn->real_escape_string($data['PassTitle']);

    $stmt = $conn->prepare("DELETE FROM passwords WHERE UserID = ? AND PassTitle = ?");
    $stmt->bind_param("is", $userId, $title);
    $stmt->execute();

    echo json_encode($stmt->affected_rows > 0
        ? ["message" => "Password deleted"]
        : ["error" => "Delete failed"]);
}

// Encryption logic
function encryptPassword($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16);
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}
?>
