<?php
session_start();
include("db.php");

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        $name = htmlspecialchars($_POST['driver_Name']);
        $licenseNo = htmlspecialchars($_POST['driver_LicenseNo']);
        $phone = htmlspecialchars($_POST['driver_phone_no']);
        $password = $_POST['driver_Password'];

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO driver_details (Name, LicenseNo, Phone_Number, Password) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            echo "<script>alert('Error preparing statement: " . $con->error . "');</script>";
        } else {
            $stmt->bind_param("ssss", $name, $licenseNo, $phone, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['driver'] = [
                    'id' => $stmt->insert_id,
                    'name' => $name,
                    'licenseNo' => $licenseNo,
                    'phone_no' => $phone
                ];
                $stmt->close();
                $con->close();
                header("Location: Driver.php");
                exit();
            } else {
                echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
            }
        }
    }

    // Handle Login
    if (isset($_POST['login'])) {
        $licenseNo = htmlspecialchars($_POST['driver_LicenseNo']);
        $password = $_POST['driver_Password'];

        if (!empty($licenseNo) && !empty($password)) {
            $stmt = $con->prepare("SELECT * FROM driver_details WHERE LicenseNo = ?");
            if (!$stmt) {
                echo "<script>alert('Error preparing statement: " . $con->error . "');</script>";
            } else {
                $stmt->bind_param("s", $licenseNo);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $storedPassword = $row['Password'];

                        if (password_verify($password, $storedPassword)) {
                            $_SESSION['driver'] = [
                                'id' => $row['id'],
                                'name' => $row['Name'],
                                'licenseNo' => $row['LicenseNo'],
                                'phone_no' => $row['Phone_Number']
                            ];
                            $stmt->close();
                            $con->close();
                            header("Location: Driver.php");
                            exit();
                        } else {
                            echo "<script>alert('Invalid password.');</script>";
                        }
                    } else {
                        echo "<script>alert('Invalid license number.');</script>";
                    }
                } else {
                    echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
                }
            }
        } else {
            echo "<script>alert('Please fill in both license number and password.');</script>";
        }
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Signup and Login Forms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background: linear-gradient(135deg, #3a0ca3 10%, #4361ee 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: #f0f0f0;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .form-container {
            margin: 20px 0;
            display: none;
        }
        .form-container.active {
            display: block;
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-weight: 700;
            color: #2c3e50;
        }
        .btn-outline-primary,
        .btn-outline-secondary {
            margin: 5px;
            border-radius: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            border-radius: 30px;
            padding: 15px;
            background-color: #e8e8e8;
            border: 1px solid #bdc3c7;
        }
        .btn-primary {
            width: 100%;
            border-radius: 30px;
            padding: 10px 0;
            background: #3a0ca3;
            border: none;
        }
        .btn-primary:hover {
            background: #4361ee;
        }
        .dropdown-toggle {
            width: 100%;
            border-radius: 30px;
            padding: 10px 0;
            background-color: #34495e;
            color: #ecf0f1;
        }
        .dropdown-menu {
            width: 100%;
            border-radius: 10px;
            background-color: #34495e;
        }
        .dropdown-menu a {
            border-radius: 10px;
            padding: 10px 20px;
            color: #ecf0f1;
        }
        .dropdown-menu a:hover {
            background-color: #2c3e50;
        }
        .text-center button {
            width: 120px;
        }
        @media (max-width: 576px) {
            .container {
                padding: 20px;
            }
            .form-control {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<div class="container">
    <div class="text-center">
        <img src="Pics/logo.png" alt="Logo" height="100px">
        <h2>BEBABEBA</h2>
    </div>
    <div class="text-center " style="margin-top: 60px;">
        <button class="btn btn-outline-primary" onclick="showForm('signup')">Driver Signup</button>
        <button class="btn btn-outline-secondary" onclick="showForm('login')">Driver Login</button>
    </div>

    <div id="signup" class="form-container">
        <h2>Driver Signup</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="driver_Name">Name:</label>
                <input type="text" class="form-control" id="driver_Name" name="driver_Name" required>
            </div>
            <div class="form-group">
                <label for="driver_LicenseNo">License Number:</label>
                <input type="text" class="form-control" id="driver_LicenseNo" name="driver_LicenseNo" required>
            </div>
            <div class="form-group">
                <label for="driver_phone_no">Phone Number:</label>
                <input type="text" class="form-control" id="driver_phone_no" name="driver_phone_no" required>
            </div>
            <div class="form-group">
                <label for="driver_Password">Password:</label>
                <input type="password" class="form-control" id="driver_Password" name="driver_Password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="signup">Signup</button>
        </form>
        <div class="dropdown mt-3">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Not a Driver?
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="index.php">Student</a></li>
                <li><a class="dropdown-item" href="Admin.php">Supervisor</a></li>
            </ul>
        </div>
    </div>

    <div id="login" class="form-container">
        <h2>Driver Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="driver_LicenseNo">License Number:</label>
                <input type="text" class="form-control" id="driver_LicenseNo" name="driver_LicenseNo" required>
            </div>
            <div class="form-group">
                <label for="driver_Password">Password:</label>
                <input type="password" class="form-control" id="driver_Password" name="driver_Password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="login">Login</button>
        </form>
        <div class="dropdown mt-3">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Not a Driver?
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="index.php">Student</a></li>
                <li><a class="dropdown-item" href="Admin.php">Supervisor</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
function showForm(formId) {
    document.getElementById('signup').classList.remove('active');
    document.getElementById('login').classList.remove('active');
    document.getElementById(formId).classList.add('active');
}

// Show login form by default
window.onload = function() {
    showForm('login');
};
</script>
</body>
</html>