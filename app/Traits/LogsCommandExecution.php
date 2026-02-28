<?php

namespace App\Traits;

use App\Models\CommandLog;
use Carbon\Carbon;

trait LogsCommandExecution
{
    protected ?CommandLog $commandLog = null;

    protected function logStart(array $metadata = []): void
    {
        $this->commandLog = CommandLog::create([
            'command' => $this->getName(),
            'status' => 'running',
            'started_at' => Carbon::now(),
            'metadata' => $metadata ?: null,
        ]);
    }

    protected function logFinish(int $recordsAffected = 0, array $metadata = []): void
    {
        if (!$this->commandLog) {
            return;
        }

        $update = [
            'status' => 'finished',
            'finished_at' => Carbon::now(),
            'records_affected' => $recordsAffected,
        ];

        if ($metadata) {
            $existing = $this->commandLog->metadata ?? [];
            $update['metadata'] = array_merge($existing, $metadata);
        }

        $this->commandLog->update($update);
    }

    protected function logError(string $message): void
    {
        if (!$this->commandLog) {
            return;
        }

        $this->commandLog->update([
            'status' => 'failed',
            'finished_at' => Carbon::now(),
            'error_message' => $message,
        ]);
    }
}
