<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdmissionEditGrantRequest;
use App\Models\AdmissionEditGrant;
use App\Models\AuditLog;
use App\Models\Institution;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdmissionEditGrantController extends Controller
{
    // ── List all grants ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Lazily expire any grants that have passed their expires_at
        AdmissionEditGrant::where('status', 'active')
            ->where('expires_at', '<', now()->timezone('Asia/Karachi'))
            ->update(['status' => 'expired']);

        $query = AdmissionEditGrant::with(['institution', 'grantedBy', 'revokedBy'])
            ->latest();

        if ($request->filled('sector_id')) {
            $query->whereHas('institution', fn($q) => $q->where('sector_id', $request->sector_id));
        }

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('expiring_soon')) {
            $query->where('status', 'active')
                  ->where('expires_at', '<=', now()->timezone('Asia/Karachi')->addHours(6));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $grants = $query->paginate(25)->withQueryString();

        $activeCount = AdmissionEditGrant::where('status', 'active')
            ->where('expires_at', '>', now()->timezone('Asia/Karachi'))
            ->count();

        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        $institutions = Institution::where('is_active', true)
            ->when($request->sector_id, fn($q) => $q->where('sector_id', $request->sector_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('fde.admission-grants.index', compact(
            'grants', 'activeCount', 'sectors', 'institutions'
        ));
    }

    // ── Show create form ───────────────────────────────────────────────────

    public function create()
    {
        $institutions = Institution::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $defaultExpiry = now()->timezone('Asia/Karachi')
            ->addHours(24)
            ->format('Y-m-d\TH:i');

        return view('fde.admission-grants.create', compact(
            'institutions', 'defaultExpiry'
        ));
    }

    // ── Store new grant ────────────────────────────────────────────────────

    public function store(StoreAdmissionEditGrantRequest $request)
    {
        $grant = AdmissionEditGrant::create([
            'institution_id' => $request->institution_id,
            'granted_by'     => Auth::id(),
            'date_from'      => $request->date_from,
            'date_to'        => $request->date_to,
            'reason'         => $request->reason,
            'expires_at'     => $request->expires_at,
            'status'         => 'active',
        ]);

        AuditLog::record(
            'grant_created',
            'AdmissionEditGrant',
            $grant->id,
            null,
            [
                'institution_id' => $grant->institution_id,
                'date_from'      => $grant->date_from->toDateString(),
                'date_to'        => $grant->date_to->toDateString(),
                'expires_at'     => $grant->expires_at->toDateTimeString(),
            ],
            $request->reason,
            $grant->institution_id
        );

        return redirect()->route('fde.admission-grants.index')
            ->with('success', "Edit permission granted for {$grant->institution->name}.");
    }

    // ── Revoke a grant ─────────────────────────────────────────────────────

    public function revoke(Request $request, AdmissionEditGrant $grant)
    {
        abort_if(! $grant->isActive(), 422, 'This grant is not currently active.');

        $request->validate([
            'revoke_reason' => 'required|string|min:10|max:500',
        ]);

        $grant->update([
            'status'        => 'revoked',
            'revoked_by'    => Auth::id(),
            'revoked_at'    => now(),
            'revoke_reason' => $request->revoke_reason,
        ]);

        AuditLog::record(
            'grant_revoked',
            'AdmissionEditGrant',
            $grant->id,
            ['status' => 'active'],
            ['status' => 'revoked'],
            $request->revoke_reason,
            $grant->institution_id
        );

        return redirect()->route('fde.admission-grants.index')
            ->with('success', 'Edit permission revoked successfully.');
    }
}
