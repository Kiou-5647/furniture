<?php

namespace App\Actions\Payment;

use App\Enums\InvoiceStatus;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Models\Sales\PaymentAllocation;
use App\Services\Payment\VnPayService;
use Illuminate\Support\Facades\DB;

class ProcessVnPayReturnAction
{
    public function __construct(
        private VnPayService $vnPayService,
    ) {}

    /**
     * Process VNPay return callback.
     *
     * @param  array<string, string>  $returnData
     * @return array{success: bool, message: string, invoice?: Invoice}
     */
    public function execute(array $returnData): array
    {
        // Verify signature
        if (! $this->vnPayService->verifyReturn($returnData)) {
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ.'];
        }

        $responseCode = $returnData['vnp_ResponseCode'] ?? '';
        $txnRef = $returnData['vnp_TxnRef'] ?? '';
        $transactionNo = $returnData['vnp_TransactionNo'] ?? '';
        $amount = ($returnData['vnp_Amount'] ?? '0') / 100; // Convert back from VND*100
        $bankCode = $returnData['vnp_BankCode'] ?? '';
        $payDate = $returnData['vnp_PayDate'] ?? '';

        $invoiceNumber = explode('_', $txnRef)[0];
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();
        if (! $invoice) {
            return ['success' => false, 'message' => 'Hóa đơn không tồn tại.'];
        }

        // Payment successful
        if ($responseCode === '00') {
            DB::transaction(function () use ($invoice, $amount, $transactionNo, $bankCode, $payDate, $responseCode, $txnRef, $returnData) {
                // Update invoice amount_paid
                $invoice->amount_paid = min(
                    (float) $invoice->amount_paid + $amount,
                    (float) $invoice->amount_due,
                );

                // Check if fully paid
                if ((float) $invoice->amount_paid >= (float) $invoice->amount_due) {
                    $invoice->status = InvoiceStatus::Paid;
                } else {
                    $invoice->status = InvoiceStatus::Open;
                }

                $invoice->save();

                // Create Payment record
                $payment = Payment::create([
                    'customer_id' => $this->getCustomerId($invoice),
                    'gateway' => 'vnpay',
                    'transaction_id' => $transactionNo,
                    'amount' => $amount,
                    'gateway_payload' => [
                        'vnp_ResponseCode' => $responseCode,
                        'vnp_TxnRef' => $txnRef,
                        'vnp_TransactionNo' => $transactionNo,
                        'vnp_BankCode' => $bankCode,
                        'vnp_PayDate' => $payDate,
                        'raw' => $returnData,
                    ],
                ]);

                // Create payment allocation
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $amount,
                ]);
            });

            return [
                'success' => true,
                'message' => 'Thanh toán thành công.',
                'invoice' => $invoice,
            ];
        }

        // Payment failed
        return [
            'success' => false,
            'message' => $this->getResponseMessage($responseCode),
            'invoice' => $invoice,
        ];
    }

    protected function getCustomerId(Invoice $invoice): ?string
    {
        // Try to get customer from invoiceable (Order or Booking)
        $invoiceable = $invoice->invoiceable;
        if (! $invoiceable) {
            return null;
        }

        return match (true) {
            $invoiceable instanceof Order => $invoiceable->customer_id,
            $invoiceable instanceof Booking => $invoiceable->customer_id,
            default => null,
        };
    }

    protected function getResponseMessage(string $code): string
    {
        return match ($code) {
            '07' => 'Trừ tiền thành công nhưng giao dịch bị lỗi.',
            '09' => 'Thẻ/Tài khoản của khách hàng chưa được đăng ký dịch vụ InternetBanking.',
            '10' => 'Khách hàng xác thực thông tin của thẻ/Tài khoản không đúng quá 3 lần.',
            '11' => 'Đã hết hạn chờ thanh toán.',
            '12' => 'Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Khách hàng hủy thanh toán.',
            '24' => 'Khách hàng hủy giao dịch bằng OTP.',
            '51' => 'Tài khoản của khách hàng không đủ số dư để thanh toán.',
            '65' => 'Tài khoản của khách hàng đã vượt số hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Khách hàng nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Lỗi không xác định.',
            default => 'Giao dịch không thành công (Mã: ' . $code . ').',
        };
    }
}
