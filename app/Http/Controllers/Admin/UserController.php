<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\AuditLog;
use Spatie\Permission\Models\Role;

/**
 * AEO sector assignment uses the aeo_sectors pivot table (belongsToMany).
 * There is NO sector_id column on the users table.
 * Use $user->sectors()->sync([id]) to assign, whereHas('sectors',...) to query.
 */
class UserController extends Controller
{
    // ── List all users ─────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::with(['institution', 'sectors', 'roles'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
            );
        }

        if ($request->filled('school')) {
            $school = $request->school;
            $query->whereHas('institution', fn($q) =>
                $q->where('name', 'like', "%{$school}%")
                  ->orWhere('code', 'like', "%{$school}%")
            );
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => User::count(),
            'hoi'      => User::role('hoi')->count(),
            'aeo'      => User::role('aeo')->count(),
            'fde_cell' => User::role('fde_cell')->count(),
            'director' => User::role('director')->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        $institutions = Institution::where('is_active', true)->orderBy('code')->get();
        $sectors      = Sector::withCount(['aeos as has_aeo' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')->get();

        return view('admin.users.create', compact('institutions', 'sectors'));
    }

    // ── Store new user ─────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'role'           => 'required|in:hoi,aeo,fde_cell,director',
            'phone'          => 'nullable|string|max:20',
            'institution_id' => 'required_if:role,hoi|nullable|exists:institutions,id',
            'sector_id'      => 'required_if:role,aeo|nullable|exists:sectors,id',
        ], [
            'institution_id.required_if' => 'Please select an institution for this HOI.',
            'sector_id.required_if'      => 'Please select a sector for this AEO.',
        ]);

        // ── One AEO per sector enforcement ───────────────────
        if ($request->role === 'aeo' && $request->sector_id) {
            $existing = User::role('aeo')
                ->whereHas('sectors', fn($q) => $q->where('sectors.id', $request->sector_id))
                ->where('is_active', true)
                ->first();

            if ($existing) {
                return back()->withInput()->withErrors([
                    'sector_id' => "This sector already has an active AEO: {$existing->name}. Deactivate them first.",
                ]);
            }
        }

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'phone'          => $request->phone,
            'institution_id' => $request->role === 'hoi' ? $request->institution_id : null,
            'is_active'      => true,
        ]);

        $user->assignRole($request->role);

        // Assign sector via pivot (AEO only)
        if ($request->role === 'aeo' && $request->sector_id) {
            $user->sectors()->sync([$request->sector_id]);
        }

        AuditLog::record(
            action: 'created',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $request->role]
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    // ── Show edit form ─────────────────────────────────────
    public function edit(User $user)
    {
        $user->load('sectors');

        $institutions = Institution::where('is_active', true)->orderBy('code')->get();
        $sectors      = Sector::withCount(['aeos as has_aeo' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')->get();
        $userRole     = $user->getRoleNames()->first();

        return view('admin.users.edit', compact('user', 'institutions', 'sectors', 'userRole'));
    }

    // ── Update user ────────────────────────────────────────
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'password'       => 'nullable|string|min:8|confirmed',
            'role'           => 'required|in:hoi,aeo,fde_cell,director',
            'phone'          => 'nullable|string|max:20',
            'institution_id' => 'required_if:role,hoi|nullable|exists:institutions,id',
            'sector_id'      => 'required_if:role,aeo|nullable|exists:sectors,id',
        ], [
            'institution_id.required_if' => 'Please select an institution for this HOI.',
            'sector_id.required_if'      => 'Please select a sector for this AEO.',
        ]);

        // ── One AEO per sector (excluding self) ───────────────
        if ($request->role === 'aeo' && $request->sector_id) {
            $existing = User::role('aeo')
                ->whereHas('sectors', fn($q) => $q->where('sectors.id', $request->sector_id))
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existing) {
                return back()->withInput()->withErrors([
                    'sector_id' => "This sector already has an active AEO: {$existing->name}.",
                ]);
            }
        }

        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'institution_id' => $request->role === 'hoi' ? $request->institution_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        // Sync sector pivot (AEO only — clear for other roles)
        if ($request->role === 'aeo' && $request->sector_id) {
            $user->sectors()->sync([$request->sector_id]);
        } else {
            $user->sectors()->detach();
        }

        AuditLog::record(
            action: 'updated',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $request->role]
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    // ── Toggle active status ───────────────────────────────
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        AuditLog::record(
            action: 'updated',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['is_active' => $user->is_active]
        );

        return back()->with('success', "{$user->name} has been {$status}.");
    }

    // ── Delete user ────────────────────────────────────────
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;

        AuditLog::record(
            action: 'deleted',
            modelType: 'User',
            modelId: $user->id,
            oldValues: ['name' => $name, 'email' => $user->email]
        );

        $user->sectors()->detach();
        $user->roles()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$name}\" has been deleted.");
    }
}
