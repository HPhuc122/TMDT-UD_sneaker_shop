<?php
declare(strict_types=1);

namespace SneakerShop\Order;

final class CancellableOrder
{
    public function __construct(
        public readonly int $userId,
        public readonly string $paymentMethod,
        public string $status,
        /** @var array<string,int> Quantity by stock variant ID. */
        public readonly array $items
    ) {}
}

final class OrderCancellation
{
    /** @return array{result:string,restock:array<string,int>} */
    public function cancel(?CancellableOrder $order, int $userId): array
    {
        if ($order === null || $order->userId !== $userId) {
            return ['result' => 'not_found', 'restock' => []];
        }
        $allowed = ($order->paymentMethod === 'cash' && $order->status === 'pending')
            || ($order->paymentMethod === 'online' && $order->status === 'pending_payment');
        if (!$allowed) {
            return ['result' => 'not_cancellable', 'restock' => []];
        }
        $order->status = 'cancelled';
        return ['result' => 'cancelled', 'restock' => $order->items];
    }
}
