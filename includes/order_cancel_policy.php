<?php

function canCustomerCancelOrder(
    string $paymentMethod,
    string $status,
    array $pendingPaymentStatuses
): bool {
    // Each payment method has its own exit: no cross-method path.
    switch ($paymentMethod) {
        case 'cash':
            return $status === 'pending';
        case 'online':
            return in_array($status, $pendingPaymentStatuses, true);
        default:
            return false;
    }
}
