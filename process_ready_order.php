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

    // Fetch the ordered product details
    $order_query = "SELECT * FROM ordered_products WHERE id = $order_id";
    $result = mysqli_query($conn, $order_query);
    $order = mysqli_fetch_assoc($result);

    if ($order) {
        // Insert into ready_orders table with additional fields
        $products_json = $order['products'];
        $query = "INSERT INTO ready_orders (customer_name, phone, address, products, total_price, amount_paid, debt) VALUES (
                    '{$order['customer_name']}', 
                    '{$order['phone']}', 
                    '{$order['address']}', 
                    '$products_json', 
                    {$order['total_price']}, 
                    {$order['amount_paid']}, 
                    {$order['debt']}
                  )";
        mysqli_query($conn, $query);

        // Optionally: Remove from ordered_products table
        $delete_query = "DELETE FROM ordered_products WHERE id = $order_id";
        mysqli_query($conn, $delete_query);
    }

    // Redirect to ready_orders.php
    header("Location: ready_orders.php");
    exit();
}
?>
