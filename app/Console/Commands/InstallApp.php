<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;
use Throwable;

class InstallApp extends Command
{
    protected $signature   = 'app:install';
    protected $description = 'Run the FDE Admission Portal first-time setup wizard';

    public function handle(): int
    {
        $this->printHeader();

        // ── Already-installed guard ───────────────────────────────────────
        if ($this->isAlreadyInstalled()) {
            $this->warn('  ⚠  A .env with an APP_KEY already exists.');
            if (! $this->confirm('  The system appears to be installed. Continue and overwrite settings?', false)) {
                $this->info('  Aborted.');
                return self::SUCCESS;
            }
        }

        // ── Gather app config ─────────────────────────────────────────────
        $this->line('');
        $this->line('  <fg=cyan>── Application Settings ──────────────────────────────</>');
        $this->line('');

        $appName = $this->ask('  App name', 'FDE Admission Portal 2026');
        $appUrl  = $this->ask('  App URL (no trailing slash)', 'http://localhost');
        $appEnv  = $this->choice('  Environment', ['production', 'local'], 0);

        // ── Gather database config ────────────────────────────────────────
        $this->line('');
        $this->line('  <fg=cyan>── Database Settings ─────────────────────────────────</>');
        $this->line('');

        $retries = 0;
        $pdo     = null;

        while (true) {
            $dbHost = $this->ask('  DB Host', '127.0.0.1');
            $dbPort = $this->ask('  DB Port', '3306');
            $dbName = $this->ask('  DB Name', 'fde_admission_2026');
            $dbUser = $this->ask('  DB Username', 'root');
            $dbPass = $this->secret('  DB Password (leave blank if none)') ?? '';

            $this->line('');
            $this->info('  Testing database connection...');

            try {
                // Connect without database name to allow CREATE DATABASE
                $pdo = new PDO(
                    "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
                );
                $this->line('  <fg=green>✓ Connection successful.</>');
                break;
            } catch (PDOException $e) {
                $this->error('  Connection failed: ' . $e->getMessage());
                $retries++;
                if ($retries >= 3 || ! $this->confirm('  Retry with different credentials?', true)) {
                    $this->error('  Cannot connect to MySQL. Aborting.');
                    return self::FAILURE;
                }
            }
        }

        // ── Create database ───────────────────────────────────────────────
        $this->line('');
        $this->info('  Creating database if it does not exist...');
        try {
            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            $this->line('  <fg=green>✓ Database ready.</>');
        } catch (PDOException $e) {
            $this->error('  Failed to create database: ' . $e->getMessage());
            return self::FAILURE;
        }

        // ── Write .env ────────────────────────────────────────────────────
        $this->line('');
        $this->info('  Writing .env file...');
        $this->writeEnv([
            'APP_NAME'         => "\"{$appName}\"",
            'APP_ENV'          => $appEnv,
            'APP_DEBUG'        => $appEnv === 'production' ? 'false' : 'true',
            'APP_URL'          => $appUrl,
            'LOG_LEVEL'        => $appEnv === 'production' ? 'warning' : 'debug',
            'DB_CONNECTION'    => 'mysql',
            'DB_HOST'          => $dbHost,
            'DB_PORT'          => $dbPort,
            'DB_DATABASE'      => $dbName,
            'DB_USERNAME'      => $dbUser,
            'DB_PASSWORD'      => $dbPass,
            'MAIL_FROM_ADDRESS'=> 'noreply@fde.edu.pk',
            'MAIL_FROM_NAME'   => '"FDE Admission Portal"',
        ]);
        $this->line('  <fg=green>✓ .env written.</>');

        // ── Generate app key ──────────────────────────────────────────────
        $this->line('');
        $this->info('  Generating application key...');
        Artisan::call('key:generate', ['--force' => true]);
        $this->line('  <fg=green>✓ Key generated.</>');

        // ── Run migrations ────────────────────────────────────────────────
        $this->line('');
        $this->info('  Running database migrations...');
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->line('  <fg=green>✓ Migrations complete.</>');
        } catch (Throwable $e) {
            $this->error('  Migration failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // ── Seed master data ──────────────────────────────────────────────
        $this->line('');
        $this->info('  Seeding master data...');

        $seeders = [
            'RolesSeeder'              => 'Roles & Permissions (106 permissions, 4 roles)',
            'ClassesSeeder'            => 'Classes (Nursery → Class 12)',
            'AcademicYearSeeder'       => 'Academic Year (2026-27)',
            'AdminUserSeeder'          => 'Admin user (admin@fde.gov.pk)',
            'HoiUsersSeeder'           => 'HoI accounts (430 school heads — password: Test@1234)',
            'SectorSeeder'             => 'Sectors (7)',
            'UnionCouncilSeeder'       => 'Union Councils',
            'InstitutionSeeder'        => 'Institutions (432 schools)',
            'NewConstructionRoomsSeeder' => 'New Construction Rooms',
            'UcControlRoomSeeder'      => 'UC Control Rooms',
            'ModelCollegeSeeder'       => 'Model Colleges',
        ];

        foreach ($seeders as $class => $label) {
            try {
                Artisan::call('db:seed', [
                    '--class' => "Database\\Seeders\\{$class}",
                    '--force' => true,
                ]);
                $this->line("  <fg=green>✓</> {$label}");
            } catch (Throwable $e) {
                $this->warn("  ⚠ {$label} — {$e->getMessage()}");
            }
        }

        // ── Storage link ──────────────────────────────────────────────────
        $this->line('');
        $this->info('  Creating storage symlink...');
        try {
            Artisan::call('storage:link');
            $this->line('  <fg=green>✓ Storage link created.</>');
        } catch (Throwable) {
            $this->line('  <fg=yellow>⚠ Storage link already exists or could not be created.</>');
        }

        // ── Cache / optimise (production only) ────────────────────────────
        if ($appEnv === 'production') {
            $this->line('');
            $this->info('  Optimising for production...');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $this->line('  <fg=green>✓ Config, routes, and views cached.</>');
        }

        // ── Success banner ────────────────────────────────────────────────
        $loginUrl = rtrim($appUrl, '/') . '/login';

        $this->line('');
        $this->line('  <fg=green>╔══════════════════════════════════════════════════════╗</>');
        $this->line('  <fg=green>║      ✅  FDE Admission Portal — Ready!               ║</>');
        $this->line('  <fg=green>╠══════════════════════════════════════════════════════╣</>');
        $this->line("  <fg=green>║</>  URL:      <fg=yellow>{$loginUrl}</>                ");
        $this->line('  <fg=green>║</>  Email:    <fg=yellow>admin@fde.gov.pk</>           ');
        $this->line('  <fg=green>║</>  Password: <fg=yellow>Admin@1234</>                 ');
        $this->line('  <fg=green>║</>                                                     ');
        $this->line('  <fg=green>║</>  <fg=red>⚠  Change the admin password after first login!</>');
        $this->line('  <fg=green>╚══════════════════════════════════════════════════════╝</>');
        $this->line('');

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function printHeader(): void
    {
        $this->line('');
        $this->line('  <fg=cyan;options=bold>════════════════════════════════════════════════════</>');
        $this->line('  <fg=cyan;options=bold>   FDE Admission Portal 2026 — Setup Wizard          </>');
        $this->line('  <fg=cyan;options=bold>════════════════════════════════════════════════════</>');
        $this->line('');
    }

    private function isAlreadyInstalled(): bool
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return false;
        }
        $contents = file_get_contents($envPath);
        return str_contains($contents, 'APP_KEY=base64:');
    }

    /**
     * Read .env.example → .env, then overwrite the given key-value pairs.
     * Creates .env from .env.example if it doesn't already exist.
     */
    private function writeEnv(array $values): void
    {
        $envPath     = base_path('.env');
        $examplePath = base_path('.env.example');

        // Start from .env.example so all keys are present
        $source   = file_exists($examplePath) ? $examplePath : $envPath;
        $contents = file_get_contents($source);

        foreach ($values as $key => $value) {
            // If key exists in file — replace its value
            if (preg_match("/^{$key}=.*/m", $contents)) {
                $contents = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $contents
                );
            } else {
                // Key doesn't exist — append it
                $contents .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $contents);
    }
}
