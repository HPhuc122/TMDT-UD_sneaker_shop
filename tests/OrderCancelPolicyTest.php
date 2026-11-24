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
}