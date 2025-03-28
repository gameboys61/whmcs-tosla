<?php date_default_timezone_set("Europe/Istanbul");
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

include_once 'tosla/ToslaGateway.php';

function tosla_MetaData() {
    return array(
        'DisplayName' => 'Tosla Virtual Pos API',
        'APIVersion' => '1.1',
    );
}

function tosla_config() {
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Tosla Virtual Pos API',
        ),
        'apiUser' => array(
            'FriendlyName' => 'API User',
            'Type' => 'text',
            'Size' => '20',
            'Description' => 'Enter your Tosla API User here',
        ),
        'clientId' => array(
            'FriendlyName' => 'Client ID',
            'Type' => 'text',
            'Size' => '20',
            'Description' => 'Enter your Tosla Client ID here',
        ),
        'apiPass' => array(
            'FriendlyName' => 'API Password',
            'Type' => 'password',
            'Size' => '20',
            'Description' => 'Enter your Tosla API Password here',
        ),
        'iframeGo' => array(
            'FriendlyName' => 'Iframe Payment',
            'Type' => 'yesno',
            'Description' => 'Ortak iframe ödeme kullan',
        ),
    );
}


if (!function_exists('generateHash')) {
    function generateHash($apiPass, $clientId, $apiUser, $rnd, $timeSpan) {
        $hashString = $apiPass . $clientId . $apiUser . $rnd . $timeSpan;
        return base64_encode(hash('sha512', $hashString, true));
    }
}

function tosla_link($params) {
    $apiUser = $params['apiUser'];
    $clientId = $params['clientId'];
    $apiPass = $params['apiPass'];
    $orderId = 'WHMCS-' . $params['invoiceid'];
    $amount = $params['amount'] * 100;
    $callback_url = "https://" . $_SERVER['HTTP_HOST'] . "/modules/gateways/callback/tosla.php";
    $description = $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'];

    if ($params['iframeGo'] == true) {
        // ORTAK IFRAME ODEME
        $rnd = strval(rand(1000, 9999));
        $timeSpan = date("YmdHis");
        $hash = generateHash($apiPass, $clientId, $apiUser, $rnd, $timeSpan);
        $gateway = new ToslaPay("https://api.akodepos.com/api/Payment/", $clientId, $apiUser, $apiPass);
        try {
            $payment = $gateway->startPaymentThreeDSession($callback_url, $amount, $instalment, $orderId);
            $iframe_url = $gateway->getFrameUrl($payment->ThreeDSessionId);
            echo '<iframe src="' . $iframe_url . '" style=" width:100%; height: 550px;position: relative;" frameborder="0" allowfullscreen></iframe>';
        } catch (Exception $e) {
            print_r($e);
        }
    } else {
        // 3D ODEME
        $gateway = new ToslaPay("https://api.akodepos.com/api/Payment/", $clientId, $apiUser, $apiPass);
        try {
            $payment = $gateway->threeDPayment($callback_url, $amount, 0, $orderId);
            if(!$payment->ThreeDSessionId){
                die('<br><div class="alert alert-danger" role="alert">Pos hatası, destek ile iletişime geçin.</div>');
            }
            echo '<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-credit-card"></i> Kredi / Banka Kartı ile Ödeme</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="paymentFormContainer">
                    <form role="form" method="post" action="'.$gateway->getFormUrl().'" target="paymentIframe" onsubmit="showIframe()" class="p-4 border rounded-lg">
                        <input type="hidden" name="ThreeDSessionId" value="'.$payment->ThreeDSessionId.'">
                        <label for="CardHolderName" class="form-label">Kart Üzerindeki İsim Soyisim</label>
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" name="CardHolderName" id="CardHolderName" placeholder="İsim Soyisim" minlength="3" required>
                        </div>
                        <label for="CardNo" class="form-label">Kredi Kart Numarası</label>
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-credit-card"></i></span>
                        </div>
                        <input type="text" class="form-control" name="CardNo" id="CardNo" placeholder="Kredi Kart Numarası" maxlength="19" required>
                        </div>
                        <div class="row">
                        <div class="col-6">
                        <label for="ExpireDate" class="form-label">Son Kullanma Tarihi</label>
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control" name="ExpireDate" id="ExpireDate" placeholder="AA/YY" maxlength="5" required>
                        </div>
                        </div>
                            <div class="col-6">
                            <label for="Cvv" class="form-label">Güvenlik Kodu</label>
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-unlock-alt"></i></span>
                        </div>
                        <input type="text" class="form-control" name="Cvv" id="Cvv" placeholder="CVV" maxlength="4" required>
                        </div>
                    </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="far fa-credit-card"></i> Ödemeyi Tamamla</button>
                    </form>
                    <hr>
                    <div class="text-center">
                    <div class="alert alert-info" style="font-size: 0.75rem;" role="alert"><i class="far fa-info-circle"></i> İşleminizi bankanızın güvenli <b>3D</b> ödeme sayfasında tamamlayacaksınız.</div>
                    <img class="img-fluid  small-img" style="width: 50%;" src="/assets/img/withcard.png">
                </div>
                </div>
                <iframe name="paymentIframe" style="width: 100%; height: 400px; border: none; display: none;"></iframe>
            </div>
        </div>
    </div>
</div>';

echo '<script>
function showIframe() {
    document.getElementById("paymentFormContainer").style.display = "none";
    document.querySelector("iframe[name=\'paymentIframe\']").style.display = "block";
}

document.addEventListener("DOMContentLoaded", function() {
    var toslaPay = new bootstrap.Modal(document.getElementById("paymentModal"));
    toslaPay.show();

    document.getElementById("ExpireDate").addEventListener("input", function (e) {
        let input = e.target.value;
        input = input.replace(/[^0-9]/g, "");

        if (input.length > 2) {
            input = input.slice(0, 2) + "/" + input.slice(2);
        }
        
        if (input.length > 5) {
            input = input.slice(0, 5);
        }

        e.target.value = input;
    });

    document.getElementById("ExpireDate").addEventListener("keydown", function(e) {
        if (e.key === "/" || (e.key === "Backspace" && this.selectionStart === 3)) {
            e.preventDefault();
        }
    });

    document.getElementById("CardNo").addEventListener("input", function (e) {
        let input = e.target.value.replace(/\D/g, "").substring(0, 16);
        input = input.match(/.{1,4}/g)?.join(" ") || "";
        e.target.value = input;
    });

    document.getElementById("CardNo").addEventListener("blur", function (e) {
        e.target.value = e.target.value.replace(/\s/g, "");
    });
});
</script>';

        } catch (Exception $e) {
            die('<br><div class="alert alert-danger" role="alert">Bağlantı hatası, destek ile iletişime geçin.</div>');
            print_r($e);
        }
    }
    
    return '<button type="submit" class="btn btn-success" data-toggle="modal" data-target="#paymentModal"><i class="far fa-credit-card"></i> '.$params['langpaynow'].'</button>';
}


function tosla_refund($params) {
    $apiUser    = $params['apiUser'];
    $clientId   = $params['clientId'];
    $apiPass    = $params['apiPass'];
    $orderId    = $params['transid'];
    $retAmount  = $params['amount'] * 100;
    
    $gateway = new ToslaPay("https://api.akodepos.com/api/Payment/", $clientId, $apiUser, $apiPass);
    $paymentCheck = $gateway->refund($orderId, $retAmount);
    
    return false;
    
}
