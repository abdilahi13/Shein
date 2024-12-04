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

// Fetch ready orders
$query = "SELECT * FROM ready_orders";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch data as an associative array
$ready_orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ready Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            overflow-x: hidden;
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
            text-align: center;
            color: #ffffff;
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
        .preview {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        <h1 class="mb-4">Ready Orders</h1>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Products</th>
                    <th>Total Price</th>
                    <th>Amount Paid</th>
                    <th>Debt</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ready_orders)) { ?>
                    <?php foreach ($ready_orders as $order) { ?>
                        <tr>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                            <td><?= htmlspecialchars($order['address']) ?></td>
                            <td>
                                <?php 
                                $products = json_decode($order['products'], true);
                                if (is_array($products)) {
                                    foreach ($products as $product) {
                                        ?>
                                        <div>
                                            <?php if (!empty($product['image'])): ?>
                                                <div class="preview">
                                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
                                                </div>
                                                <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="setImageModal('<?= htmlspecialchars($product['image']) ?>')">View Image</button>
                                            <?php else: ?>
                                                <div class="preview">No Image</div>
                                            <?php endif; ?>
                                            <p><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
                                            <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
                                            <p><strong>Quantity:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                                        </div>
                                        <hr>
                                        <?php
                                    }
                                } else {
                                    echo "<p>No products available.</p>";
                                }
                                ?>
                            </td>
                            <td>$<?= number_format($order['total_price'], 2) ?></td>
                            <td>$<?= number_format($order['amount_paid'], 2) ?></td>
                            <td>$<?= number_format($order['debt'], 2) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                            <td>
                                <form action="delete_ready_order.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9">No ready orders found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Image Viewer Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Product Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Product Image">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to load the image into the modal
        function setImageModal(imageUrl) {
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageUrl;
        }
    </script>
</body>
</html>
