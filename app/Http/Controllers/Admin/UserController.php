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

class UserController extends Controller
{
    // ── List all users ─────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::with(['institution', 'roles'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        $roles        = Role::orderBy('name')->get();
        $institutions = Institution::where('is_active', true)
                          ->orderBy('name')->get();
        $sectors      = Sector::where('is_active', true)
                          ->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'institutions', 'sectors'));
    }

    // ── Store new user ─────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'role'           => 'required|exists:roles,name',
            'phone'          => 'nullable|string|max:20',
            'institution_id' => 'nullable|exists:institutions,id',
            'sector_ids'     => 'nullable|array',
            'sector_ids.*'   => 'exists:sectors,id',
        ]);

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'phone'          => $request->phone,
            'institution_id' => $request->role === 'hoi'
                                    ? $request->institution_id
                                    : null,
            'is_active'      => true,
        ]);

        // Assign role
        $user->assignRole($request->role);

        // Assign sectors for AEO
        if ($request->role === 'aeo' && $request->filled('sector_ids')) {
            foreach ($request->sector_ids as $sectorId) {
                \DB::table('aeo_sectors')->insert([
                    'user_id'    => $user->id,
                    'sector_id'  => $sectorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        AuditLog::record(
            action: 'created',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $request->role]
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    // ── Show edit form ─────────────────────────────────────
    public function edit(User $user)
    {
        $roles        = Role::orderBy('name')->get();
        $institutions = Institution::where('is_active', true)
                          ->orderBy('name')->get();
        $sectors      = Sector::where('is_active', true)
                          ->orderBy('name')->get();

        $userSectorIds = $user->sectors->pluck('id')->toArray();
        $userRole      = $user->getRoleNames()->first();

        return view('admin.users.edit', compact(
            'user', 'roles', 'institutions', 'sectors', 'userSectorIds', 'userRole'
        ));
    }

    // ── Update user ────────────────────────────────────────
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'password'       => 'nullable|string|min:8|confirmed',
            'role'           => 'required|exists:roles,name',
            'phone'          => 'nullable|string|max:20',
            'institution_id' => 'nullable|exists:institutions,id',
            'sector_ids'     => 'nullable|array',
            'sector_ids.*'   => 'exists:sectors,id',
        ]);

        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'institution_id' => $request->role === 'hoi'
                                    ? $request->institution_id
                                    : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update role
        $user->syncRoles([$request->role]);

        // Update AEO sectors
        \DB::table('aeo_sectors')->where('user_id', $user->id)->delete();

        if ($request->role === 'aeo' && $request->filled('sector_ids')) {
            foreach ($request->sector_ids as $sectorId) {
                \DB::table('aeo_sectors')->insert([
                    'user_id'    => $user->id,
                    'sector_id'  => $sectorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        AuditLog::record(
            action: 'updated',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $request->role]
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    // ── Toggle active status ───────────────────────────────
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        AuditLog::record(
            action: 'updated',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['is_active' => $user->is_active]
        );

        return back()->with('success', 'User status updated.');
    }
}
