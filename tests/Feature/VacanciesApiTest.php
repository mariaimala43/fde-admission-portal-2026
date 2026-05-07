<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolSeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class VacanciesApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-api-key-vacancies';
    private const YEAR    = '2024-25';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.nfemis.api_key', self::API_KEY);
    }

    private function apiKey(): array
    {
        return ['X-Api-Key' => self::API_KEY];
    }

    /** Helper: create a school with a seat row. */
    private function makeSchoolWithSeat(array $seatOverrides = []): School
    {
        $school = School::create([
            'name'              => 'Test School ' . uniqid(),
            'address'           => 'G-9 Islamabad',
            'principal_name'    => 'Mr. Test',
            'principal_contact' => '03001111111',
            'emis_code'         => 'FDE-T-' . uniqid(),
        ]);

        SchoolSeat::create(array_merge([
            'school_id'      => $school->id,
            'class_name'     => 'Class 1',
            'total_seats'    => 40,
            'occupied_seats' => 10,
            'academic_year'  => self::YEAR,
        ], $seatOverrides));

        return $school;
    }

    /** Test 1: 401 without API key header. */
    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/schools/vacancies');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    /** Test 2: Returns schools with vacancies when authenticated. */
    public function test_returns_schools_with_vacancies(): void
    {
        $this->makeSchoolWithSeat(['occupied_seats' => 10, 'academic_year' => self::YEAR]);

        $response = $this->getJson('/api/v1/schools/vacancies?academic_year=' . self::YEAR, $this->apiKey());

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.vacant_seats', 30);
    }

    /** Test 3: Filter by class_name works. */
    public function test_filter_by_class_name(): void
    {
        $school = $this->makeSchoolWithSeat(['class_name' => 'Class 3', 'academic_year' => self::YEAR]);

        // A seat for a different class
        SchoolSeat::create([
            'school_id'      => $school->id,
            'class_name'     => 'Class 5',
            'total_seats'    => 40,
            'occupied_seats' => 5,
            'academic_year'  => self::YEAR,
        ]);

        $response = $this->getJson(
            '/api/v1/schools/vacancies?class_name=Class 3&academic_year=' . self::YEAR,
            $this->apiKey()
        );

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $row) {
            $this->assertEquals('Class 3', $row['class_name']);
        }
    }

    /** Test 4: Full schools (occupied == total) are NOT returned. */
    public function test_full_schools_not_returned(): void
    {
        // A full seat (no vacancy)
        $school = School::create([
            'name'              => 'Full School',
            'address'           => 'I-8 Islamabad',
            'principal_name'    => 'Mr. Full',
            'principal_contact' => '03009999999',
            'emis_code'         => 'FDE-FULL-' . uniqid(),
        ]);

        SchoolSeat::create([
            'school_id'      => $school->id,
            'class_name'     => 'Class 1',
            'total_seats'    => 40,
            'occupied_seats' => 40, // fully occupied
            'academic_year'  => self::YEAR,
        ]);

        $response = $this->getJson('/api/v1/schools/vacancies?academic_year=' . self::YEAR, $this->apiKey());

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $row) {
            $this->assertNotEquals($school->id, $row['school_id'], 'Full school should not appear in vacancies.');
        }
    }
}
