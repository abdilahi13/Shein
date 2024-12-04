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

// Handle button actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $order_id = (int) $_POST['order_id'];
    $action = $_POST['action'];

    if ($action === 'ready') {
        // Mark as ready (move to ready_orders table)
        $order_query = "SELECT * FROM ordered_products WHERE id = $order_id";
        $result = $conn->query($order_query);
        if ($result && $result->num_rows > 0) {
            $order = $result->fetch_assoc();

            // Insert into ready_orders table
            $insert_query = "INSERT INTO ready_orders (customer_name, phone, address, products, total_price, amount_paid, debt) 
                             VALUES (
                                 '{$order['customer_name']}', 
                                 '{$order['phone']}', 
                                 '{$order['address']}', 
                                 '{$order['products']}', 
                                 {$order['total_price']}, 
                                 {$order['amount_paid']}, 
                                 {$order['debt']}
                             )";
            $conn->query($insert_query);

            // Delete from ordered_products
            $delete_query = "DELETE FROM ordered_products WHERE id = $order_id";
            $conn->query($delete_query);

            echo "<script>alert('Order marked as ready and moved to ready orders.'); window.location.href='ordered_products.php';</script>";
        }
    } elseif ($action === 'delete') {
        // Delete the order
        $delete_query = "DELETE FROM ordered_products WHERE id = $order_id";
        if ($conn->query($delete_query)) {
            echo "<script>alert('Order deleted successfully.'); window.location.href='ordered_products.php';</script>";
        } else {
            echo "<script>alert('Error deleting order.');</script>";
        }
    }
}

// Fetch ordered products
$ordered_products = mysqli_query($conn, "SELECT * FROM ordered_products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordered Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .preview {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid #dee2e6;
            color: #ffffff;
        }
        .sidebar h4 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 10px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.2s, padding-left 0.2s;
        }
        .sidebar a:hover {
            background-color: #495057;
            padding-left: 20px;
        }
        .sidebar a i {
            font-size: 18px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>
            <img src="dist/img/shein.jfif" class="brand-image img-circle elevation-3" 
                style="opacity: .8; width: 30px; height: 30px; vertical-align: middle;"> 
            Shein Logistics
        </h4>
        <a href="customer_orders.php"><i class="fas fa-shopping-cart"></i>Dalabka macmiisha</a>
        <a href="vip_requests.php"><i class="fas fa-star"></i>Dad khaasa </a>
        <a href="ordered_products.php"><i class="fas fa-box"></i>Alaabihii la dalbay</a>
        <a href="ready_orders.php"><i class="fas fa-check"></i>Alaabtii timid</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="mb-4">Ordered Products</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Products</th>
                    <th>Total Price</th>
                    <th>Amount Paid</th>
                    <th>Debt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($ordered_products)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['phone']) ?></td>
                        <td><?= htmlspecialchars($order['address']) ?></td>
                        <td>
                            <?php 
                            $products = json_decode($order['products'], true);
                            foreach ($products as $product) {
                                ?>
                                <div>
                                    <?php if (!empty($product['image'])): ?>
                                        <div class="preview">
                                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
                                        </div>
                                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="setImageModal('<?= htmlspecialchars($product['image']) ?>')">View Image</button>
                                    <?php else: ?>
                                        <p>No Image</p>
                                    <?php endif; ?>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
                                    <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
                                    <p><strong>Quantity:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                                </div>
                                <hr>
                                <?php
                            }
                            ?>
                        </td>
                        <td>$<?= number_format($order['total_price'], 2) ?></td>
                        <td>$<?= number_format($order['amount_paid'], 2) ?></td>
                        <td>$<?= number_format($order['debt'], 2) ?></td>
                        <td>
                            <form method="post" action="ordered_products.php" class="d-inline">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" name="action" value="ready" class="btn btn-success btn-sm">Ready</button>
                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Viewing Image -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Product Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Product Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to set the image in the modal
        function setImageModal(imageUrl) {
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageUrl;
        }
    </script>
</body>
</html>
