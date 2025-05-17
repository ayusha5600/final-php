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

// Define decryption function
function Decrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use the first 16 bytes of the key as the IV
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

try {
    // Get input data from JSON
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Extract username and password
    $username = mysqli_real_escape_string($conn, $data['Username']);
    $user_password = mysqli_real_escape_string($conn, $data['UserPassword']);

    // Verify user login
    $query = "SELECT ID, Password FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows != 1) {
        echo json_encode(array("error" => "Invalid Username or Password"));
        exit;
    }

    $row = $result->fetch_assoc();
    $userid = $row['ID'];
    $hashed_password = $row['Password'];

    if (!password_verify($user_password, $hashed_password)) {
        echo json_encode(array("error" => "Invalid Username or Password"));
        exit;
    }

    // Fetch passwords if login is successful
    $query = "SELECT * FROM passwords WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $passtitle = $row["PassTitle"];
            $encrypted_pass = $row["Pass"];
            $pass = Decrypt($encrypted_pass, $user_password);
            $data[] = array("PassTitle" => $passtitle, "Password" => $pass);
        }
    }

    echo json_encode($data);

} catch (Exception $e) {
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
?>
