<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'add':
                addToCart($data['item']);
                break;
            case 'remove':
                removeFromCart($data['index']);
                break;
            case 'clear':
                clearCart();
                break;
            case 'load':
                loadCart();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
    }
}

function addToCart($item) {
    if (!isset($_SESSION['cartItems'])) {
        $_SESSION['cartItems'] = [];
    }

    $cartItems = &$_SESSION['cartItems'];
    $found = false;

    foreach ($cartItems as &$cartItem) {
        if ($cartItem['numberPlate'] === $item['numberPlate']) {
            $cartItem['quantity'] += 1;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $item['quantity'] = 1;
        $cartItems[] = $item;
    }

    echo json_encode(['success' => true, 'cartItems' => $cartItems]);
}

function removeFromCart($index) {
    if (isset($_SESSION['cartItems'][$index])) {
        array_splice($_SESSION['cartItems'], $index, 1);
    }
    echo json_encode(['success' => true, 'cartItems' => $_SESSION['cartItems']]);
}

function clearCart() {
    $_SESSION['cartItems'] = [];
    echo json_encode(['success' => true, 'cartItems' => []]);
}

function loadCart() {
    $cartItems = isset($_SESSION['cartItems']) ? $_SESSION['cartItems'] : [];
    echo json_encode(['success' => true, 'cartItems' => $cartItems]);
}
?>
