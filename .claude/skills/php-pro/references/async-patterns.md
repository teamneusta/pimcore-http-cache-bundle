# Async PHP Patterns

## Swoole HTTP Server

```php
<?php declare(strict_types=1);

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$server = new Server('0.0.0.0', 9501);

$server->set([
    'worker_num' => swoole_cpu_num(),
    'max_request' => 10000,
    'enable_coroutine' => true,
]);

$server->on('request', function (Request $request, Response $response): void {
    $response->header('Content-Type', 'application/json');
    $response->end(json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR));
});

$server->start();
```

## Swoole Coroutines

```php
use Swoole\Coroutine;

Coroutine\run(function (): void {
    $results = [];

    // Parallel execution
    $wg = new Coroutine\WaitGroup();

    $wg->add();
    Coroutine::create(function () use (&$results, $wg): void {
        $results['users'] = fetchUsers();
        $wg->done();
    });

    $wg->add();
    Coroutine::create(function () use (&$results, $wg): void {
        $results['orders'] = fetchOrders();
        $wg->done();
    });

    $wg->wait();
    // Both complete, process results
});
```

## ReactPHP Event Loop

```php
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

$server = new HttpServer(function (ServerRequestInterface $request): Response {
    return new Response(200, ['Content-Type' => 'application/json'], '{"status":"ok"}');
});

$socket = new \React\Socket\SocketServer('0.0.0.0:8080');
$server->listen($socket);

echo "Listening on port 8080\n";
```

## ReactPHP Promises

```php
use React\Promise\Deferred;

function fetchAsync(string $url): \React\Promise\PromiseInterface
{
    $deferred = new Deferred();

    $client->request('GET', $url)->then(
        fn ($response) => $deferred->resolve($response->getBody()),
        fn (\Throwable $e) => $deferred->reject($e),
    );

    return $deferred->promise();
}

// Parallel requests
\React\Promise\all([
    fetchAsync('https://api.example.com/users'),
    fetchAsync('https://api.example.com/orders'),
])->then(function (array $results): void {
    [$users, $orders] = $results;
});
```

## Fibers for Async

```php
final class AsyncTaskRunner
{
    /** @var array<Fiber> */
    private array $fibers = [];

    public function add(callable $task): void
    {
        $this->fibers[] = new Fiber($task);
    }

    public function run(): array
    {
        $results = [];

        foreach ($this->fibers as $fiber) {
            $fiber->start();
        }

        foreach ($this->fibers as $i => $fiber) {
            while (!$fiber->isTerminated()) {
                $fiber->resume();
            }
            $results[$i] = $fiber->getReturn();
        }

        return $results;
    }
}
```

## Connection Pooling (Swoole)

```php
use Swoole\Database\PDOPool;
use Swoole\Database\PDOConfig;

$pool = new PDOPool(
    (new PDOConfig())
        ->withDriver('mysql')
        ->withHost('localhost')
        ->withDbName('app')
        ->withUsername('root')
        ->withPassword('secret'),
    size: 64,
);

Coroutine\run(function () use ($pool): void {
    $pdo = $pool->get();
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([42]);
        $user = $stmt->fetch();
    } finally {
        $pool->put($pdo);
    }
});
```
