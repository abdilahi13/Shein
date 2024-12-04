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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['customer_name'], $_POST['phone'], $_POST['address'], $_POST['products'])) {
    $customer_name = trim(mysqli_real_escape_string($conn, $_POST['customer_name']));
    $phone = trim(mysqli_real_escape_string($conn, $_POST['phone']));
    $address = trim(mysqli_real_escape_string($conn, $_POST['address']));
    $amount_paid = isset($_POST['amount_paid']) ? (float) $_POST['amount_paid'] : 0;

    // Validate required fields
    if (empty($customer_name) || empty($phone) || empty($address)) {
        echo "<script>alert('Customer name, phone, and address are required.'); window.history.back();</script>";
        exit;
    }

    $products = isset($_POST['products']) ? $_POST['products'] : [];
    if (empty($products)) {
        echo "<script>alert('At least one product is required.'); window.history.back();</script>";
        exit;
    }

    $total_price = 0;

    // Process products
    foreach ($products as $index => $product) {
        $product_image = '';
        if (!empty($_FILES['products']['tmp_name'][$index]['image']) && is_uploaded_file($_FILES['products']['tmp_name'][$index]['image'])) {
            $imageName = time() . '_' . basename($_FILES['products']['name'][$index]['image']);
            $targetPath = "uploads/" . $imageName;
            if (move_uploaded_file($_FILES['products']['tmp_name'][$index]['image'], $targetPath)) {
                $products[$index]['image'] = $targetPath;
            }
        }

        if (!isset($product['price']) || !isset($product['quantity']) || empty($product['price']) || empty($product['quantity'])) {
            echo "<script>alert('Each product must have a price and quantity.'); window.history.back();</script>";
            exit;
        }

        $total_price += (float)$product['price'] * (int)$product['quantity'];
    }

    $debt = $total_price - $amount_paid;
    $products_json = json_encode($products);

    // Insert into database based on total price
    if ($total_price >= 250) {
        $vip_query = "INSERT INTO vip_requests (customer_name, phone, address, products, total_price, amount_paid, debt) 
                      VALUES ('$customer_name', '$phone', '$address', '$products_json', $total_price, $amount_paid, $debt)";
        if (!$conn->query($vip_query)) {
            error_log("Error inserting VIP request: " . $conn->error);
            echo "<script>alert('Failed to add VIP request.'); window.history.back();</script>";
            exit;
        }
    } else {
        $order_query = "INSERT INTO customer_orders (customer_name, phone, address, products, total_price, amount_paid, debt) 
                        VALUES ('$customer_name', '$phone', '$address', '$products_json', $total_price, $amount_paid, $debt)";
        if (!$conn->query($order_query)) {
            error_log("Error inserting customer order: " . $conn->error);
            echo "<script>alert('Failed to add customer order.'); window.history.back();</script>";
            exit;
        }
    }
}

// Handle approve/delete/edit actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $order_id = (int) $_POST['order_id'];
    $action = $_POST['action'];

    if ($order_id <= 0) {
        echo "<script>alert('Invalid order ID.'); window.history.back();</script>";
        exit;
    }

    if ($action === 'approve') {
        $order_query = "SELECT * FROM customer_orders WHERE id = $order_id";
        $result = $conn->query($order_query);

        if ($result && $result->num_rows > 0) {
            $order = $result->fetch_assoc();
            $products_json = $conn->real_escape_string($order['products']);

            $insert_query = "INSERT INTO ordered_products (customer_name, phone, address, products, total_price, amount_paid, debt)
                             VALUES ('{$order['customer_name']}', '{$order['phone']}', '{$order['address']}', 
                                     '$products_json', {$order['total_price']}, {$order['amount_paid']}, {$order['debt']})";

            if ($conn->query($insert_query)) {
                $delete_query = "DELETE FROM customer_orders WHERE id = $order_id";
                $conn->query($delete_query);
                echo "<script>alert('Order approved and moved to ordered products.'); window.location.href='customer_orders.php';</script>";
            } else {
                echo "<script>alert('Error moving order to ordered products.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Order not found.'); window.history.back();</script>";
        }
    } elseif ($action === 'delete') {
        $delete_query = "DELETE FROM customer_orders WHERE id = $order_id";
        if ($conn->query($delete_query)) {
            echo "<script>alert('Order deleted successfully.'); window.location.href='customer_orders.php';</script>";
        } else {
            echo "<script>alert('Error deleting order.'); window.history.back();</script>";
        }
    }
}

// Fetch orders
$orders_query = "SELECT * FROM customer_orders";
$orders = $conn->query($orders_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .btn-group {
            display: flex;
            justify-content: end;
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
        <div class="container mt-4">
            <h1 class="mb-4">Customer Orders</h1>
            <form method="post" action="customer_orders.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div id="productFields" class="mb-3">
                    <div class="product mb-3">
                        <label class="form-label">Product 1</label>
                        <div class="row g-2">
                            <div class="col-md">
                                <input type="file" class="form-control" name="products[0][image]" accept="image/*" required>
                                <div id="preview-0" class="preview mt-2"><img src="" alt="Preview" style="display: none;"></div>
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" name="products[0][description]" placeholder="Description" required>
                            </div>
                            <div class="col-md">
                                <input type="number" class="form-control" name="products[0][price]" placeholder="Price" step="0.01" required>
                            </div>
                            <div class="col-md">
                                <input type="number" class="form-control" name="products[0][quantity]" placeholder="Quantity" required>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mb-3" onclick="addProductField()">Add Another Product</button>
                <div class="mb-3">
                    <label for="amount_paid" class="form-label">Amount Paid</label>
                    <input type="number" class="form-control" id="amount_paid" name="amount_paid" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit Order</button>
            </forms
            <h2 class="mt-5">Orders</h2>
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
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)) { ?>
                        <tr>
                            <form method="post" action="customer_orders.php">
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
                                        <div class="preview">No Image</div>
                                    <?php endif; ?>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
                                    <p><strong>Price:</strong> <?= htmlspecialchars($product['price']) ?></p>
                                    <p><strong>Quantity:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                                </div>
                                <hr>
                                <?php
                            }
                            ?>
                            </td>
                            <td><?= htmlspecialchars($order['total_price']) ?></td>
                            <td><?= htmlspecialchars($order['amount_paid']) ?></td>
                            <td><?= htmlspecialchars($order['debt']) ?></td>
                            <td>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
            <button type="submit" name="action" value="edit" class="btn btn-primary btn-sm">Edit</button>
            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
        </td>
    </form>
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
    </div>
     <script>
        // Function to set the image in the modal
        function setImageModal(imageUrl) {
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageUrl;
        }
    </script>
    </div>

   <script>
    function addProductField() {
        const productFields = document.getElementById('productFields');
        const productCount = productFields.children.length; // Adjust count dynamically
        const newField = `
            <div class="product mb-3">
                <label class="form-label">Product ${productCount + 1}</label>
                <div class="row g-2">
                    <div class="col-md">
                        <input type="file" class="form-control" name="products[${productCount}][image]" accept="image/*" required>
                        <div id="preview-${productCount}" class="preview mt-2"><img src="" alt="Preview" style="display: none;"></div>
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
            </div>
        `;
        productFields.insertAdjacentHTML('beforeend', newField); // Append new field
    }
</script>

</body>
</html>
