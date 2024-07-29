<?php
session_start();
include("db.php"); // Include your database connection logic

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        $name = htmlspecialchars($_POST['stud_Name']);
        $email = htmlspecialchars($_POST['stud_Email']);
        $phone = htmlspecialchars($_POST['stud_phone_no']);
        $address = htmlspecialchars($_POST['stud_Address']);
        $password = $_POST['password'];

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $profilePic = null;
        if (isset($_FILES['profile_Picture']) && $_FILES['profile_Picture']['error'] == 0) {
            $profilePic = file_get_contents($_FILES['profile_Picture']['tmp_name']);
        }

        $stmt = $con->prepare("INSERT INTO student_details (Name, Email, Phone_Number, Address, Password, Profile_Picture) VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $null = NULL;
            $stmt->bind_param("sssssb", $name, $email, $phone, $address, $hashed_password, $null);

            if ($profilePic !== null) {
                $stmt->send_long_data(5, $profilePic);
            }

            if ($stmt->execute()) {
                $_SESSION['student'] = [
                    'id' => $stmt->insert_id,
                    'name' => $name,
                    'email' => $email,
                    'phone_no' => $phone,
                    'address' => $address,
                    'profilePic' => $profilePic ? base64_encode($profilePic) : ''
                ];
                $stmt->close();
                $con->close();
                header("Location: home.php");
                exit();
            } else {
                echo "<script>alert('Error registering user.');</script>";
            }
        } else {
            echo "<script>alert('Error preparing statement.');</script>";
        }
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = htmlspecialchars($_POST['student_Email']);
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT id, Name, Email, Phone_Number, Address, Password, Profile_Picture FROM student_details WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            $_SESSION['student'] = [
                'id' => $user['id'],
                'name' => $user['Name'],
                'email' => $user['Email'],
                'phone_no' => $user['Phone_Number'],
                'address' => $user['Address'],
                'profilePic' => $user['Profile_Picture'] ? base64_encode($user['Profile_Picture']) : ''
            ];
            header("Location: home.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.');</script>";
        }
    } else {
        echo "<script>alert('No user found with this email.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Signup Forms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background: linear-gradient(135deg, #72edf2 10%, #5151e5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .form-container {
            margin: 20px 0;
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-weight: 700;
            color: #333;
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
        }
        .form-control-file {
            border-radius: 30px;
            padding: 10px 15px;
        }
        .btn-primary {
            width: 100%;
            border-radius: 30px;
            padding: 10px 0;
            background: #5151e5;
            border: none;
        }
        .btn-primary:hover {
            background: #6b6bf7;
        }
        .dropdown-toggle {
            width: 100%;
            border-radius: 30px;
            padding: 10px 0;
        }
        .dropdown-menu {
            width: 100%;
            border-radius: 10px;
        }
        .dropdown-menu a {
            border-radius: 10px;
            padding: 10px 20px;
        }
        .text-center button {
            width: 120px;
        }
        @media (max-width: 576px) {
            .container {
                padding: 20px;
            }
            .form-control, .form-control-file {
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
        <button class="btn btn-outline-secondary" onclick="showLogin()">Login</button>
        <button class="btn btn-outline-primary" onclick="showSignup()">Signup</button>
    </div>

    <div id="login" class="form-container" style="margin-top: 10px;">
        <h2>Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="login">
            <div class="form-group">
                <label for="student_Email">Email:</label>
                <input type="email" class="form-control" id="student_Email" name="student_Email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <div class="dropdown mt-3">
                <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Not a student?
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="DriverLogin.php">Driver</a></li>
                    <li><a class="dropdown-item" href="Admin.php">Supervisor</a></li>
                </ul>
            </div>
        </form>
    </div>

    <div id="signup" class="form-container" style="display:none;">
        <h2>Signup</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="signup">
            <div class="form-group">
                <label for="stud_Name">Name:</label>
                <input type="text" class="form-control" id="stud_Name" name="stud_Name" required>
            </div>
            <div class="form-group">
                <label for="stud_Email">Email:</label>
                <input type="email" class="form-control" id="stud_Email" name="stud_Email" required>
            </div>
            <div class="form-group">
                <label for="stud_phone_no">Phone Number:</label>
                <input type="text" class="form-control" id="stud_phone_no" name="stud_phone_no" required>
            </div>
            <div class="form-group">
                <label for="stud_Address">Address:</label>
                <textarea class="form-control" id="stud_Address" name="stud_Address" required></textarea>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="profile_Picture">Profile Picture:</label>
                <input type="file" class="form-control-file" id="profile_Picture" name="profile_Picture" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>
        <div class="dropdown mt-3">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Not a student?
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="DriverLogin.php">Driver</a></li>
                <li><a class="dropdown-item" href="Admin.php">Supervisor</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
function showSignup() {
    document.getElementById('signup').style.display = 'block';
    document.getElementById('login').style.display = 'none';
}
function showLogin() {
    document.getElementById('signup').style.display = 'none';
    document.getElementById('login').style.display = 'block';
}
// Show login form by default
window.onload = showLogin;
</script>
</body>
</html>