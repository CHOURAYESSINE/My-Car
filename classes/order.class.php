<?php
require_once __DIR__ . '/db.class.php';

class Order extends DB {
    
    public function get_order($id = "") {
        $conn = $this->connect();
        if ($id == "") {
            $sql = "SELECT * FROM `orders`;";
            return $conn->query($sql);
        } else {
            $sql = "SELECT * FROM `orders` WHERE `id` = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }
    }

    public function delete_order($id) {
        $conn = $this->connect();
        $sql = "DELETE FROM `orders` WHERE `id` = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function get_cart($id) {
        $conn = $this->connect();
        $sql = "SELECT * FROM `cart` WHERE `user_id` = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function clear_cart($id) {
        $conn = $this->connect();
        $sql = "DELETE FROM `cart` WHERE `user_id` = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function place_order($user_id, $address, $city, $phone, $postal_code) {
        $conn = $this->connect();

        $sql = "INSERT INTO `orders` (user_id, address, city, phone, postal_code) VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE address = VALUES(address), city = VALUES(city), phone = VALUES(phone), postal_code = VALUES(postal_code);";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $address, $city, $phone, $postal_code);
        $stmt->execute();
        $stmt->close();
    }

    public function cart_to_order($id) {
        $conn = $this->connect();

        $sql = "SELECT id FROM `orders` WHERE `user_id` = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            return false;
        }
        $order = $result->fetch_assoc();
        $order_id = $order['id'];

        $sql = "SELECT * FROM `cart` WHERE `user_id` = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cart = $stmt->get_result();

        while ($row = $cart->fetch_assoc()) {
            $sql = "INSERT INTO `order_items` (order_id, product_id, product_id2) VALUES (?, ?, ?);";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $order_id, $row['product_id'], $row['product_id_2']);
            $stmt->execute();
        }
        $stmt->close();
    }

    public function get_items($id) {
        $conn = $this->connect();
        $sql = "SELECT * FROM `order_items` WHERE `order_id` = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>
