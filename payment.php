<?php
require __DIR__ . "/vendor/autoload.php";
session_start();
include("db.php");

// Define the base URL
$base_url = "https://a942-41-90-34-177.ngrok-free.app";

function createOrder($con, $userId, $cartItems) {
    $orderId = uniqid(); // Generate a unique order ID
    $status = 'pending';

    foreach ($cartItems as $item) {
        $stmt = $con->prepare("INSERT INTO orders (order_id, user_id, number_plate, from_location, to_location, arrival_time, departure_time, price, quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare statement failed: " . $con->error);
        }

        // Convert arrival_time and departure_time to datetime
        $arrivalTime = date('Y-m-d H:i:s', strtotime($item['arrivalTime']));
        $departureTime = date('Y-m-d H:i:s', strtotime($item['departureTime']));

        $stmt->bind_param("sisssssdis", $orderId, $userId, $item['numberPlate'], $item['from'], $item['to'], $arrivalTime, $departureTime, $item['price'], $item['quantity'], $status);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
    }

    return $orderId;
}

// Check if user is logged in
if (!isset($_SESSION['student']['id'])) {
    die("User not logged in.");
}

$userId = $_SESSION['student']['id'];

// Prepare for payment
$cartItems = isset($_SESSION['cartItems']) ? $_SESSION['cartItems'] : [];

// Set your Stripe secret key
$stripe_secret_key = "sk_test_51PbQ8vL8czQ0xkyVgdOzMIHRtx9ru3Uip8xdbSg7yVOchwHX10Pcak1Zf7KQqWxhMStwDoy1fxOMWY1kSkNpex1W00WSpIyYt6";
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Prepare line items for the checkout session
$line_items = [];

foreach ($cartItems as $item) {
    $line_items[] = [
        "quantity" => $item['quantity'],
        "price_data" => [
            "currency" => "kes",
            "unit_amount" => $item['price'] * 100,
            "product_data" => [
                "name" => $item['numberPlate'],
                "description" => "From: " . $item['from'] . " To: " . $item['to'] . "\nArrival Time: " . $item['arrivalTime'] . "\nDeparture Time: " . $item['departureTime'],
                "images" => [$item['image']],
            ],
        ],
    ];
}

// Create the order and store it in the database
$orderId = createOrder($con, $userId, $cartItems);

// Create the checkout session
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "{$base_url}/BebaBeba/thankyou.php?session_id={CHECKOUT_SESSION_ID}&user_id={$userId}&order_id={$orderId}",
        "cancel_url" => "{$base_url}/BebaBeba/cancel.php",
        "line_items" => $line_items,
        "metadata" => [
            "order_id" => $orderId, // Pass the order ID to Stripe metadata
        ],
    ]);

    // Set order details in session
    $_SESSION['order_details'] = [
        'order_id' => $orderId,
        'session_id' => $checkout_session->id
    ];

} catch (Exception $e) {
    die("Error creating Stripe Checkout session: " . $e->getMessage());
}

// Redirect to the checkout session URL
http_response_code(303);
header("Location: " . $checkout_session->url);
?>
