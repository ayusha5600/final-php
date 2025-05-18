<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "book";

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set proper content type for JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $username = mysqli_real_escape_string($conn, $data['Username']);
    $user_password = mysqli_real_escape_string($conn, $data['UserPassword']);

    // Verify user login
    $query = "SELECT ID, Password FROM users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows != 1) {
        echo json_encode(array("error" => "Invalid Username"));
        exit;
    }

    $row = $result->fetch_assoc();
    $userid = $row['ID'];
    $hashed_password = $row['Password'];

    if (!password_verify($user_password, $hashed_password)) {
        echo json_encode(array("error" => "Invalid Password"));
        exit;
    }

    // Determine action: save, update, or delete
    $action = isset($data['Action']) ? $data['Action'] : '';
    switch ($action) {
        case 'Save':
            savePassword($conn, $data, $userid);
            break;
        case 'Update':
            updatePassword($conn, $data, $userid);
            break;
        case 'Delete':
            deletePassword($conn, $data, $userid);
            break;
        default:
            echo json_encode(array("error" => "Invalid Action"));
            break;
    }

} catch (Exception $e) {
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);

function savePassword($conn, $data, $userid) {
    $pass_title = mysqli_real_escape_string($conn, $data['PassTitle']);
    $password = mysqli_real_escape_string($conn, $data['Password']);

    // Encrypt the password
    $encrypted_pass = Encrypt($password, $data['UserPassword']);

    // Insert new password into the database
    $query = "INSERT INTO passwords (UserID, PassTitle, Pass) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $userid, $pass_title, $encrypted_pass);
    $result = $stmt->execute();

    if ($result) {
        $response = array("message" => "Password saved successfully");
    } else {
        $response = array("error" => "Failed to save password");
    }
    echo json_encode($response);
}

function updatePassword($conn, $data, $userid) {
    $pass_title = mysqli_real_escape_string($conn, $data['PassTitle']);
    $password = mysqli_real_escape_string($conn, $data['Password']);

    // Encrypt the new password
    $encrypted_pass = Encrypt($password, $data['UserPassword']);

    // Update password in the database
    $query = "UPDATE passwords SET Pass = ? WHERE UserID = ? AND PassTitle = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $encrypted_pass, $userid, $pass_title);
    $result = $stmt->execute();

    if ($result) {
        $response = array("message" => "Password updated successfully");
    } else {
        $response = array("error" => "Failed to update password");
    }
    echo json_encode($response);
}

function deletePassword($conn, $data, $userid) {
    $pass_title = mysqli_real_escape_string($conn, $data['PassTitle']);

    // Delete password from the database
    $query = "DELETE FROM passwords WHERE UserID = ? AND PassTitle = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userid, $pass_title);
    $result = $stmt->execute();

    if ($result) {
        $response = array("message" => "Password deleted successfully");
    } else {
        $response = array("error" => "Failed to delete password");
    }
    echo json_encode($response);
}

function Encrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use the first 16 bytes of the key as the IV
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}
?>
