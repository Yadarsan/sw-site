<?php
$baseUrl = '/awe-electronics'; // adjust if your base folder is different

function getUrl($page, $params = []) {
    global $baseUrl;
    $url = $baseUrl . '/index.php?page=' . $page;

    foreach ($params as $key => $value) {
        $url .= '&' . $key . '=' . urlencode($value);
    }

    return $url;
}

$homeUrl = getUrl('home');
$authUrl = getUrl('auth');
$accountUrl = getUrl('account');
$cartUrl = getUrl('cart');
$checkoutUrl = getUrl('checkout');
$ordersUrl = getUrl('orders');
$logoutUrl = getUrl('logout');

// Admin URLs
$adminProductsUrl = getUrl('admin-products');
$adminInventoryUrl = getUrl('admin-inventory');
$adminReportsUrl = getUrl('admin-reports');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $homeUrl; ?>">AWE Electronics</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo $homeUrl; ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $cartUrl; ?>">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $accountUrl; ?>">Account</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $ordersUrl; ?>">Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $authUrl; ?>">Login / Register</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $logoutUrl; ?>">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
