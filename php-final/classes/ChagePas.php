<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "Book";

// Connect to the MySQL database
$conn = mysqli_connect($hostname, $username, $password, $database);

// If connection fails, stop and show error
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set content type to JSON for API response
header('Content-Type: application/json; charset=utf-8');

// Function to encrypt data using AES-256-CBC
function Encrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use first 16 bytes of hashed key as IV
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

// Function to decrypt data using AES-256-CBC
function Decrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use first 16 bytes of hashed key as IV
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

try {
    // Read the raw JSON input and decode it into a PHP array
    $data = json_decode(file_get_contents("php://input"), true);

    // Get the username, old password, and new password from request
    $field1 = mysqli_real_escape_string($conn, $data['Username']);
    $field2 = mysqli_real_escape_string($conn, $data['Password']);
    $field3 = mysqli_real_escape_string($conn, $data['PasswordNew']);

    // Check if the user exists
    $query = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $field1);
    $stmt->execute();
    $result = $stmt->get_result();

    $hashed_password = "";
    $userid = 0;

    // If user found, store the hashed password and user ID
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['Password'];
        $userid = $row['ID'];
    }

    // Check if the old password is correct
    if (!empty($hashed_password) && password_verify($field2, $hashed_password)) {
        // Hash the new password
        $hashed_new_password = password_hash($field3, PASSWORD_BCRYPT);

        // Update the user's password in the database
        $query_update_user = "UPDATE Users SET Password = ? WHERE Username = ?";
        $stmt_update_user = $conn->prepare($query_update_user);
        $stmt_update_user->bind_param("ss", $hashed_new_password, $field1);
        $stmt_update_user->execute();

        // Get all saved encrypted passwords for this user
        $query_get_passwords = "SELECT PassTitle, Pass FROM passwords WHERE UserID = ?";
        $stmt_get_passwords = $conn->prepare($query_get_passwords);
        $stmt_get_passwords->bind_param("i", $userid);
        $stmt_get_passwords->execute();
        $result_passwords = $stmt_get_passwords->get_result();

        // Loop through each password, decrypt with old password, encrypt with new password, and update
        while ($row = $result_passwords->fetch_assoc()) {
            $passtitle = $row["PassTitle"];
            $encrypted_pass = $row["Pass"];
            $pass = Decrypt($encrypted_pass, $field2);     // Decrypt using old password
            $pass = Encrypt($pass, $field3);               // Encrypt again using new password

            // Update the password in the database
            $query_update_pass = "UPDATE passwords SET Pass = ? WHERE PassTitle = ? AND UserID = ?";
            $stmt_update_pass = $conn->prepare($query_update_pass);
            $stmt_update_pass->bind_param("ssi", $pass, $passtitle, $userid);
            $stmt_update_pass->execute();
        }

        // Success response
        $response = array(
            "message" => "Password Updated successfully",
            "detail" => $userid
        );
        echo json_encode($response);
        
    } else {
        // Old password incorrect or user not found
        $response = array("error" => "Password Update Failed");
        echo json_encode($response);
    }

} catch (Exception $e) {
    // Handle unexpected errors
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

mysqli_close($conn);
?>
