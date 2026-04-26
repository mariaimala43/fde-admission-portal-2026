<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_24_000001_create_admission_monitoring_table.php
 *
 * REPLACES the original admission_monitoring migration.
 * Drop the old migration file and use this one instead.
 *
 * Includes everything from the original schema PLUS:
 *   - total_admitted          : snapshot of DailyAdmission.regularTotal() at row creation
 *   - passed_count            : HOI entry — students who passed the test
 *   - failed_count            : HOI entry — students who failed
 *   - exempted_count          : HOI entry — students exempt from test (lower classes, etc.)
 *   - test_entry_locked       : true after HOI first saves counts (prevents silent edits)
 *   - partial_finalized       : true when batch has BOTH passed + failed students
 *   - auto_finalized_at       : set when all students passed/exempted (zero failed)
 *
 * Workflow states:
 *   draft → test_verification → merit_confirmation → doc_verification → finalized
 *   (new) partial_finalized : some passed (auto-finalized) + some failed (pending retest)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_monitoring', function (Blueprint $table) {
            $table->id();

            // ── Core foreign keys ─────────────────────────────────────────
            $table->foreignId('daily_admission_id')
                  ->unique()
                  ->constrained('daily_admissions')
                  ->cascadeOnDelete();

            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->cascadeOnDelete();

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();

            $table->date('admission_date');

            // ── Admitted count (snapshot from DailyAdmission.regularTotal()) ─
            // Populated when the monitoring row is first created (on FDE verify).
            // Used to validate: passed + failed + exempted === total_admitted
            $table->unsignedSmallInteger('total_admitted')->default(0);

            // ── Test count breakdown — entered by HOI ─────────────────────
            // All nullable — null means HOI has not entered counts yet
            $table->unsignedSmallInteger('passed_count')->nullable();
            $table->unsignedSmallInteger('failed_count')->nullable();
            $table->unsignedSmallInteger('exempted_count')->nullable();

            // ── Test entry state flags ────────────────────────────────────
            // test_entry_locked: set to true after HOI saves counts
            //   (prevents silent re-edits; FDE override required to unlock)
            // partial_finalized: true when failed_count > 0 AND (passed_count > 0 OR exempted_count > 0)
            //   shown as a distinct workflow state in the UI
            // auto_finalized_at: timestamp set when failed_count = 0 (all passed/exempted)
            $table->boolean('test_entry_locked')->default(false);
            $table->boolean('partial_finalized')->default(false);
            $table->timestamp('auto_finalized_at')->nullable();

            // ── Workflow ──────────────────────────────────────────────────
            // partial_finalized is a UI/model concept; the enum here keeps
            // FDE/AEO/Director queries simple with a dedicated value.
            $table->enum('workflow_status', [
                'draft',
                'test_verification',
                'merit_confirmation',
                'doc_verification',
                'partial_finalized',   // ← NEW: some passed, some failed
                'finalized',
            ])->default('draft')->index();

            // ── Test status (legacy single-value field — kept for FDE compat) ─
            // After count entry this is derived:
            //   all passed/exempted      → 'passed'   (or 'not_required' if exempted only)
            //   all failed               → 'failed'
            //   mixed                    → 'pending'  (splits tell the real story)
            $table->enum('test_status', [
                'not_required',
                'pending',
                'passed',
                'failed',
            ])->default('pending');
            $table->timestamp('test_updated_at')->nullable();
            $table->foreignId('test_updated_by')
                  ->nullable()->constrained('users')->nullOnDelete();

            // ── Merit (FDE only) ──────────────────────────────────────────
            $table->enum('merit_status', [
                'pending',
                'selected',
                'waiting',
                'rejected',
            ])->default('pending');
            $table->timestamp('merit_updated_at')->nullable();
            $table->foreignId('merit_updated_by')
                  ->nullable()->constrained('users')->nullOnDelete();

            // ── Documentation (parent-level — used when no splits exist) ──
            // For split-based records, doc lifecycle lives on each split row.
            // This field is kept for FDE override flows and legacy records.
            $table->enum('doc_status', [
                'pending',
                'provisional',
                'affidavit_case',
                'complete',
            ])->default('pending');
            $table->string('affidavit_path', 500)->nullable();
            $table->string('affidavit_original_name', 255)->nullable();
            $table->timestamp('doc_updated_at')->nullable();
            $table->foreignId('doc_updated_by')
                  ->nullable()->constrained('users')->nullOnDelete();

            // ── FDE override ──────────────────────────────────────────────
            $table->foreignId('doc_override_by')
                  ->nullable()->constrained('users')->nullOnDelete();
            $table->text('doc_override_reason')->nullable();
            $table->timestamp('doc_override_at')->nullable();

            // ── Finalization ──────────────────────────────────────────────
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')
                  ->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['institution_id', 'admission_date']);
            $table->index(['academic_year_id', 'workflow_status']);
            $table->index(['academic_year_id', 'doc_status']);
            $table->index(['institution_id', 'test_entry_locked']); // fast "pending count entry" queries
            $table->index('partial_finalized');                     // dashboard filter
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_monitoring');
    }
};
