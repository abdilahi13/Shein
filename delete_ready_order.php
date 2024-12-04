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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];

    // Delete from ready_orders table
    $delete_query = "DELETE FROM ready_orders WHERE id = $order_id";
    mysqli_query($conn, $delete_query);

    // Redirect to ready_orders.php
    header("Location: ready_orders.php");
    exit();
}
?>
