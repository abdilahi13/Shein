<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "harhub";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];

    // Fetch the VIP request details using a prepared statement
    $stmt = $conn->prepare("SELECT * FROM vip_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        // Insert into ordered_products using prepared statement
        $products_json = $request['products'];
        $insert_stmt = $conn->prepare("INSERT INTO ordered_products (customer_name, phone, address, products, total_price, amount_paid, debt) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssddd", 
            $request['customer_name'], 
            $request['phone'], 
            $request['address'], 
            $products_json, 
            $request['total_price'], 
            $request['amount_paid'], 
            $request['debt']);
        $insert_stmt->execute();

        // Optionally, delete from vip_requests table
        $delete_stmt = $conn->prepare("DELETE FROM vip_requests WHERE id = ?");
        $delete_stmt->bind_param("i", $request_id);
        $delete_stmt->execute();
    }

    // Redirect to ordered_products.php
    header('Location: ordered_products.php');
    exit;
}
?>
