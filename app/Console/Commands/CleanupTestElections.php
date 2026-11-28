<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupTestElections extends Command
{
    protected $signature = 'elections:cleanup-test {--days=30 : Minimum age in days} {--force : Delete eligible test elections}';

    protected $description = 'List or remove closed test elections older than the retention period.';

    public function handle(AuditLogger $auditLogger): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);
        if ($days === false || $days < 1) {
            $this->error('The --days option must be a positive integer.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $elections = Election::query()
            ->where('is_test', true)
            ->where('status', 'closed')
            ->where('ends_at', '<', $cutoff)
            ->orderBy('id')
            ->get();

        if (! $this->option('force')) {
            $this->info("Dry run: {$elections->count()} closed test election(s) are eligible for cleanup.");

            return self::SUCCESS;
        }

        $cleaned = 0;
        foreach ($elections as $election) {
            $photoPaths = $election->positions()
                ->with('candidates:id,position_id,photo')
                ->get()
                ->flatMap(fn ($position) => $position->candidates->pluck('photo'))
                ->filter()
                ->all();

            $deleted = DB::transaction(function () use ($election, $cutoff, $auditLogger): bool {
                $locked = Election::query()->lockForUpdate()->find($election->id);
                if (! $locked
                    || ! $locked->is_test
                    || $locked->status !== 'closed'
                    || ! $locked->ends_at->lt($cutoff)) {
                    return false;
                }

                $auditLogger->record('election.test_data_cleaned', ['election_id' => $locked->id]);
                $locked->delete();

                return true;
            });

            if ($deleted) {
                Storage::disk('public')->delete($photoPaths);
                $cleaned++;
            }
        }

        $this->info("Cleaned {$cleaned} test election(s).");

        return self::SUCCESS;
    }
}
