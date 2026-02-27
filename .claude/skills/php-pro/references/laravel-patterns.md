# Laravel Patterns

## Service Pattern

```php
<?php declare(strict_types=1);

final class OrderService
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly PaymentGateway $payment,
        private readonly EventDispatcher $events,
    ) {
    }

    public function place(PlaceOrderDTO $dto): Order
    {
        $order = Order::create([
            'user_id' => $dto->userId,
            'total' => $dto->total,
            'status' => OrderStatus::Pending,
        ]);

        $this->payment->charge($order);
        $this->events->dispatch(new OrderPlaced($order));

        return $order;
    }
}
```

## Repository Pattern

```php
final class EloquentOrderRepository implements OrderRepository
{
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Order::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function save(Order $order): void
    {
        $order->save();
    }
}
```

## API Resources

```php
final class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'total' => $this->total,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

## Form Requests

```php
final class StoreOrderRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address_id' => ['required', 'exists:addresses,id'],
        ];
    }

    public function toDTO(): PlaceOrderDTO
    {
        return new PlaceOrderDTO(
            userId: $this->user()->id,
            items: $this->validated('items'),
            shippingAddressId: $this->validated('shipping_address_id'),
        );
    }
}
```

## Jobs & Queues

```php
final class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly Order $order,
    ) {
    }

    public function handle(OrderProcessor $processor): void
    {
        $processor->process($this->order);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Order processing failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

## Middleware

```php
final class EnsureApiVersion
{
    public function handle(Request $request, Closure $next, string $version): Response
    {
        if ($request->header('Accept-Version') !== $version) {
            return response()->json(['error' => 'Unsupported API version'], 406);
        }

        return $next($request);
    }
}
```

## Eloquent Scopes & Casts

```php
final class Order extends Model
{
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'metadata' => 'array',
            'placed_at' => 'immutable_datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Processing]);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
```
