<?php

namespace App\Console\Commands;

use App\Models\Institution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pulls all FDE schools (ImplementingAgencyID=57) from NFEMIS
 * and writes their SchoolCode into institutions.emis_code
 * by matching on school name (case-insensitive, trimmed).
 *
 * Usage:
 *   php artisan nfemis:sync-schools          -- dry run (shows matches, writes nothing)
 *   php artisan nfemis:sync-schools --write  -- actually saves to DB
 */
class SyncNfemisSchools extends Command
{
    protected $signature   = 'nfemis:sync-schools {--write : Save matches to institutions.emis_code}';
    protected $description = 'Match NFEMIS FDE schools to portal institutions by name and sync EMIS codes';

    public function handle(): void
    {
        $write = $this->option('write');
        $this->info('Fetching FDE schools from NFEMIS (AgencyID=57)...');

        $nfemisSchools = DB::connection('nfemis')
            ->table('School')
            ->where('ImplementingAgencyID', 57)
            ->select('SchoolID', 'SchoolCode', 'SchoolName')
            ->orderBy('SchoolID')
            ->get();

        $this->info("Found {$nfemisSchools->count()} FDE schools in NFEMIS.");

        $institutions = Institution::where('is_active', true)->get();
        $this->info("Found {$institutions->count()} active institutions in portal.");
        $this->line('');

        $matched   = 0;
        $unmatched = [];

        foreach ($nfemisSchools as $ns) {
            $nName = $this->normalize($ns->SchoolName);

            // Try exact normalized match first
            $inst = $institutions->first(fn($i) => $this->normalize($i->name) === $nName);

            // Fallback: contains match
            if (!$inst) {
                $inst = $institutions->first(function ($i) use ($nName) {
                    $pName = $this->normalize($i->name);
                    return str_contains($nName, $pName) || str_contains($pName, $nName);
                });
            }

            if ($inst) {
                $matched++;
                $this->line("MATCH: [{$ns->SchoolCode}] {$ns->SchoolName}  =>  [{$inst->id}] {$inst->name}");
                if ($write) {
                    $inst->update(['emis_code' => trim($ns->SchoolCode)]);
                }
            } else {
                $unmatched[] = "[{$ns->SchoolCode}] {$ns->SchoolName}";
            }
        }

        $this->line('');
        $this->info("Matched: {$matched} / {$nfemisSchools->count()}");
        $this->warn('Unmatched: ' . count($unmatched));
        foreach (array_slice($unmatched, 0, 20) as $u) {
            $this->line("  UNMATCHED: {$u}");
        }

        if (!$write) {
            $this->line('');
            $this->comment('Run with --write to save matches to DB.');
        } else {
            $this->info('emis_code saved for all matched institutions.');
        }
    }

    private function normalize(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
