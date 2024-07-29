<?php
session_start();
include("db.php");
// Fetch data from the orders table
$query = "SELECT * FROM orders";
$result = $con->query($query);

// Check for any errors
if ($con->error) {
    die("Error fetching data: " . $con->error);
}

// Include the header (if you have a separate header file)
// include("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .navbar {
            display: flex;
            justify-content: space-around;
            background-color: #333;
            padding: 14px 0;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            border-radius: 20px;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        h1 {
            text-align: center;
            margin: 20px 0;
        }
        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .logout {
            margin-left: auto;
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin.php" style="background-color: green;">Vehicle Control</a>
        <a href="Bookings.php" style="background-color: red;">Bookings</a>
        <a href="Drivers.php" style="background-color: orangered;">Drivers</a>
        <a href="users.php" style="background-color: red;">Users</a>
        <a href="index.php" class="logout">
            <i class="fa-solid fa-right-from-bracket fa-2x" style="color: white;">Logout</i>
        </a>
    </div>
    <h1>Orders</h1>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Number Plate</th>
                <th>From Location</th>
                <th>To Location</th>
                <th>Arrival Time</th>
                <th>Departure Time</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['order_id']) . "</td>
                        <td>" . htmlspecialchars($row['user_id']) . "</td>
                        <td>" . htmlspecialchars($row['number_plate']) . "</td>
                        <td>" . htmlspecialchars($row['from_location']) . "</td>
                        <td>" . htmlspecialchars($row['to_location']) . "</td>
                        <td>" . htmlspecialchars($row['arrival_time']) . "</td>
                        <td>" . htmlspecialchars($row['departure_time']) . "</td>
                        <td>" . htmlspecialchars($row['price']) . "</td>
                        <td>" . htmlspecialchars($row['quantity']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No orders found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Close the database connection
$con->close();
?>
