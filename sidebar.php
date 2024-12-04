<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Sidebar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: #ffffff;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            text-decoration: none;
            color: #ffffff;
            transition: background-color 0.3s, padding-left 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar a.active {
            background-color: #007bff;
            color: #ffffff;
        }
        .sidebar a i {
            font-size: 18px;
            margin-right: 15px;
            transition: margin-right 0.3s;
        }
        .sidebar a span {
            flex: 1;
        }
        .sidebar .collapse-btn {
            display: none;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .sidebar .collapse-btn {
                display: block;
                position: absolute;
                top: 10px;
                right: -30px;
                background-color: #343a40;
                color: #ffffff;
                border: none;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                text-align: center;
                line-height: 30px;
                cursor: pointer;
            }
            .sidebar.collapsed {
                width: 0;
                overflow: hidden;
            }
            .sidebar.collapsed .sidebar-content {
                display: none;
            }
            .sidebar.collapsed .collapse-btn {
                right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <button class="collapse-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <!-- Sidebar -->
    <div class="sidebar">
        <h4>
    <img src="dist/img/shein.jfif"
         class="brand-image img-circle elevation-3" 
         style="opacity: .8; width: 30px; height: 30px; vertical-align: middle;"> 
    Shein Clothing
</h4>
        <a href="customer_orders.php"><i class="fas fa-shopping-cart"></i> La Dalbayo</a>
        <a href="vip_requests.php"><i class="fas fa-star"></i>Vip People</a>
        <a href="ordered_products.php"><i class="fas fa-box"></i>La Dalbay</a>
        <a href="ready_orders.php"><i class="fas fa-check"></i>Alaabtii Timid </a>
        <!-- Add more sidebar links as needed -->
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }
    </script>
</body>
</html>
