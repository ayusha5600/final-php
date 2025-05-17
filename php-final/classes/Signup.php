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
    $password = mysqli_real_escape_string($conn, $data['Password']);

    // Check if the username already exists
    $query = "SELECT * FROM users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = array("error" => "Account Already Exists");
        echo json_encode($response);
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new user into the database
        $query = "INSERT INTO users (Username, Password) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $hashed_password);
        $result = $stmt->execute();

        if ($result) {
            $response = array("message" => "Signed Up Successfully");
            echo json_encode($response);
        } else {
            $response = array("error" => "Sign-Up Attempt Failed");
            echo json_encode($response);
        }
    }

} catch (Exception $e) {
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
?>
