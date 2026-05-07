<?php

namespace Tests\Feature;

use App\Jobs\SendAdmissionSmsJob;
use App\Models\Admission;
use App\Models\School;
use App\Models\SchoolSeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdmissionApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-api-key-admissions';
    private const YEAR    = '2024-25';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.nfemis.api_key', self::API_KEY);
        Http::fake(); // prevent real SMS calls
    }

    private function apiKey(): array
    {
        return ['X-Api-Key' => self::API_KEY];
    }

    private function makeSchoolWithVacancy(int $occupied = 10): array
    {
        $school = School::create([
            'name'              => 'Test School ' . uniqid(),
            'address'           => 'G-9 Islamabad',
            'principal_name'    => 'Mr. Test',
            'principal_contact' => '03001111111',
            'emis_code'         => 'FDE-T-' . uniqid(),
        ]);

        $seat = SchoolSeat::create([
            'school_id'      => $school->id,
            'class_name'     => 'Class 1',
            'total_seats'    => 40,
            'occupied_seats' => $occupied,
            'academic_year'  => self::YEAR,
        ]);

        return [$school, $seat];
    }

    private function validPayload(int $schoolId): array
    {
        return [
            'child_name'    => 'Ahmed Ali',
            'child_dob'     => '2018-03-15',
            'child_gender'  => 'male',
            'parent_name'   => 'Muhammad Ali',
            'parent_contact'=> '03001234567',
            'school_id'     => $schoolId,
            'class_name'    => 'Class 1',
            'referral_date' => today()->toDateString(),
        ];
    }

    /** Test 1: 201 on successful admission creation. */
    public function test_creates_admission_and_returns_201(): void
    {
        Queue::fake();

        [$school] = $this->makeSchoolWithVacancy();

        $response = $this->postJson('/api/v1/admissions', $this->validPayload($school->id), $this->apiKey());

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonStructure(['data' => ['ref_id', 'id', 'child_name', 'school']]);

        Queue::assertPushed(SendAdmissionSmsJob::class);
    }

    /** Test 2: 422 when no vacancies available. */
    public function test_returns_422_when_no_vacancies(): void
    {
        [$school] = $this->makeSchoolWithVacancy(40); // fully occupied

        $response = $this->postJson('/api/v1/admissions', $this->validPayload($school->id), $this->apiKey());

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** Test 3: Duplicate nfemis_referral_id is rejected with 422. */
    public function test_duplicate_nfemis_referral_id_returns_422(): void
    {
        Queue::fake();
        [$school] = $this->makeSchoolWithVacancy();

        // First admission
        $payload = array_merge($this->validPayload($school->id), ['nfemis_referral_id' => 999]);
        $this->postJson('/api/v1/admissions', $payload, $this->apiKey())->assertStatus(201);

        // Add another vacancy for the second attempt
        SchoolSeat::create([
            'school_id'      => $school->id,
            'class_name'     => 'Class 1',
            'total_seats'    => 40,
            'occupied_seats' => 5,
            'academic_year'  => '2025-26',
        ]);

        // Duplicate referral_id (same school, but should fail on unique constraint)
        $response = $this->postJson('/api/v1/admissions', $payload, $this->apiKey());

        $response->assertStatus(422);
    }

    /** Test 4: GET by ref_id returns the admission. */
    public function test_get_admission_by_ref_id(): void
    {
        Queue::fake();
        [$school] = $this->makeSchoolWithVacancy();

        $created = $this->postJson('/api/v1/admissions', $this->validPayload($school->id), $this->apiKey())
            ->assertStatus(201)
            ->json('data');

        $response = $this->getJson("/api/v1/admissions/{$created['ref_id']}", $this->apiKey());

        $response->assertStatus(200)
            ->assertJsonPath('data.ref_id', $created['ref_id'])
            ->assertJsonPath('data.child_name', 'Ahmed Ali');
    }

    /** Test 5: 404 for unknown ref_id. */
    public function test_get_unknown_ref_id_returns_404(): void
    {
        $response = $this->getJson('/api/v1/admissions/FDE-DOESNOTEXIST', $this->apiKey());

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    /** Test 6: Status update to confirmed returns 200. */
    public function test_update_status_to_confirmed(): void
    {
        Queue::fake();
        [$school] = $this->makeSchoolWithVacancy();

        $refId = $this->postJson('/api/v1/admissions', $this->validPayload($school->id), $this->apiKey())
            ->json('data.ref_id');

        $response = $this->putJson("/api/v1/admissions/{$refId}/status", [
            'status' => 'confirmed',
        ], $this->apiKey());

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('success', true);
    }

    /** Test 7: Status update to rejected without reason returns 422. */
    public function test_update_status_rejected_without_reason_returns_422(): void
    {
        Queue::fake();
        [$school] = $this->makeSchoolWithVacancy();

        $refId = $this->postJson('/api/v1/admissions', $this->validPayload($school->id), $this->apiKey())
            ->json('data.ref_id');

        $response = $this->putJson("/api/v1/admissions/{$refId}/status", [
            'status' => 'rejected',
            // rejected_reason intentionally missing
        ], $this->apiKey());

        $response->assertStatus(422);
    }
}
