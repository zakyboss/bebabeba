<?php
session_start();
include("db.php");

// Handle deletion if a remove request is made
if (isset($_POST['remove_driver_id'])) {
    $remove_driver_id = $_POST['remove_driver_id'];
    $delete_query = "DELETE FROM driver_details WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $remove_driver_id);
    if ($stmt->execute()) {
        echo "<script>alert('Driver removed successfully');</script>";
    } else {
        echo "<script>alert('Error removing driver: " . $con->error . "');</script>";
    }
    $stmt->close();
}

// Fetch data from the driver_details table
$query = "SELECT * FROM driver_details";
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
    <title>Driver Details</title>
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
        .remove-button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin.php" style="background-color: green;">Vehicle Control</a>
        <a href="Bookings.php" style="background-color: red;">Bookings</a>
        <a href="orders.php" style="background-color: orangered;">Drivers</a>
        <a href="users.php" style="background-color: red;">Users</a>
        <a href="index.php" class="logout">
            <i class="fa-solid fa-right-from-bracket fa-2x" style="color: white;">Logout</i>
        </a>
    </div>
    <h1>Driver Details</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>License No</th>
                <th>Phone Number</th>
                <th>Password</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td>" . htmlspecialchars($row['Name']) . "</td>
                        <td>" . htmlspecialchars($row['LicenseNo']) . "</td>
                        <td>" . htmlspecialchars($row['Phone_Number']) . "</td>
                        <td>" . htmlspecialchars($row['Password']) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='remove_driver_id' value='" . htmlspecialchars($row['id']) . "'>
                                <button type='submit' class='remove-button'>Remove</button>
                            </form>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No driver details found</td></tr>";
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
