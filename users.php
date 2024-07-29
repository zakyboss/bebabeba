<?php
session_start();
include("db.php");

// Handle deletion if a ban request is made
if (isset($_POST['ban_user_id'])) {
    $ban_user_id = $_POST['ban_user_id'];
    $delete_query = "DELETE FROM student_details WHERE id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $ban_user_id);
    if ($stmt->execute()) {
        echo "<script>alert('User banned successfully');</script>";
    } else {
        echo "<script>alert('Error banning user: " . $con->error . "');</script>";
    }
    $stmt->close();
}

// Fetch data from the student_details table
$query = "SELECT * FROM student_details";
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
    <title>Student Details</title>
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
        .ban-button {
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
        <a href="Drivers.php" style="background-color: orangered;">Drivers</a>
        <a href="users.php" style="background-color: red;">Users</a>
        <a href="index.php" class="logout">
            <i class="fa-solid fa-right-from-bracket fa-2x" style="color: white;">Logout</i>
        </a>
    </div>
    <h1>Student Details</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Password</th>
                <th>Profile Picture</th>
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
                        <td>" . htmlspecialchars($row['Email']) . "</td>
                        <td>" . htmlspecialchars($row['Phone_Number']) . "</td>
                        <td>" . htmlspecialchars($row['Address']) . "</td>
                        <td>" . htmlspecialchars($row['Password']) . "</td>
                        <td><img src='data:image/jpeg;base64," . base64_encode($row['Profile_Picture']) . "' width='50' height='50'/></td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='ban_user_id' value='" . htmlspecialchars($row['id']) . "'>
                                <button type='submit' class='ban-button'>Ban</button>
                            </form>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No student details found</td></tr>";
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
