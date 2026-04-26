<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sector;
use App\Models\UnionCouncil;
use App\Models\Institution;

class CheckInstitutionLinks extends Command
{
    protected $signature   = 'check:institution-links {--fix : Auto-fix sector_id on institutions from their UC}';
    protected $description = 'Audit institution → UC → Sector linkage integrity';

    public function handle(): int
    {
        $this->info('');
        $this->info('══════════════════════════════════════════════════════════════');
        $this->info('  Institution ↔ UC ↔ Sector Integrity Check');
        $this->info('══════════════════════════════════════════════════════════════');

        $fix     = $this->option('fix');
        $hasIssue = false;

        // ── 1. UCs with no Sector assigned ────────────────────────────────
        $this->line('');
        $this->line('<fg=yellow>① UCs with no Sector (sector_id IS NULL)</>');

        $orphanUCs = UnionCouncil::whereNull('sector_id')->get();
        if ($orphanUCs->isEmpty()) {
            $this->line('   ✅ All UCs have a sector assigned.');
        } else {
            $hasIssue = true;
            $rows = $orphanUCs->map(fn($uc) => [$uc->id, $uc->code, $uc->name, $uc->institutions()->count().' schools'])->toArray();
            $this->table(['ID', 'Code', 'Name', 'Schools'], $rows);
        }

        // ── 2. Institutions with no UC ─────────────────────────────────────
        $this->line('');
        $this->line('<fg=yellow>② Institutions with no UC (uc_id IS NULL)</>');

        $noUC = Institution::whereNull('uc_id')->get();
        if ($noUC->isEmpty()) {
            $this->line('   ✅ All institutions have a UC assigned.');
        } else {
            $hasIssue = true;
            $rows = $noUC->map(fn($i) => [
                $i->id,
                $i->name,
                $i->sector?->name ?? '—',
                $i->type,
            ])->toArray();
            $this->table(['ID', 'Institution', 'Sector', 'Type'], $rows);
        }

        // ── 3. Institutions with no Sector ────────────────────────────────
        $this->line('');
        $this->line('<fg=yellow>③ Institutions with no Sector (sector_id IS NULL)</>');

        $noSector = Institution::whereNull('sector_id')->get();
        if ($noSector->isEmpty()) {
            $this->line('   ✅ All institutions have a sector assigned.');
        } else {
            $hasIssue = true;
            $rows = $noSector->map(fn($i) => [
                $i->id,
                $i->name,
                $i->unionCouncil?->code ?? '—',
                $i->type,
            ])->toArray();
            $this->table(['ID', 'Institution', 'UC', 'Type'], $rows);
        }

        // ── 4. Sector/UC Mismatch ─────────────────────────────────────────
        // institution.sector_id ≠ institution.uc.sector_id
        $this->line('');
        $this->line('<fg=yellow>④ Sector ↔ UC Mismatch (institution.sector_id ≠ uc.sector_id)</>');

        $mismatches = Institution::with(['sector', 'unionCouncil.sector'])
            ->whereNotNull('uc_id')
            ->whereNotNull('sector_id')
            ->get()
            ->filter(function ($inst) {
                $ucSectorId = $inst->unionCouncil?->sector_id;
                return $ucSectorId && $inst->sector_id !== $ucSectorId;
            });

        if ($mismatches->isEmpty()) {
            $this->line('   ✅ No sector/UC mismatches found.');
        } else {
            $hasIssue = true;
            $rows = $mismatches->map(fn($i) => [
                $i->id,
                $i->name,
                $i->unionCouncil?->code ?? '—',
                $i->unionCouncil?->sector?->name ?? '—',  // UC's sector
                $i->sector?->name ?? '—',                  // institution's own sector_id
                $fix ? '🔧 FIXED' : '⚠ MISMATCH',
            ])->toArray();
            $this->table(
                ['ID', 'Institution', 'UC', 'UC→Sector (correct)', 'Inst. Sector (wrong)', 'Status'],
                $rows
            );

            if ($fix) {
                foreach ($mismatches as $inst) {
                    $correctSectorId = $inst->unionCouncil->sector_id;
                    $inst->update(['sector_id' => $correctSectorId]);
                }
                $this->info("   ✅ Fixed {$mismatches->count()} institution(s) — sector_id now matches UC.");
            }
        }

        // ── 5. Sector → UC → Institution summary ─────────────────────────
        $this->line('');
        $this->line('<fg=cyan>⑤ Sector → UC → School count summary</>');
        $this->line('');

        $sectors = Sector::with(['unionCouncils.institutions'])->orderBy('name')->get();

        foreach ($sectors as $sector) {
            $totalSchools = $sector->unionCouncils->sum(fn($uc) => $uc->institutions->count());
            $this->line("  <fg=blue>▌ {$sector->name}</> ({$sector->code}) — {$totalSchools} schools across {$sector->unionCouncils->count()} UCs");

            foreach ($sector->unionCouncils->sortBy('code') as $uc) {
                $cnt = $uc->institutions->count();
                $bar = str_repeat('█', min(20, $cnt));
                $this->line(sprintf(
                    "    %-6s %-35s %3d schools  %s",
                    $uc->code,
                    mb_strimwidth($uc->name, 0, 35, '…'),
                    $cnt,
                    $bar
                ));
            }
            $this->line('');
        }

        // ── 6. UCs not linked to any Sector (from summary) ────────────────
        $ucWithNoSectorInSummary = UnionCouncil::whereNull('sector_id')->withCount('institutions')->get();
        if ($ucWithNoSectorInSummary->isNotEmpty()) {
            $this->line('  <fg=red>▌ NO SECTOR</> — unassigned UCs');
            foreach ($ucWithNoSectorInSummary as $uc) {
                $this->line("    {$uc->code}  {$uc->name}  [{$uc->institutions_count} schools]");
            }
            $this->line('');
        }

        // ── Grand totals ──────────────────────────────────────────────────
        $grandTotal  = Institution::count();
        $activeTotal = Institution::where('is_active', true)->count();
        $configured  = Institution::where('classes_configured', true)->count();

        $this->line('──────────────────────────────────────────────────────────────');
        $this->line("  Total institutions : <fg=green>{$grandTotal}</>");
        $this->line("  Active             : <fg=green>{$activeTotal}</>");
        $this->line("  Classes configured : <fg=green>{$configured}</>");
        $this->line('──────────────────────────────────────────────────────────────');

        if ($hasIssue) {
            $this->warn('  ⚠  Issues found above. Run with --fix to auto-correct sector mismatches.');
            return self::FAILURE;
        }

        $this->info('  ✅ All institution ↔ UC ↔ Sector links are clean.');
        return self::SUCCESS;
    }
}
