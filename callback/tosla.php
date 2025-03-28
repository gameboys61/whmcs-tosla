<?php
// Tosla Callback
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');
include_once '../tosla/ToslaGateway.php';
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams["type"]) {
    die("Module Not Activated");
}
echo '<style>.alert{position:relative;padding:.75rem 1.25rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.375rem}.alert-success{color:#155724;background-color:#d4edda;border-color:#c3e6cb}.alert-danger{color:#721c24;background-color:#f8d7da;border-color:#f5c6cb;}</style>';
$apiUser    = $gatewayParams['apiUser'];
$clientId   = $gatewayParams['clientId'];
$apiPass    = $gatewayParams['apiPass'];
$gateway = new ToslaPay("https://api.akodepos.com/api/Payment/", $clientId, $apiUser, $apiPass);
$gateway->setPost($_REQUEST);
$orderId = $gateway->getOrderId();
if (empty($orderId)) {
    die("Order ID is missing");
}
$invoiceId = checkCbInvoiceID(ltrim($orderId, 'WHMCS-'), $gatewayModuleName);
if (!$invoiceId) {
    die("Invalid Invoice ID");
}
if ($gateway->isSuccessfull()) {
    $amount = $_POST['amount'] ?? 0;
    $logAmount = $amount / 100;
    try {
        $result = addInvoicePayment($invoiceId, $orderId, $logAmount, 0, $gatewayModuleName);
        if (!$result) {
           echo '<div class="alert alert-danger" role="alert">Ödemeniz alınamadı.</div>';
        } else {
           echo '<div class="alert alert-success" role="alert">Ödemeniz başarıyla alındı, teşekkürler.</div>';
        }
    } catch (Exception $e) {
       echo '<div class="alert alert-danger" role="alert">Ödemeniz alınamadı. Hata 2</div>';
    }
} else {
    echo '<div class="alert alert-danger" role="alert"><b>Ödemeniz alınamadı.</b> Kart bilgilerinizi ve bakiyenizi kontrol edin.</div>';
}
?>
