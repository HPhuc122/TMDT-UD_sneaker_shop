<?php

function canCustomerCancelOrder(
    string $paymentMethod,
    string $status,
    array $pendingPaymentStatuses
): bool {
    return ($paymentMethod === 'cash' && $status === 'pending')
        || (
            $paymentMethod === 'online'
            && in_array($status, $pendingPaymentStatuses, true)
        );
}
