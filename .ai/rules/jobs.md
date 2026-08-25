---
paths:
  - 'app/Jobs/**/*.php'
---

# Jobs

## HTTP check polls use Polls queue cursors
DispatchHttpCheckPolls fans out RunHttpCheckChunk jobs onto config('monitors.polls_queue') (default Polls) with afterId+limit only—never serialize monitor rows. Chunk jobs call HttpCheckPoolService::executePage() then HttpCheckLogRepositoryInterface::writeLogs(). Both jobs are ShouldBeUniqueUntilProcessing.
