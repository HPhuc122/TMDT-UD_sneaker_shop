<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/order_cancel_policy.php';

final class OrderCancelPolicyTest extends TestCase
{
    private array $pendingStatuses = [
        'pending_payment',
        'awaiting_payment',
    ];

    public function testCashPendingCanBeCancelled(): void
    {
        $actual = canCustomerCancelOrder(
            'cash',
            'pending',
            $this->pendingStatuses
        );

        $this->assertTrue($actual);
    }

    public function testCashConfirmedCannotBeCancelled(): void
    {
        $actual = canCustomerCancelOrder(
            'cash',
            'confirmed',
            $this->pendingStatuses
        );

        $this->assertFalse($actual);
    }

    public function testOnlinePendingPaymentCanBeCancelled(): void
    {
        $actual = canCustomerCancelOrder(
            'online',
            'pending_payment',
            $this->pendingStatuses
        );

        $this->assertTrue($actual);
    }

    public function testOnlineAwaitingPaymentCanBeCancelled(): void
    {
        $actual = canCustomerCancelOrder(
            'online',
            'awaiting_payment',
            $this->pendingStatuses
        );

        $this->assertTrue($actual);
    }

    public function testOnlineConfirmedCannotBeCancelled(): void
    {
        $actual = canCustomerCancelOrder(
            'online',
            'confirmed',
            $this->pendingStatuses
        );

        $this->assertFalse($actual);
    }

    public function testTransferPendingCannotBeCancelled(): void
    {
        $actual = canCustomerCancelOrder(
            'transfer',
            'pending',
            $this->pendingStatuses
        );

        $this->assertFalse($actual);
    }

    /** @dataProvider cancellationMatrix */
    public function testCancellationPolicyMatrix(string $method, string $status, array $pending, bool $expected): void
    {
        $this->assertSame($expected, canCustomerCancelOrder($method, $status, $pending));
    }

    public static function cancellationMatrix(): array
    {
        $pending = ['pending_payment', 'awaiting_payment'];
        $cases = [];
        // Explicit expected results, independent of the implementation.
        $expectedByMethod = [
            'cash' => [true, false, false, false, false, false],
            'online' => [false, true, true, false, false, false],
            'transfer' => [false, false, false, false, false, false],
        ];
        $statuses = ['pending', 'pending_payment', 'awaiting_payment', 'confirmed', 'delivered', 'cancelled'];
        foreach ($expectedByMethod as $method => $expected) {
            foreach ($statuses as $index => $status) {
                $cases[$method . '_' . $status] = [$method, $status, $pending, $expected[$index]];
            }
        }
        $cases['online_legacy_pending'] = ['online', 'pending', ['pending'], true];
        $cases['online_empty_configuration'] = ['online', 'pending', [], false];
        $cases['cash_ignores_online_configuration'] = ['cash', 'pending', [], true];
        $cases['unknown_method'] = ['unknown', 'pending', $pending, false];
        $cases['empty_method'] = ['', 'pending', $pending, false];
        $cases['strict_status_comparison'] = ['online', '0', [0], false];
        return $cases;
    }
}