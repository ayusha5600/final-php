<?php
// Database connection details
$hostname = "localhost";
$username = "root";
$password = "";
$database = "Book";

// Connecting to MySQL database
$conn = mysqli_connect($hostname, $username, $password, $database);

// Check if connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set header to return JSON response with UTF-8 encoding
header('Content-Type: application/json; charset=utf-8');

// Function to decrypt password using AES-256-CBC
function Decrypt($data, $key) {
    // Generate IV from key
    $iv = substr(hash('sha256', $key), 0, 16); // Taking first 16 bytes of hashed key as IV
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

try {
    // Read raw input and decode JSON to array
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Get username and password from request
    $username = mysqli_real_escape_string($conn, $data['Username']);
    $user_password = mysqli_real_escape_string($conn, $data['UserPassword']);

    // Check if user exists
    $query = "SELECT ID, Password FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If no user found, send error
    if ($result->num_rows != 1) {
        echo json_encode(array("error" => "Invalid Username or Password"));
        exit;
    }

    // Get user info
    $row = $result->fetch_assoc();
    $userid = $row['ID'];
    $hashed_password = $row['Password'];

    // Verify password
    if (!password_verify($user_password, $hashed_password)) {
        echo json_encode(array("error" => "Invalid Username or Password"));
        exit;
    }

    // If login successful, fetch saved passwords
    $query = "SELECT * FROM passwords WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();

    // Loop through each password entry and decrypt it
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $passtitle = $row["PassTitle"];
            $encrypted_pass = $row["Pass"];
            $pass = Decrypt($encrypted_pass, $user_password); // Decrypt using user password
            $data[] = array("PassTitle" => $passtitle, "Password" => $pass);
        }
    }

    // Send final JSON response
    echo json_encode($data);

} catch (Exception $e) {
    // If any error occurs, send error response
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

// Close DB connection
mysqli_close($conn);
?>
