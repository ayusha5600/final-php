<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "Book";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

header('Content-Type: application/json; charset=utf-8');

// Function to decrypt data
function Decrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use first 16 bytes of key as IV
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

try {
    // Get data from JSON or from form POST
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $username = isset($data['Username']) ? $data['Username'] : '';
        $user_password = isset($data['UserPassword']) ? $data['UserPassword'] : '';
    } else {
        $username = isset($_POST['Username']) ? $_POST['Username'] : '';
        $user_password = isset($_POST['UserPassword']) ? $_POST['UserPassword'] : '';
    }

    // Escape inputs for security
    $username = mysqli_real_escape_string($conn, $username);
    $user_password = mysqli_real_escape_string($conn, $user_password);

    // Check if username exists
    $query = "SELECT ID, Password FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows != 1) {
        echo json_encode(array("error" => "Login Attempt Failed"));
        exit;
    }

    $row = $result->fetch_assoc();
    $userid = $row['ID'];
    $hashed_password = $row['Password'];

    // Verify password
    if (!password_verify($user_password, $hashed_password)) {
        echo json_encode(array("error" => "Login Attempt Failed"));
        exit;
    }

    // If login is successful, fetch user's saved passwords
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

mysqli_close($conn);
?>
