<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('modules.announcement.manage', compact('announcements'));
    }

    public function create()
    {
        return view('modules.announcement.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'body'         => 'required|string|max:2000',
            'type'         => 'required|in:info,warning,success,danger',
            'priority'     => 'required|in:normal,high,urgent',
            'is_active'    => 'boolean',
            'is_pinned'    => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:published_at',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:hoi,aeo,fde_cell,director',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_pinned'] = $request->boolean('is_pinned', false);
        $data['created_by'] = auth()->id();

        // Null out empty target_roles (means all roles)
        if (empty($data['target_roles'])) {
            $data['target_roles'] = null;
        }

        Announcement::create($data);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement)
    {
        return view('modules.announcement.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        return view('modules.announcement.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'body'         => 'required|string|max:2000',
            'type'         => 'required|in:info,warning,success,danger',
            'priority'     => 'required|in:normal,high,urgent',
            'is_active'    => 'boolean',
            'is_pinned'    => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:published_at',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:hoi,aeo,fde_cell,director',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_pinned'] = $request->boolean('is_pinned', false);

        if (empty($data['target_roles'])) {
            $data['target_roles'] = null;
        }

        $announcement->update($data);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted.');
    }
}
