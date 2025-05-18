<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "Book";

// Connect to the database
$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Function to decrypt saved passwords
function Decrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use the first 16 bytes of the key as IV
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

try {
    // Handle both JSON and form POST requests
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $data = json_decode(file_get_contents("php://input"), true);
        $username = isset($data['Username']) ? $data['Username'] : '';
        $user_password = isset($data['UserPassword']) ? $data['UserPassword'] : '';
    } else {
        $username = isset($_POST['Username']) ? $_POST['Username'] : '';
        $user_password = isset($_POST['UserPassword']) ? $_POST['UserPassword'] : '';
    }

    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, $username);
    $user_password = mysqli_real_escape_string($conn, $user_password);

    // Check if the user exists
    $query = "SELECT ID, Password FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If no user found
    if ($result->num_rows != 1) {
        echo json_encode(array("error" => "Login Attempt Failed"));
        exit;
    }

    $row = $result->fetch_assoc();
    $userid = $row['ID'];
    $hashed_password = $row['Password'];

    // Check password match
    if (!password_verify($user_password, $hashed_password)) {
        echo json_encode(array("error" => "Login Attempt Failed"));
        exit;
    }

    // Get all saved passwords for this user
    $query = "SELECT PassTitle, Pass FROM passwords WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();

    while ($row = $result->fetch_assoc()) {
        $passTitle = $row["PassTitle"];
        $encrypted_pass = $row["Pass"];
        $decrypted_pass = Decrypt($encrypted_pass, $user_password);
        $data[] = array(
            "PassTitle" => $passTitle,
            "Password" => $decrypted_pass
        );
    }

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(array(
        "error" => "Exception occurred",
        "message" => $e->getMessage()
    ));
}

// Close connection
mysqli_close($conn);
?>
