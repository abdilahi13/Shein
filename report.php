<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "harhub";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get date range from the form or set default values
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Revenue Report Query
$revenue_query = "
    SELECT 
        SUM(quantity * price) AS total_revenue
    FROM ordered_products
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$revenue_result = $conn->query($revenue_query);
$revenue_data = $revenue_result->fetch_assoc();

// Sales Report Query
$sales_query = "
    SELECT 
        product_name,
        SUM(quantity) AS total_quantity,
        SUM(quantity * price) AS total_revenue
    FROM ordered_products
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY product_name
    ORDER BY total_revenue DESC";
$sales_result = $conn->query($sales_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h3 {
            margin-top: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">Sales Report</h1>
        <p class="text-center">Period: <?php echo $start_date; ?> to <?php echo $end_date; ?></p>

        <!-- Date Filter Form -->
        <form method="GET" action="" class="row g-3 mb-4 no-print">
            <div class="col-md-5">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>

        <!-- Revenue Summary -->
        <h3>Revenue Summary</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$<?php echo number_format($revenue_data['total_revenue'] ?? 0, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Sales Details -->
        <h3>Sales Details</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Product Name</th>
                    <th>Total Quantity</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($sales_result && $sales_result->num_rows > 0) { ?>
                    <?php while ($row = $sales_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['total_quantity']; ?></td>
                            <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3" class="text-center">No sales data found for the selected period.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Print Button -->
        <div class="text-center no-print">
            <button onclick="window.print()" class="btn btn-success mt-4">Print Report</button>
        </div>
    </div>
</body>
</html>
