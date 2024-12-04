<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "harhub";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $query = "SELECT * FROM customer_orders WHERE id = $order_id";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $products = $_POST['products'];
    $total_price = 0;

    // Calculate total price
    foreach ($products as $product) {
        $total_price += $product['price'] * $product['quantity'];
    }

    $amount_paid = $_POST['amount_paid'];
    $debt = $total_price - $amount_paid;

    // Update customer_orders table
    $products_json = json_encode($products); // Convert products array to JSON for storage
    $update_query = "UPDATE customer_orders SET customer_name = '$customer_name', products = '$products_json', total_price = $total_price, amount_paid = $amount_paid, debt = $debt WHERE id = $order_id";
    mysqli_query($conn, $update_query);

    // Check if the order should be moved to VIP requests
    if ($total_price >= 250) {
        // Insert into VIP requests table
        $vip_query = "INSERT INTO vip_requests (customer_name, products, total_price, amount_paid, debt) VALUES ('$customer_name', '$products_json', $total_price, $amount_paid, $debt)";
        mysqli_query($conn, $vip_query);

        // Redirect to VIP requests page
        header("Location: vip_requests.php");
        exit();
    } else {
        // Redirect to orders page
        header("Location: customer_orders.php");
        exit();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Link to custom stylesheet -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <h4 class="text-center">Admin Dashboard</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="customer_orders.php">Customer Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="vip_requests.php">VIP Requests</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">Settings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <h1 class="mt-4">Edit Order</h1>
                <form method="post" action="edit_order.php?id=<?= htmlspecialchars($order_id) ?>">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= htmlspecialchars($order['customer_name']) ?>" required>
                    </div>
                    <div id="productFields" class="mb-3">
                        <?php
                        $products = json_decode($order['products'], true);
                        foreach ($products as $index => $product) {
                            ?>
                            <div class="product mb-3">
                                <label class="form-label">Product <?= $index + 1 ?></label>
                                <div class="row g-2">
                                    <div class="col-md">
                                        <input type="text" class="form-control" name="products[<?= $index ?>][link]" value="<?= htmlspecialchars($product['link']) ?>" placeholder="Product Link" required>
                                    </div>
                                    <div class="col-md">
                                        <input type="text" class="form-control" name="products[<?= $index ?>][description]" value="<?= htmlspecialchars($product['description']) ?>" placeholder="Description" required>
                                    </div>
                                    <div class="col-md">
                                        <input type="number" class="form-control" name="products[<?= $index ?>][price]" value="<?= htmlspecialchars($product['price']) ?>" placeholder="Price" step="0.01" required>
                                    </div>
                                    <div class="col-md">
                                        <input type="number" class="form-control" name="products[<?= $index ?>][quantity]" value="<?= htmlspecialchars($product['quantity']) ?>" placeholder="Quantity" required>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" onclick="addProductField()">Add Another Product</button>
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid</label>
                        <input type="number" class="form-control" id="amount_paid" name="amount_paid" value="<?= htmlspecialchars($order['amount_paid']) ?>" step="0.01" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Order</button>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        function addProductField() {
            let productCount = document.querySelectorAll('.product').length;
            let newField = document.createElement('div');
            newField.className = 'product mb-3';
            newField.innerHTML = `
                <label class="form-label">Product ${productCount + 1}</label>
                <div class="row g-2">
                    <div class="col-md">
                        <input type="text" class="form-control" name="products[${productCount}][link]" placeholder="Product Link" required>
                    </div>
                    <div class="col-md">
                        <input type="text" class="form-control" name="products[${productCount}][description]" placeholder="Description" required>
                    </div>
                    <div class="col-md">
                        <input type="number" class="form-control" name="products[${productCount}][price]" placeholder="Price" step="0.01" required>
                    </div>
                    <div class="col-md">
                        <input type="number" class="form-control" name="products[${productCount}][quantity]" placeholder="Quantity" required>
                    </div>
                </div>
            `;
            document.getElementById('productFields').appendChild(newField);
        }
    </script>
</body>
</html>
