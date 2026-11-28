<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncElectionStatuses extends Command
{
    protected $signature = 'elections:sync-statuses';

    protected $description = 'Open scheduled elections and close expired elections.';

    public function handle(AuditLogger $auditLogger): int
    {
        $opened = 0;
        $closed = 0;

        Election::query()
            ->whereIn('status', ['scheduled', 'open'])
            ->orderBy('id')
            ->chunkById(100, function ($elections) use ($auditLogger, &$opened, &$closed): void {
                foreach ($elections as $election) {
                    DB::transaction(function () use ($election, $auditLogger, &$opened, &$closed): void {
                        $locked = Election::query()->lockForUpdate()->find($election->id);
                        if (! $locked) {
                            return;
                        }

                        if ($locked->ends_at->isPast() && in_array($locked->status, ['scheduled', 'open'], true)) {
                            $locked->update(['status' => 'closed']);
                            $auditLogger->record('election.closed_automatically', ['election_id' => $locked->id]);
                            $closed++;

                            return;
                        }

                        if ($locked->status === 'scheduled' && $locked->starts_at->isPast()) {
                            $locked->update(['status' => 'open']);
                            $auditLogger->record('election.opened_automatically', ['election_id' => $locked->id]);
                            $opened++;
                        }
                    });
                }
            });

        $this->info("Opened: {$opened}; closed: {$closed}.");

        return self::SUCCESS;
    }
}
