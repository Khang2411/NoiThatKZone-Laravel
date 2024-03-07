<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderMail;
use App\Models\Cart;
use App\Models\Momo;
use App\Models\Order;
use App\Models\User;
use App\Models\Vnpay;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminCheckOutController extends Controller
{
    function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    function addOrder($params)
    {
        $order = Order::create([
            'user_id' => $params['order']['user_id'],
            'phone' => $params['order']['phone'],
            'ship_address' => $params['order']['ship_address'],
            'ship_name' => $params['order']['ship_name'],
            'method' => $params['order']['method'],
            'status' => $params['order']['status'],
            'city_id' => $params['order']['city_id'],
            'district_id' => $params['order']['district_id'],
            'ward_id' => $params['order']['ward_id'],
            'discount' => $params['order']['discount'],
            'coupon_code' => $params['order']['coupon_code']
        ]);
        $order = Order::find($order->id);
        foreach (request()->product as $product) {
            $order->products()->attach($product['id'], ['quantity' => $product['quantity'], 'price' => $product['price']]);
        }
        return $order;
    }

    public function momoCheckout()
    {
        $order = $this->addOrder(request()->all());

        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán qua MoMo";
        $amount = request()->total;
        $orderId = time() . "";
        $redirectUrl = route('api.checkout.momo.return', $order['id']);
        $ipnUrl = route('api.checkout.momo.return', $order['id']);
        $extraData = '';

        $requestId = time() . "";
        $requestType = "captureWallet";
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json
        //Just a example, please check more in there
        return response()->json(['redirect' =>  $jsonResult['payUrl']]);
        //return Inertia::location($jsonResult['payUrl']);
    }

    function momoCheckoutReturn(Order $order)
    {
        $params = http_build_query(request()->all());

        if (request()->partnerCode) {
            $data = [
                'partnerCode' => request()->partnerCode,
                'orderId' => request()->orderId,
                'requestId' => request()->requestId,
                'amount' => request()->amount,
                'orderInfo' => request()->orderInfo,
                'orderType' => request()->orderType,
                'transId' => request()->transId,
                'payType' => request()->payType,
                'signature' => request()->signature,
            ];
            $result = Momo::create($data);
        }

        if (request()->resultCode === '0') {
            $order->status = 'processing';
            $order->momo_id =  $result['id'];
            $order->save();
        }
        SendOrderMail::dispatch($order->user_id, $order);
        Cart::where('user_id', $order->user_id)->delete();
        return redirect("http://localhost:3000/order-success?{$params}");
    }

    function vnpayCheckout()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $order = $this->addOrder(request()->all());
        $vnp_TmnCode = "VHTIVTCQ"; //Mã định danh merchant kết nối (Terminal Id)
        $vnp_HashSecret = "BCEMOZWQJQTKETOIDJNKWPRYMRQXGNAZ"; //Secret key
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('api.checkout.vnpay.return', $order['id']);
        // $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        // $apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";

        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
        $vnp_TxnRef = rand(1, 10000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = request()->total; // Số tiền thanh toán
        $vnp_Locale = "vn"; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = ""; //Mã phương thức thanh toán
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; //IP Khách hàng thanh toán

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        return response()->json(['redirect' =>   $vnp_Url]);
    }

    function vnpayCheckoutReturn(Order $order)
    {
        $params = http_build_query(request()->all());

        if (request()->vnp_ResponseCode) {
            $data = [
                'vnp_Amount' => request()->vnp_Amount,
                'vnp_BankCode' => request()->vnp_BankCode,
                'vnp_BankTranNo' => request()->vnp_BankTranNo,
                'vnp_CardType' => request()->vnp_CardType,
                'vnp_OrderInfo' => request()->vnp_OrderInfo,
                'vnp_PayDate' => request()->vnp_PayDate,
                'vnp_ResponseCode' => request()->vnp_ResponseCode,
                'vnp_TmnCode' => request()->vnp_TmnCode,
                'vnp_TransactionNo' => request()->vnp_TransactionNo,
                'vnp_TxnRef' => request()->vnp_TxnRef,
                'vnp_SecureHash' => request()->vnp_SecureHash,
            ];
            $result = Vnpay::create($data);
        }

        if (request()->vnp_ResponseCode === '00') {
            $order = Order::find(request()->order_id);

            $order->status = 'processing';
            $order->vnpay_id =  $result['id'];
            $order->save();
        }
        SendOrderMail::dispatch($order->user_id, $order);
        return redirect("http://localhost:3000/order-success?{$params}");
    }
}
