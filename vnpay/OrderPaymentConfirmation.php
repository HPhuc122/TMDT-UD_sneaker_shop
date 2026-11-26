<?php
declare(strict_types=1);

namespace SneakerShop\Payment;

final class OrderPayment
{
    public function __construct(
        public readonly string $code,
        public readonly int $totalAmount,
        public string $status,
        public ?string $paymentStatus,
        public ?string $paymentDeadline,
        public readonly string $paymentMethod = 'online'
    ) {}
}

final class OrderPaymentConfirmation
{
    public static function hasValidSignature(array $params, string $secret): bool
    {
        $signature = $params['vnp_SecureHash'] ?? null;
        if ($secret === '' || !is_string($signature)) {
            return false;
        }
        $signed = [];
        foreach ($params as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_') && $key !== 'vnp_SecureHash') {
                if (!is_string($value) && !is_int($value)) {
                    return false;
                }
                $signed[$key] = $value;
            }
        }
        ksort($signed);
        $data = http_build_query($signed, '', '&', PHP_QUERY_RFC1738);
        return hash_equals(hash_hmac('sha512', $data, $secret), $signature);
    }

    public function confirm(?OrderPayment $order, array $params, string $secret,
        array $pendingStatuses = ['pending_payment', 'awaiting_payment']): string
    {
        if (!self::hasValidSignature($params, $secret)) {
            return '97';
        }
        if ($order === null || ($params['vnp_TxnRef'] ?? '') !== $order->code) {
            return '01';
        }
        $amount = $params['vnp_Amount'] ?? '';
        if ((!is_int($amount) && !is_string($amount)) || $order->totalAmount <= 0
            || (string) $amount !== (string) ($order->totalAmount * 100)
            || $order->paymentMethod !== 'online') {
            return '99';
        }
        if ($order->status === 'confirmed' && $order->paymentStatus === 'paid') {
            return '00';
        }
        if (!in_array($order->status, $pendingStatuses, true)) {
            return '99';
        }
        if (($params['vnp_ResponseCode'] ?? '') === '00'
            && ($params['vnp_TransactionStatus'] ?? '') === '00') {
            $order->status = 'confirmed';
            $order->paymentStatus = 'paid';
            $order->paymentDeadline = null;
        } else {
            $order->paymentStatus = 'pending';
        }
        return '00';
    }
}
