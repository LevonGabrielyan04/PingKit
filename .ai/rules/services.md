---
paths:
  - 'app/Services/**/*.php'
---

# Services

## ChunkedRequestProvider via interface
Inject App\Contracts\ChunkedRequestProviderInterface, not the concrete ChunkedRequestProvider. Binding is in AppServiceProvider::register().

## HttpCheckPoolService execute only
HttpCheckPoolService::execute() returns pool results keyed by monitor id. Persist via HttpCheckLogRepositoryInterface::writeLogs(), not the service. Network failures use status 599 (NETWORK_ERROR_STATUS_CODE). Inject ChunkedRequestProviderInterface; pass a Guzzle Client with MockHandler in tests.
