<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_24_000002_create_admission_monitoring_splits_table.php
 *
 * NEW table — purely additive, no existing data touched.
 * Must run AFTER 2026_03_24_000001_create_admission_monitoring_table.php
 *
 * One child row per outcome-group within a monitoring batch.
 * e.g. 20 admitted → 'passed' split (15 students) + 'failed' split (5 students)
 *
 * split_type values:
 *   passed   — students who passed the admission test
 *              → auto-finalized immediately by the system (no merit/doc check)
 *   failed   — students who failed
 *              → informational only; re-test happens via a new DailyAdmission entry
 *   exempted — test not required (e.g. lower classes, special cases)
 *              → goes to document check stage (doc_status lifecycle applies)
 *
 * workflow_status per split:
 *   finalized       — passed (auto) or exempted whose docs are complete/not_required
 *   pending_doc     — exempted students, doc check in progress
 *   pending_retest  — failed students, waiting for re-test via new daily admission
 *   doc_complete    — FDE marked documentation complete (exempted path)
 *
 * Constraints:
 *   - One split per type per monitoring record (unique_monitoring_split_type)
 *   - split_type 'passed' and 'failed' never have doc_status (null)
 *   - split_type 'exempted' always starts with doc_status = 'pending'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_monitoring_splits', function (Blueprint $table) {
            $table->id();

            // ── Parent reference ──────────────────────────────────────────
            $table->foreignId('monitoring_id')
                  ->constrained('admission_monitoring')
                  ->cascadeOnDelete();

            // ── Split definition ──────────────────────────────────────────
            $table->enum('split_type', ['passed', 'failed', 'exempted']);
            $table->unsignedSmallInteger('student_count')->default(0);

            // ── Per-split workflow ────────────────────────────────────────
            $table->enum('workflow_status', [
                'finalized',        // passed auto-finalized OR exempted doc complete/not_required
                'pending_doc',      // exempted — doc check in progress
                'pending_retest',   // failed — waiting for re-test (informational)
                'doc_complete',     // FDE marked docs complete (exempted path only)
            ])->default('pending_doc');

            // ── Per-split documentation ───────────────────────────────────
            // NULL for 'passed' and 'failed' splits — they have no doc lifecycle.
            // 'exempted' splits start as 'pending'.
            // HOI can set: not_required | pending | provisional | affidavit_case
            // FDE can set: complete
            $table->enum('doc_status', [
                'not_required',
                'pending',
                'provisional',
                'affidavit_case',
                'complete',
            ])->nullable();

            // Affidavit upload (exempted split, affidavit_case only)
            $table->string('affidavit_path', 500)->nullable();
            $table->string('affidavit_original_name', 255)->nullable();

            // ── Timestamps ────────────────────────────────────────────────
            $table->timestamp('finalized_at')->nullable();   // set when workflow_status → finalized
            $table->timestamp('doc_updated_at')->nullable(); // set on each doc_status change

            // ── Who did what ──────────────────────────────────────────────
            $table->foreignId('created_by')
                  ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')
                  ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('doc_updated_by')
                  ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // ── Constraints ───────────────────────────────────────────────
            // One row per split_type per monitoring record
            $table->unique(['monitoring_id', 'split_type'], 'unique_monitoring_split_type');

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['monitoring_id', 'workflow_status']);
            $table->index(['monitoring_id', 'doc_status']);
            $table->index('split_type'); // filter all failed/exempted splits across school
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_monitoring_splits');
    }
};
