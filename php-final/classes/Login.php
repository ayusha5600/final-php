<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "Book";

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set proper content type for JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $field1 = mysqli_real_escape_string($conn, $data['Username']);
    $field2 = mysqli_real_escape_string($conn, $data['Password']);

    $query = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $field1);
    $stmt->execute();
    $result = $stmt->get_result();

    $hashed_password = "";
    $userid = 0;

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['Password'];
        $userid = $row['ID'];
    }

    if (!empty($hashed_password) && password_verify($field2, $hashed_password)) {
        $response = array(
            "message" => "Logged in successfully",
            "detail" => $userid
        );
        echo json_encode($response);
    } else {
        $response = array("error" => "Login Attempt Failed");
        echo json_encode($response);
    }

} catch (Exception $e) {
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
?>
