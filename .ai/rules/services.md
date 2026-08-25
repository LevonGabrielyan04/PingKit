---
paths:
  - 'app/Services/**/*.php'
---

# Services

## ChunkedRequestProvider via interface
Inject App\Contracts\ChunkedRequestProviderInterface, not the concrete ChunkedRequestProvider. Binding is in AppServiceProvider::register().
