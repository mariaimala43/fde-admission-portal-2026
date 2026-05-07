<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdmissionSmsJob;
use App\Jobs\SyncStatusToNfemisJob;
use App\Models\Admission;
use App\Models\SchoolSeat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdmissionController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/admissions
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nfemis_referral_id' => ['nullable', 'integer', Rule::unique('admissions', 'nfemis_referral_id')],
            'child_name'         => ['required', 'string', 'max:255'],
            'child_dob'          => ['required', 'date'],
            'child_gender'       => ['required', Rule::in(['male', 'female'])],
            'parent_name'        => ['required', 'string', 'max:255'],
            'parent_contact'     => ['required', 'string', 'max:20'],
            'school_id'          => ['required', 'integer', 'exists:schools,id'],
            'class_name'         => ['required', 'string', 'max:50'],
            'referral_date'      => ['required', 'date'],
        ]);

        // Check vacancy
        $seat = SchoolSeat::where('school_id', $validated['school_id'])
            ->where('class_name', $validated['class_name'])
            ->vacant()
            ->first();

        if (!$seat) {
            return $this->errorResponse(
                'No vacant seats available for the requested school and class.',
                422
            );
        }

        return DB::transaction(function () use ($validated, $seat) {
            $admission = new Admission();
            $refId     = $admission->generateRefId();

            $admission = Admission::create(array_merge($validated, [
                'ref_id' => $refId,
                'status' => 'pending',
            ]));

            $seat->increment('occupied_seats');

            SendAdmissionSmsJob::dispatch($admission);

            return $this->successResponse(
                $this->formatAdmission($admission->load('school')),
                'Admission referral created successfully.',
                201
            );
        });
    }

    /**
     * GET /api/v1/admissions/{ref_id}
     */
    public function show(string $refId): JsonResponse
    {
        $admission = Admission::where('ref_id', $refId)->with('school')->first();

        if (!$admission) {
            return $this->errorResponse('Record not found.', 404);
        }

        return $this->successResponse(
            $this->formatAdmission($admission),
            'Admission retrieved successfully.'
        );
    }

    /**
     * PUT /api/v1/admissions/{ref_id}/status
     */
    public function updateStatus(Request $request, string $refId): JsonResponse
    {
        $admission = Admission::where('ref_id', $refId)->first();

        if (!$admission) {
            return $this->errorResponse('Record not found.', 404);
        }

        $validated = $request->validate([
            'status'          => ['required', Rule::in(['confirmed', 'rejected'])],
            'rejected_reason' => [
                Rule::requiredIf($request->input('status') === 'rejected'),
                'nullable',
                'string',
            ],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'confirmed') {
            $updates['confirmed_at'] = now();
        }

        if ($validated['status'] === 'rejected') {
            $updates['rejected_reason'] = $validated['rejected_reason'];
        }

        $admission->update($updates);

        return $this->successResponse(
            $this->formatAdmission($admission->fresh()->load('school')),
            'Admission status updated successfully.'
        );
    }

    private function formatAdmission(Admission $admission): array
    {
        return [
            'id'                  => $admission->id,
            'ref_id'              => $admission->ref_id,
            'nfemis_referral_id'  => $admission->nfemis_referral_id,
            'child_name'          => $admission->child_name,
            'child_dob'           => $admission->child_dob?->toDateString(),
            'child_gender'        => $admission->child_gender,
            'parent_name'         => $admission->parent_name,
            'parent_contact'      => $admission->parent_contact,
            'school'              => $admission->school ? [
                'id'                => $admission->school->id,
                'name'              => $admission->school->name,
                'emis_code'         => $admission->school->emis_code,
                'address'           => $admission->school->address,
                'principal_name'    => $admission->school->principal_name,
                'principal_contact' => $admission->school->principal_contact,
            ] : null,
            'class_name'          => $admission->class_name,
            'referral_date'       => $admission->referral_date?->toDateString(),
            'status'              => $admission->status,
            'confirmed_at'        => $admission->confirmed_at?->toIso8601String(),
            'rejected_reason'     => $admission->rejected_reason,
            'sms_sent_at'         => $admission->sms_sent_at?->toIso8601String(),
            'nfemis_synced_at'    => $admission->nfemis_synced_at?->toIso8601String(),
            'created_at'          => $admission->created_at?->toIso8601String(),
            'updated_at'          => $admission->updated_at?->toIso8601String(),
        ];
    }
}
