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

$order_id = $_GET['id'];

// Sanitize input
$order_id = intval($order_id);

$delete_query = "DELETE FROM ordered_products WHERE id = $order_id";
mysqli_query($conn, $delete_query);

header("Location: ordered_products.php");
exit();
?>
