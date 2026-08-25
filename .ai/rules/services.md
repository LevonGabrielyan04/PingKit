---
paths:
  - 'app/Services/**/*.php'
---

# Services

## ChunkedRequestProvider via interface
Inject App\Contracts\ChunkedRequestProviderInterface, not the concrete ChunkedRequestProvider. Binding is in AppServiceProvider::register().

## HttpCheckPoolService execute only
HttpCheckPoolService::execute() returns pool results keyed by monitor id. Persist via HttpCheckLogRepositoryInterface::writeLogs(), not the service. Network failures use status 599 (NETWORK_ERROR_STATUS_CODE). Inject ChunkedRequestProviderInterface; pass a Guzzle Client with MockHandler in tests.

## HttpCheckPoolService returns HttpCheckResult DTOs
HttpCheckPoolService::execute() returns array<string, HttpCheckResult> keyed by monitor id. Build results via HttpCheckResult::fromResponse() / fromFailure(); persist with HttpCheckLogRepositoryInterface::writeLogs(). Network failures use HttpCheckResult::NETWORK_ERROR_STATUS_CODE (599); the service aliases the same constant.

## executePage for cursor-scoped pool checks
HttpCheckPoolService::executePage(?afterId, limit) runs one page via ChunkedRequestProviderInterface::requestsPage(). Use execute() only for full sweeps; Polls jobs must use executePage.
