---
paths:
  - 'app/Jobs/**/*.php'
  - app/Jobs/PruneHttpCheckLogs.php
---

# Jobs

## HTTP check polls use Polls queue cursors
DispatchHttpCheckPolls fans out RunHttpCheckChunk jobs onto config('monitors.polls_queue') (default Polls) with afterId+limit only—never serialize monitor rows. Chunk jobs call HttpCheckPoolService::executePage() then HttpCheckLogRepositoryInterface::writeLogs(). Both jobs are ShouldBeUniqueUntilProcessing.

## Daily prune of http_check_logs
PruneHttpCheckLogs runs daily (Schedule::job in routes/console.php). It calls HttpCheckLogRepositoryInterface::deleteSuccessfulOlderThan48Hours() then deleteOlderThanOneMonth(). Keep retention logic in the repository, not ad-hoc Model::query() deletes in the job.
