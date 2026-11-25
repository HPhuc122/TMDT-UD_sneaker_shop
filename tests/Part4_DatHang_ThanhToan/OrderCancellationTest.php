<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use PHPUnit\Framework\TestCase;
use SneakerShop\Order\CancellableOrder;
use SneakerShop\Order\OrderCancellation;

require_once dirname(__DIR__, 2) . '/includes/OrderCancellation.php';

/** @group part4
 * @group unit
 */
final class OrderCancellationTest extends TestCase
{
    /** @dataProvider cancellationProvider */
    public function testCancellationRules(string $method, string $status, bool $allowed): void
    {
        $items = ['2:1:2' => 2, '1:1:1' => 1];
        $order = new CancellableOrder(2, $method, $status, $items);
        $result = (new OrderCancellation())->cancel($order, 2);

        $this->assertSame($allowed ? 'cancelled' : 'not_cancellable', $result['result']);
        $this->assertSame($allowed ? 'cancelled' : $status, $order->status);
        $this->assertSame($allowed ? $items : [], $result['restock']);
    }

    public static function cancellationProvider(): array
    {
        return [
            'COD pending' => ['cash', 'pending', true],
            'COD confirmed' => ['cash', 'confirmed', false],
            'Online pending payment' => ['online', 'pending_payment', true],
            'Online confirmed' => ['online', 'confirmed', false],
            'Transfer pending' => ['transfer', 'pending', false],
        ];
    }

    public function testMissingOrderIsRejected(): void
    {
        $this->assertSame(['result' => 'not_found', 'restock' => []],
            (new OrderCancellation())->cancel(null, 2));
    }

    public function testOtherUsersOrderIsRejected(): void
    {
        $order = new CancellableOrder(3, 'cash', 'pending', ['2:1:2' => 2]);
        $result = (new OrderCancellation())->cancel($order, 2);
        $this->assertSame('not_found', $result['result']);
        $this->assertSame('pending', $order->status);
        $this->assertSame([], $result['restock']);
    }

    public function testRepeatedCancellationDoesNotRestockTwice(): void
    {
        $service = new OrderCancellation();
        $order = new CancellableOrder(2, 'cash', 'pending', ['2:1:2' => 2]);
        $first = $service->cancel($order, 2);
        $second = $service->cancel($order, 2);
        $this->assertSame('cancelled', $first['result']);
        $this->assertSame(['2:1:2' => 2], $first['restock']);
        $this->assertSame('not_cancellable', $second['result']);
        $this->assertSame([], $second['restock']);
        $this->assertSame('cancelled', $order->status);
    }
}
