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

$request_id = $_GET['id'];
$request_query = "SELECT * FROM vip_requests WHERE id = $request_id";
$result = mysqli_query($conn, $request_query);
$request = mysqli_fetch_assoc($result);

if (!$request) {
    die("Request not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $products = json_encode($_POST['products']);
    $total_price = $_POST['total_price'];
    $amount_paid = $_POST['amount_paid'];
    
    // Calculate the debt
    $debt = $total_price - $amount_paid;

    $update_query = "UPDATE vip_requests SET customer_name = '$customer_name', products = '$products', total_price = $total_price, amount_paid = $amount_paid, debt = $debt WHERE id = $request_id";
    mysqli_query($conn, $update_query);

    header("Location: vip_requests.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit VIP Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit VIP Request</h1>
        <form method="post">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= htmlspecialchars($request['customer_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="products" class="form-label">Products (JSON)</label>
                <textarea class="form-control" id="products" name="products" rows="5" required><?= htmlspecialchars($request['products']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="total_price" class="form-label">Total Price</label>
                <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" value="<?= htmlspecialchars($request['total_price']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="amount_paid" class="form-label">Amount Paid</label>
                <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" value="<?= htmlspecialchars($request['amount_paid']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Request</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
