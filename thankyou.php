<?php
// thankyou.php
session_start();
include("db.php");
require __DIR__ . "/vendor/autoload.php";

// Ensure order_id and session_id are set in the URL parameters
if (!isset($_GET['order_id']) || !isset($_GET['session_id'])) {
    die("Order ID or session ID not specified.");
}

$orderId = $_GET['order_id'];
$sessionId = $_GET['session_id'];
$userId = $_SESSION['student']['id'];

$stripe_secret_key = "sk_test_51PbQ8vL8czQ0xkyVgdOzMIHRtx9ru3Uip8xdbSg7yVOchwHX10Pcak1Zf7KQqWxhMStwDoy1fxOMWY1kSkNpex1W00WSpIyYt6";
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Verify payment status with Stripe
try {
    $stripe_session = \Stripe\Checkout\Session::retrieve($sessionId);
    
    if ($stripe_session->payment_status === 'paid') {
        // Update order status in the database
        $updateQuery = "UPDATE orders SET status = 'successful' WHERE order_id = ?";
        $updateStmt = $con->prepare($updateQuery);
        if ($updateStmt) {
            $updateStmt->bind_param("s", $orderId);
            if (!$updateStmt->execute()) {
                die("Update failed: " . $updateStmt->error);
            }
        } else {
            die("Prepare statement failed: " . $con->error);
        }
    } else {
        // Update order status in the database as unsuccessful
        $updateQuery = "UPDATE orders SET status = 'unsuccessful' WHERE order_id = ?";
        $updateStmt = $con->prepare($updateQuery);
        if ($updateStmt) {
            $updateStmt->bind_param("s", $orderId);
            if (!$updateStmt->execute()) {
                die("Update failed: " . $updateStmt->error);
            }
        } else {
            die("Prepare statement failed: " . $con->error);
        }
        die("Payment not successful.");
    }
} catch (Exception $e) {
    die("Error retrieving Stripe session: " . $e->getMessage());
}

// Retrieve the specific order from the database
$query = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $con->prepare($query);
if ($stmt) {
    $stmt->bind_param("si", $orderId, $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            
            // Determine status color
            $statusColor = ($order['status'] === 'successful') ? 'green' : 'red';

            // Display the order details
            echo "<div id='order-details' style='max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; font-size: 18px;'>
                    <img src='Pics/logo.png' alt='logo' style='height: 90px; display: block; margin: auto;'>
                    <h1 style='text-align: center; color: #333; font-size: 24px;'>Thank You for Your Order!</h1>
                    <p><strong>Order ID:</strong> " . htmlspecialchars($order['order_id']) . "</p>
                    <p><strong>Number Plate:</strong> " . htmlspecialchars($order['number_plate']) . "</p>
                    <p><strong>From:</strong> " . htmlspecialchars($order['from_location']) . "</p>
                    <p><strong>To:</strong> " . htmlspecialchars($order['to_location']) . "</p>
                    <p><strong>Arrival Time:</strong> " . htmlspecialchars(date('g:i A', strtotime($order['arrival_time']))) . "</p>
                    <p><strong>Departure Time:</strong> " . htmlspecialchars(date('g:i A', strtotime($order['departure_time']))) . "</p>
                    <p><strong>Price:</strong> " . htmlspecialchars($order['price']) . "</p>
                    <p><strong>Status:</strong> <span style='color: $statusColor;'>" . htmlspecialchars($order['status']) . "</span></p>
                </div>";
        } else {
            echo "Order not found for Order ID: " . htmlspecialchars($orderId);
        }
    } else {
        die("Execute failed: " . $stmt->error);
    }
} else {
    die("Prepare statement failed: " . $con->error);
}

$stmt->close();
$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .styled-button {
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: whitesmoke;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        .home-button {
            background-color: green;
        }
        .download-button {
            background-color: blue;
        }
    </style>
</head>
<body>
    <a href="Home.php" class="styled-button home-button">Go back Home</a>
    <button id="download-button" class="styled-button download-button">Download as Image</button>

    <script>
        document.getElementById('download-button').addEventListener('click', function () {
            html2canvas(document.getElementById('order-details'), {
                onrendered: function (canvas) {
                    var link = document.createElement('a');
                    link.href = canvas.toDataURL();
                    link.download = 'order-details.png';
                    link.click();
                }
            });
        });
    </script>
</body>
</html>
