<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use PHPUnit\Framework\TestCase;
use SneakerShop\Payment\OrderPayment;
use SneakerShop\Payment\OrderPaymentConfirmation;

require_once __DIR__ . '/../../vnpay/OrderPaymentConfirmation.php';

/**
 * @group part4
 * @group unit
 */
final class OrderPaymentConfirmationTest extends TestCase
{
    private const SECRET = 'unit-test-secret';
    private const DEADLINE = '2026-11-25 14:00:00';
    private OrderPayment $order;
    private OrderPaymentConfirmation $service;

    protected function setUp(): void
    {
        $this->order = new OrderPayment('DH001', 2700000,
            'pending_payment', 'pending', self::DEADLINE);
        $this->service = new OrderPaymentConfirmation();
    }

    private function signed(array $overrides = []): array
    {
        $params = array_replace([
            'vnp_TxnRef' => 'DH001', 'vnp_Amount' => '270000000',
            'vnp_ResponseCode' => '00', 'vnp_TransactionStatus' => '00',
        ], $overrides);
        ksort($params);
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = urlencode($key) . '=' . urlencode($value);
        }
        $params['vnp_SecureHash'] = hash_hmac('sha512', implode('&', $parts), self::SECRET);
        return $params;
    }

    private function confirm(array $params): string
    {
        return $this->service->confirm($this->order, $params, self::SECRET);
    }

    private function assertPending(): void
    {
        $this->assertSame('pending_payment', $this->order->status);
        $this->assertSame('pending', $this->order->paymentStatus);
        $this->assertSame(self::DEADLINE, $this->order->paymentDeadline);
    }

    public function testValidPaymentConfirmsOrder(): void
    {
        $this->assertSame('00', $this->confirm($this->signed()));
        $this->assertSame('confirmed', $this->order->status);
        $this->assertSame('paid', $this->order->paymentStatus);
        $this->assertNull($this->order->paymentDeadline);
    }

    public function testInvalidSignatureDoesNotConfirmOrder(): void
    {
        $params = $this->signed();
        $params['vnp_SecureHash'] = str_repeat('0', 128);
        $this->assertSame('97', $this->confirm($params));
        $this->assertPending();
    }

    public function testUnknownOrderIsRejected(): void
    {
        $params = $this->signed(['vnp_TxnRef' => 'UNKNOWN']);
        $this->assertSame('01', $this->service->confirm(null, $params, self::SECRET));
        $this->assertSame('01', $this->confirm($params));
        $this->assertPending();
    }

    public function testAmountMismatchDoesNotConfirmOrder(): void
    {
        $this->assertSame('99', $this->confirm($this->signed(['vnp_Amount' => '100000'])));
        $this->assertPending();
    }

    public function testRepeatedConfirmationIsIdempotent(): void
    {
        $params = $this->signed();
        $this->assertSame('00', $this->confirm($params));
        $this->assertSame('confirmed', $this->order->status);
        $this->assertSame('paid', $this->order->paymentStatus);
        $this->assertNull($this->order->paymentDeadline);
        $before = clone $this->order;
        $this->assertSame('00', $this->confirm($params));
        $this->assertEquals($before, $this->order);
    }

    public function testFailedPaymentKeepsOrderPending(): void
    {
        $params = $this->signed(['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '02']);
        $this->assertSame('00', $this->confirm($params));
        $this->assertPending();
    }
}
