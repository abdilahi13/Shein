<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change this to your DB username
$password = ""; // Change this to your DB password
$dbname = "harhub"; // Change this to your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the request ID is an integer to prevent SQL injection
$request_id = intval($_GET['id']);

if ($request_id) {
    $delete_query = "DELETE FROM vip_requests WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    if ($stmt) {
        $stmt->bind_param("i", $request_id);
        if ($stmt->execute()) {
            // Redirect to VIP requests page after deletion
            header("Location: vip_requests.php");
            exit();
        } else {
            echo "Error deleting request: " . $stmt->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Invalid request ID.";
}

$conn->close();
?>
