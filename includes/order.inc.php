<?php
session_start();
include_once '../classes/order.class.php';

$order = new Order();

if(isset($_POST['order-submit'])) {
    $user_id = $_POST['user_id'] ?? null;
    $address = $_POST['address'] ?? null;
    $city = $_POST['city'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $postal_code = $_POST['postal_code'] ?? null;

    if ($user_id && $address && $city && $phone && $postal_code) {
        $order->place_order($user_id, $address, $city, $phone, $postal_code);
        $order->cart_to_order($user_id);
        $order->clear_cart($user_id);
        $_SESSION['cart'] = 0;

        header("Location: ../index.php?success=order_placed");
        exit();
    } else {
        echo "Erreur : Veuillez remplir tous les champs.";
    }
}
?>
