# Symfony Patterns

## Dependency Injection

```php
<?php declare(strict_types=1);

// Autowired service with typed constructor
final class OrderService
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
    ) {
    }
}

// Service decoration
// config/services.php
$services->set(CachedOrderRepository::class)
    ->decorate(OrderRepository::class)
    ->arg('$inner', service('.inner'));
```

## Events & Listeners

```php
// Event class
final class OrderPlacedEvent
{
    public function __construct(
        public readonly Order $order,
        public readonly \DateTimeImmutable $placedAt,
    ) {
    }
}

// Listener with attribute
#[AsEventListener(event: OrderPlacedEvent::class)]
final class SendOrderConfirmation
{
    public function __invoke(OrderPlacedEvent $event): void
    {
        // Handle event
    }
}

// Subscriber
#[AsEventListener(event: OrderPlacedEvent::class, method: 'onPlace')]
#[AsEventListener(event: OrderCancelledEvent::class, method: 'onCancel')]
final class OrderNotificationListener
{
    public function onPlace(OrderPlacedEvent $event): void { }
    public function onCancel(OrderCancelledEvent $event): void { }
}
```

## Console Commands

```php
#[AsCommand(name: 'app:import-products', description: 'Import products from CSV')]
final class ImportProductsCommand extends Command
{
    public function __construct(
        private readonly ProductImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file path');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');
        $dryRun = $input->getOption('dry-run');

        $result = $this->importer->import($file, $dryRun);
        $io->success(\sprintf('Imported %d products.', $result->count()));

        return Command::SUCCESS;
    }
}
```

## Security Voters

```php
#[AsVoter]
final class OrderVoter extends Voter
{
    public const VIEW = 'ORDER_VIEW';
    public const EDIT = 'ORDER_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::EDIT], true)
            && $subject instanceof Order;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            default => false,
        };
    }

    private function canView(Order $order, User $user): bool
    {
        return $order->getCustomerId() === $user->getId();
    }

    private function canEdit(Order $order, User $user): bool
    {
        return $this->canView($order, $user) && !$order->isCompleted();
    }
}
```

## Messenger (Async)

```php
// Message
final readonly class ProcessImageMessage
{
    public function __construct(
        public int $imageId,
        public string $targetFormat,
    ) {
    }
}

// Handler
#[AsMessageHandler]
final class ProcessImageHandler
{
    public function __construct(
        private readonly ImageProcessor $processor,
    ) {
    }

    public function __invoke(ProcessImageMessage $message): void
    {
        $this->processor->convert($message->imageId, $message->targetFormat);
    }
}

// Dispatch
$this->messageBus->dispatch(new ProcessImageMessage(imageId: 42, targetFormat: 'webp'));
```

## Compiler Passes

```php
final class RegisterHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('app.handler_registry')) {
            return;
        }

        $registry = $container->getDefinition('app.handler_registry');
        $tagged = $container->findTaggedServiceIds('app.handler');

        foreach ($tagged as $id => $tags) {
            $registry->addMethodCall('register', [new Reference($id)]);
        }
    }
}
```
