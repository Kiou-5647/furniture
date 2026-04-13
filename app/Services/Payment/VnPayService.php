<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class VnPayService
{
    protected string $tmnCode;

    protected string $hashSecret;

    protected string $payUrl;

    protected string $apiUrl;

    protected string $returnUrl;

    protected string $version;

    public function __construct()
    {
        $this->tmnCode = config('services.vnpay.tmn_code');
        $this->hashSecret = config('services.vnpay.hash_secret');
        $this->payUrl = config('services.vnpay.url');
        $this->apiUrl = config('services.vnpay.api_url');
        $this->returnUrl = config('services.vnpay.return_url');
        $this->version = config('services.vnpay.version', '2.1.0');
    }

    /**
     * Build VNPay payment URL for a given transaction.
     *
     * @param  array{
     *     txn_ref: string,
     *     amount: float,
     *     order_info: string,
     *     order_type: string,
     *     locale?: string,
     *     bank_code?: string,
     *     expire_date?: string,
     *     extra_data?: array<string, mixed>,
     * }  $params
     * @return string Full redirect URL
     */
    public function buildPaymentUrl(array $params): string
    {
        $inputData = [
            'vnp_Version' => $this->version,
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => (int) ($params['amount'] * 100), // VND * 100
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip() ?? '127.0.0.1',
            'vnp_Locale' => $params['locale'] ?? 'vn',
            'vnp_OrderInfo' => $params['order_info'],
            'vnp_OrderType' => $params['order_type'] ?? 'other',
            'vnp_ReturnUrl' => $this->returnUrl,
            'vnp_TxnRef' => $params['txn_ref'],
            'vnp_ExpireDate' => $params['expire_date'] ?? now()->addMinutes(30)->format('YmdHis'),
        ];

        if (! empty($params['bank_code'])) {
            $inputData['vnp_BankCode'] = $params['bank_code'];
        }

        return $this->signAndBuildUrl($inputData);
    }

    /**
     * Verify the secure hash from VNPay return/IPN.
     *
     * @param  array<string, string>  $returnData
     */
    public function verifyReturn(array $returnData): bool
    {
        $secureHash = $returnData['vnp_SecureHash'] ?? '';
        unset($returnData['vnp_SecureHash'], $returnData['vnp_SecureHashType']);

        ksort($returnData);

        $hashData = '';
        $first = true;
        foreach ($returnData as $key => $value) {
            if ($first) {
                $hashData .= urlencode($key).'='.urlencode($value);
                $first = false;
            } else {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            }
        }

        $computedHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        return hash_equals($computedHash, $secureHash);
    }

    /**
     * Query a transaction status from VNPay API.
     *
     * @return array<string, mixed>
     */
    public function queryTransaction(string $txnRef, string $transDate): array
    {
        $requestData = [
            'vnp_RequestId' => now()->format('YmdHis').'_'.rand(1, 99999),
            'vnp_Version' => $this->version,
            'vnp_Command' => 'querydr',
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Kiem tra ket qua GD:'.$txnRef,
            'vnp_TransDate' => $transDate,
            'vnp_TransactionNo' => '0',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_IpAddr' => request()->ip() ?? '127.0.0.1',
        ];

        $hashData = '';
        $first = true;
        foreach ($requestData as $key => $value) {
            if ($first) {
                $hashData .= urlencode($key).'='.urlencode($value);
                $first = false;
            } else {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            }
        }

        $requestData['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $this->hashSecret);

        $response = Http::post($this->apiUrl, $requestData);

        return $response->json() ?? [];
    }

    /**
     * Sign input data and build the redirect URL.
     *
     * @param  array<string, mixed>  $inputData
     */
    protected function signAndBuildUrl(array $inputData): string
    {
        ksort($inputData);

        $query = '';
        $hashData = '';
        $first = true;

        foreach ($inputData as $key => $value) {
            $encodedKey = urlencode($key);
            $encodedValue = urlencode($value);

            if ($first) {
                $hashData .= $encodedKey.'='.$encodedValue;
                $query .= $encodedKey.'='.$encodedValue;
                $first = false;
            } else {
                $hashData .= '&'.$encodedKey.'='.$encodedValue;
                $query .= '&'.$encodedKey.'='.$encodedValue;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        return $this->payUrl.'?'.$query.'&vnp_SecureHash='.$secureHash;
    }
}
