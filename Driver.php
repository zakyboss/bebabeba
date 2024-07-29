<?php
session_start();
include("db.php");

// Check if Driver_ID is set in the session
if (!isset($_SESSION['driver']['id'])) {
    die("Driver ID not set in session. Please log in again.");
}

$driver_id = $_SESSION['driver']['id'];

// Fetch the driver's name
$driver_query = "SELECT name FROM driver_details WHERE id = ?";
$driver_stmt = $con->prepare($driver_query);
$driver_stmt->bind_param("i", $driver_id);
$driver_stmt->execute();
$driver_result = $driver_stmt->get_result();

if ($driver_result->num_rows > 0) {
    $driver_row = $driver_result->fetch_assoc();
    $driver_name = htmlspecialchars($driver_row['name']);
} else {
    $driver_name = "Driver"; // Default fallback if name is not found
}

// Fetch shifts for the driver from the vehicles table using Driver_ID
$query = "
    SELECT v.*, 
           (SELECT COUNT(*) FROM orders o WHERE o.number_plate = v.numberPlate) AS num_students
    FROM vehicle v 
    WHERE v.Driver_ID = ? AND v.completed = 0";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_shift'])) {
    $numberPlate = $_POST['numberPlate'];
    $delete_query = "DELETE FROM vehicle WHERE numberPlate = ?";
    $delete_stmt = $con->prepare($delete_query);
    $delete_stmt->bind_param("s", $numberPlate);
    $delete_stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
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
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <a href="DriverLogin.php" class="logout">Logout</a>
    <h1>Welcome, <?php echo $driver_name; ?></h1>
    <h2>Your Shifts for Today</h2>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Number Plate</th>
                    <th>Category</th>
                    <th>Arrival Time</th>
                    <th>Departure Time</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Vehicle Capacity</th>
                    <th>Number of Bookings</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        $imagePath = !empty($row['image']) ? htmlspecialchars($row['image']) : '';
                        echo "<tr>
                            <td>" . htmlspecialchars($row['numberPlate']) . "</td>
                            <td>" . htmlspecialchars($row['category']) . "</td>
                            <td>" . htmlspecialchars($row['arrival_time']) . "</td>
                            <td>" . htmlspecialchars($row['departure_time']) . "</td>
                            <td>" . htmlspecialchars($row['from_location']) . "</td>
                            <td>" . htmlspecialchars($row['to_location']) . "</td>
                            <td>" . htmlspecialchars($row['price']) . "</td>
                            <td>" . ($imagePath ? "<img src='" . $imagePath . "' width='50' height='50'/>" : "No image") . "</td>
                            <td>" . htmlspecialchars($row['vehicle_capacity']) . "</td>
                            <td>" . htmlspecialchars($row['num_students']) . "</td>
                            <td>
                                <input type='checkbox' name='complete_shift' value='" . htmlspecialchars($row['numberPlate']) . "' onclick='this.form.submit()'/>
                                <input type='hidden' name='numberPlate' value='" . htmlspecialchars($row['numberPlate']) . "'/>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No shifts found for today</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </form>
</body>
</html>

<?php
// Close the database connection
$con->close();
?>
