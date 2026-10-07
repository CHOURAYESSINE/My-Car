<?php
require_once __DIR__.'/bootstrap.php';require_once __DIR__.'/includes/autoloader.inc.php';
$order=(new Order())->get_order($_GET['order_id']??0)->fetch_assoc();
if(!$order){http_response_code(404);exit('Order not found.');}
$items=(new Order())->get_items($order['id']);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="css/admin.css"><title>TARUNNO DRIVES | Order details</title></head><body>
<div class="order-all"><div class="heading"><h1><a href="admin.php">Back to administration</a> TARUNNO DRIVES</h1></div>
<div class="user-info"><h3>Client details</h3><h4><?= $order['username'] ?></h4><h4><?= $order['email'] ?></h4><h4><?= $order['address'] ?> | <?= $order['city'] ?></h4><h4><?= $order['phone'] ?></h4><h4><?= $order['postal_code'] ?></h4></div>
<div class="order-container"><h3>Order #<?= $order['id'] ?></h3><div class="items">
<?php while($item=$items->fetch_assoc()): ?><div class="item"><img src="<?= $item['image'] ?>" alt="<?= $item['model'] ?>"><h5 class="item-name"><?= $item['model'] ?></h5><h5 class="item-name"><?= $item['manufacturer'] ?></h5><h6 class="item-price">$<?= $item['price'] ?></h6></div><?php endwhile; ?>
</div></div></div></body></html>
