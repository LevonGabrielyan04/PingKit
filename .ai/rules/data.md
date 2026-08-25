---
paths:
  - 'app/Data/**/*.php'
---

# Data

## HttpCheckResult is constructed via factories
HttpCheckResult has a private constructor. Create instances only with fromResponse(monitorId, response, stats, request, errorMessage?) or fromFailure(...). Timing/header normalization lives on the DTO.
