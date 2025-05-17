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

function Encrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use the first 16 bytes of the key as the IV
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

function Decrypt($data, $key) {
    $iv = substr(hash('sha256', $key), 0, 16); // Use the first 16 bytes of the key as the IV
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $field1 = mysqli_real_escape_string($conn, $data['Username']);
    $field2 = mysqli_real_escape_string($conn, $data['Password']);
    $field3 = mysqli_real_escape_string($conn, $data['PasswordNew']);

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
        $hashed_new_password = password_hash($field3, PASSWORD_BCRYPT);

        // Update user's password
        $query_update_user = "UPDATE Users SET Password = ? WHERE Username = ?";
        $stmt_update_user = $conn->prepare($query_update_user);
        $stmt_update_user->bind_param("ss", $hashed_new_password, $field1);
        $stmt_update_user->execute();

        // Retrieve and update encrypted passwords
        $query_get_passwords = "SELECT PassTitle, Pass FROM passwords WHERE UserID = ?";
        $stmt_get_passwords = $conn->prepare($query_get_passwords);
        $stmt_get_passwords->bind_param("i", $userid);
        $stmt_get_passwords->execute();
        $result_passwords = $stmt_get_passwords->get_result();

        while ($row = $result_passwords->fetch_assoc()) {
            $passtitle = $row["PassTitle"];
            $encrypted_pass = $row["Pass"];
            $pass = Decrypt($encrypted_pass, $field2);
            $pass = Encrypt($pass, $field3);

            // Update password with new encryption
            $query_update_pass = "UPDATE passwords SET Pass = ? WHERE PassTitle = ? AND UserID = ?";
            $stmt_update_pass = $conn->prepare($query_update_pass);
            $stmt_update_pass->bind_param("ssi", $pass, $passtitle, $userid);
            $stmt_update_pass->execute();
        }

        $response = array(
            "message" => "Password Updated successfully",
            "detail" => $userid
        );
        echo json_encode($response);
        
    } else {
        $response = array("error" => "Password Update Failed");
        echo json_encode($response);
    }

} catch (Exception $e) {
    $response = array("error" => "Exception occurred", "message" => $e->getMessage());
    echo json_encode($response);
}

mysqli_close($conn);
?>
