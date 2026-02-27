# Testing & Quality

## PHPUnit

```php
<?php declare(strict_types=1);

final class OrderServiceTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<OrderRepository> */
    private ObjectProphecy $repository;
    private OrderService $service;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(OrderRepository::class);
        $this->service = new OrderService($this->repository->reveal());
    }

    /** @test */
    public function it_creates_order_with_valid_items(): void
    {
        $dto = new PlaceOrderDTO(userId: 1, items: [['product_id' => 1, 'quantity' => 2]]);

        $order = $this->service->place($dto);

        self::assertSame(1, $order->getUserId());
        $this->repository->save(Argument::type(Order::class))->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function it_rejects_empty_order(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->place(new PlaceOrderDTO(userId: 1, items: []));
    }
}
```

## Data Providers

```php
/** @test @dataProvider invalidEmailProvider */
public function it_rejects_invalid_emails(string $email): void
{
    $this->expectException(ValidationException::class);

    Email::fromString($email);
}

public static function invalidEmailProvider(): iterable
{
    yield 'empty string' => [''];
    yield 'no at sign' => ['invalid'];
    yield 'no domain' => ['user@'];
    yield 'no local part' => ['@domain.com'];
    yield 'spaces' => ['user @domain.com'];
}
```

## Pest (Alternative)

```php
describe('OrderService', function (): void {
    beforeEach(function (): void {
        $this->repository = Mockery::mock(OrderRepository::class);
        $this->service = new OrderService($this->repository);
    });

    it('creates order with valid items', function (): void {
        $this->repository->shouldReceive('save')->once();

        $order = $this->service->place($dto);

        expect($order->getUserId())->toBe(1);
    });

    it('rejects empty order')->throws(InvalidArgumentException::class);
});
```

## PHPStan Configuration

```neon
# phpstan.neon
parameters:
    level: 9
    paths:
        - src
    treatPhpDocTypesAsCertain: false
    reportUnmatchedIgnoredErrors: true
    checkGenericClassInNonGenericObjectType: true
    checkMissingIterableValueType: true
```

### Common PHPStan Fixes

```php
// Level 9: generics required
/** @var array<string, mixed> */     // not just array
/** @return list<Order> */           // not just array
/** @param Collection<int, User> */  // not just Collection

// Level 9: strict comparisons
if ($value === null) { }             // not ==
if (\in_array($item, $list, true))   // strict flag required

// Level 9: dead code
// Remove unreachable branches, unused variables
```

## Integration Testing (Symfony)

```php
final class OrderApiTest extends WebTestCase
{
    /** @test */
    public function it_creates_order_via_api(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['items' => [['product_id' => 1, 'quantity' => 2]]], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains(['status' => 'pending']);
    }
}
```

## Test Coverage

```xml
<!-- phpunit.xml -->
<coverage>
    <include>
        <directory suffix=".php">src</directory>
    </include>
    <report>
        <html outputDirectory="coverage"/>
        <text outputFile="php://stdout" showOnlySummary="true"/>
    </report>
</coverage>
```

Target: 80%+ line coverage. Focus on business logic, skip getters/configuration.

## Quality Pipeline

```bash
# Run in this order
composer cs:fix          # Fix code style
composer phpstan         # Static analysis (level 9)
composer tests           # All tests
composer tests:coverage  # With coverage report
```
