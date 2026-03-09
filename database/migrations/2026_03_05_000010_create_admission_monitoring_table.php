<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_05_000010_create_admission_monitoring_table.php
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_monitoring', function (Blueprint $table) {
            $table->id();

            // ── Core links ────────────────────────────────────────────
            $table->foreignId('daily_admission_id')
                ->unique()                          // one monitoring record per admission row
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

            $table->date('admission_date');         // copied from daily_admissions, immutable for HOI

            // ── Workflow state ────────────────────────────────────────
            // Enforced transitions:
            //   draft → test_verification → merit_confirmation → doc_verification → finalized
            $table->enum('workflow_status', [
                'draft',
                'test_verification',
                'merit_confirmation',
                'doc_verification',
                'finalized',
            ])->default('draft')->index();

            // ── Admission Test Status ─────────────────────────────────
            // Rules:
            //   failed  → cannot advance to doc_status=complete
            //   passed  → eligible for merit confirmation
            $table->enum('test_status', [
                'not_required',
                'pending',
                'passed',
                'failed',
            ])->default('pending');

            $table->timestamp('test_updated_at')->nullable();
            $table->foreignId('test_updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ── Merit List Status ──────────────────────────────────────
            // Rules:
            //   must be 'selected' before doc_status can be 'complete'
            //   'rejected' blocks all further processing
            $table->enum('merit_status', [
                'pending',
                'selected',
                'waiting',
                'rejected',
            ])->default('pending');

            $table->timestamp('merit_updated_at')->nullable();
            $table->foreignId('merit_updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ── Documentation Status ───────────────────────────────────
            // Rules:
            //   'complete'     → only FDE can set (HOI can set provisional/affidavit)
            //   'provisional'  → flagged, no deadline needed
            //   'affidavit_case' → requires affidavit_path
            $table->enum('doc_status', [
                'pending',
                'provisional',
                'affidavit_case',
                'complete',
            ])->default('pending');

            // Affidavit file path (required when doc_status = affidavit_case)
            $table->string('affidavit_path', 500)->nullable();
            $table->string('affidavit_original_name', 255)->nullable();

            $table->timestamp('doc_updated_at')->nullable();
            $table->foreignId('doc_updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // FDE override tracking (when FDE overrides doc_status to complete)
            $table->foreignId('doc_override_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('doc_override_reason')->nullable();
            $table->timestamp('doc_override_at')->nullable();

            // ── Finalization ──────────────────────────────────────────
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ── Soft delete + timestamps ──────────────────────────────
            $table->softDeletes();
            $table->timestamps();

            // ── Indexes for reporting queries ─────────────────────────
            $table->index(['institution_id', 'admission_date']);
            $table->index(['academic_year_id', 'workflow_status']);
            $table->index(['academic_year_id', 'doc_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_monitoring');
    }
};
