<?php
session_start();
include("db.php");

// Fetch data for dropdowns
$drivers = mysqli_query($con, "SELECT id, Name FROM driver_details");

// Handle Vehicle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vehicle_form'])) {
    // Validate and sanitize input
    $numberPlate = mysqli_real_escape_string($con, $_POST['numberPlate']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $arrival_time = mysqli_real_escape_string($con, $_POST['arrival_time']);
    $departure_time = mysqli_real_escape_string($con, $_POST['departure_time']);
    $from_location = mysqli_real_escape_string($con, $_POST['from_location']);
    $to_location = mysqli_real_escape_string($con, $_POST['to_location']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $vehicle_capacity = mysqli_real_escape_string($con, $_POST['vehicle_capacity']);
    $Driver_ID = mysqli_real_escape_string($con, $_POST['Driver_ID']);

    // Handle the image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $_SESSION['message'] = "File is not an image.";
            header("Location: admin.php");
            exit;
        }

        // Check file size (limit to 5MB)
        if ($_FILES['image']['size'] > 5000000) {
            $_SESSION['message'] = "Sorry, your file is too large.";
            header("Location: admin.php");
            exit;
        }

        // Allow only certain file formats
        $allowedFormats = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowedFormats)) {
            $_SESSION['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: admin.php");
            exit;
        }

        // Check if $target_file already exists
        if (file_exists($target_file)) {
            $_SESSION['message'] = "Sorry, file already exists.";
            header("Location: admin.php");
            exit;
        }

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $_SESSION['message'] = "Sorry, there was an error uploading your file.";
            header("Location: admin.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "No image uploaded or there was an error uploading the image.";
        header("Location: admin.php");
        exit;
    }

    // Insert the data into the vehicle table using prepared statement
    $stmt = $con->prepare("INSERT INTO vehicle (numberPlate, category, arrival_time, departure_time, from_location, to_location, price, image, vehicle_capacity, Driver_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssdssi", $numberPlate, $category, $arrival_time, $departure_time, $from_location, $to_location, $price, $target_file, $vehicle_capacity, $Driver_ID);

    if ($stmt->execute()) {
        $_SESSION['message'] = "New vehicle record created successfully";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    // Close the database connection
    mysqli_close($con);

    // Redirect back to the current page to avoid resubmission on refresh
    header("Location: admin.php");
    exit;
}

// Handle deletion of a vehicle record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    include 'db.php'; // Include your database configuration file

    $idToDelete = mysqli_real_escape_string($con, $_POST['delete_id']);
    $deleteQuery = "DELETE FROM vehicle WHERE numberPlate = '$idToDelete'";

    if (mysqli_query($con, $deleteQuery)) {
        $_SESSION['message'] = "Vehicle record deleted successfully";
    } else {
        $_SESSION['message'] = "Error deleting record: " . mysqli_error($con);
    }

    mysqli_close($con);

    // Redirect back to the current page to avoid resubmission on refresh
    header("Location: admin.php");
    exit;
}

// Fetch vehicle data from the database
$query = "SELECT * FROM vehicle";
$vehicleResult = mysqli_query($con, $query);
$vehicleItems = [];
if (mysqli_num_rows($vehicleResult) > 0) {
    while ($row = mysqli_fetch_assoc($vehicleResult)) {
        $vehicleItems[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            padding: 10px 20px;
            margin-bottom: 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .navbar a:hover {
            background-color: #555;
        }

        .navbar .logout {
            background-color: #d9534f;
        }

        .navbar .logout:hover {
            background-color: #c9302c;
        }

        .form-container {
            margin-bottom: 20px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .form-container form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container label {
            display: block;
            margin-bottom: 10px;
        }

        .form-container input[type=text],
        .form-container input[type=time],
        .form-container input[type=number],
        .form-container textarea,
        .form-container input[type=file],
        .form-container select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 16px;
        }

        .form-container input[type=submit] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 3px;
        }

        .form-container input[type=submit]:hover {
            background-color: #0056b3;
        }

        .table-container {
            margin-top: 20px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table-container th,
        .table-container td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table-container th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .table-container td img {
            max-width: 100px;
            height: auto;
            display: block;
            margin: auto;
        }

        .table-container td button {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .table-container td button:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="admin.php">Vehicle Controll</a>
        <a href="users.php">Users</a>
        <a href="Drivers.php">Driver Details</a>
        <a href="Bookings.php">View Bookings</a>
        <a href="index.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Add New Vehicle</h2>
            <form action="admin.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="vehicle_form" value="1">
                <label for="numberPlate">Number Plate:</label>
                <input type="text" id="numberPlate" name="numberPlate" required>

                <label for="category">Category:</label>
                <input type="text" id="category" name="category" required>

                <label for="arrival_time">Arrival Time:</label>
                <input type="time" id="arrival_time" name="arrival_time" required>

                <label for="departure_time">Departure Time:</label>
                <input type="time" id="departure_time" name="departure_time" required>

                <label for="from_location">From Location:</label>
                <input type="text" id="from_location" name="from_location" required>

                <label for="to_location">To Location:</label>
                <input type="text" id="to_location" name="to_location" required>

                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>

                <label for="vehicle_capacity">Vehicle Capacity:</label>
                <input type="number" id="vehicle_capacity" name="vehicle_capacity" required>

                <label for="Driver_ID">Driver ID:</label>
                <select id="Driver_ID" name="Driver_ID" required>
                    <?php while ($driver = mysqli_fetch_assoc($drivers)): ?>
                        <option value="<?= $driver['id'] ?>"><?= $driver['Name'] ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="image">Image:</label>
                <input type="file" id="image" name="image" required>

                <input type="submit" value="Submit">
            </form>
        </div>

        <div class="table-container">
            <h2>Manage Vehicles</h2>
            <table>
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
                    <th>Driver ID</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($vehicleItems as $vehicle): ?>
                    <tr>
                        <td><?= htmlspecialchars($vehicle['numberPlate']) ?></td>
                        <td><?= htmlspecialchars($vehicle['category']) ?></td>
                        <td><?= htmlspecialchars($vehicle['arrival_time']) ?></td>
                        <td><?= htmlspecialchars($vehicle['departure_time']) ?></td>
                        <td><?= htmlspecialchars($vehicle['from_location']) ?></td>
                        <td><?= htmlspecialchars($vehicle['to_location']) ?></td>
                        <td><?= htmlspecialchars($vehicle['price']) ?></td>
                        <td><img src="<?= htmlspecialchars($vehicle['image']) ?>" alt="Vehicle Image"></td>
                        <td><?= htmlspecialchars($vehicle['vehicle_capacity']) ?></td>
                        <td><?= htmlspecialchars($vehicle['Driver_ID']) ?></td>
                        <td>
                            <form action="admin.php" method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($vehicle['numberPlate']) ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this vehicle?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>

</html>
