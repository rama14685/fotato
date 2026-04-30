<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotographerRequest;
use App\Http\Requests\UpdatePhotographerRequest;
use App\Models\User;
use App\Models\AdminAuditLog;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhotographerController extends Controller
{
    /**
     * Display a listing of photographers.
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'photographer');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $photographers = $query->paginate(25);

        return view('admin.photographers.index', [
            'photographers' => $photographers,
            'currentStatus' => $request->status ?? 'all',
            'searchQuery' => $request->search ?? '',
        ]);
    }

    /**
     * Show the form for creating a new photographer.
     */
    public function create(): View
    {
        return view('admin.photographers.create');
    }

    /**
     * Store a newly created photographer in storage.
     */
    public function store(StorePhotographerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Check for duplicate email
        if (User::where('email', $validated['email'])->exists()) {
            return back()->with('error', 'Email sudah terdaftar.');
        }

        $photographer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'photographer',
            'wallet_balance' => 0,
            'status' => 'active',
        ]);

        // Log the action
        AdminAuditLog::logAction(
            auth()->id(),
            'photographer_created',
            'photographer',
            $photographer->id,
            "Photographer '{$photographer->name}' ({$photographer->email}) created"
        );

        return redirect()->route('admin.photographers.show', $photographer)
                       ->with('success', 'Photographer berhasil dibuat.');
    }

    /**
     * Display the specified photographer.
     */
    public function show(User $photographer): View
    {
        $albums = $photographer->albums()->paginate(10);
        $earnings = $photographer->transactions()
            ->where('status', 'completed')
            ->sum('total_amount');

        return view('admin.photographers.show', [
            'photographer' => $photographer,
            'albums' => $albums,
            'earnings' => $earnings,
        ]);
    }

    /**
     * Show the form for editing the specified photographer.
     */
    public function edit(User $photographer): View
    {
        return view('admin.photographers.edit', ['photographer' => $photographer]);
    }

    /**
     * Update the specified photographer in storage.
     */
    public function update(UpdatePhotographerRequest $request, User $photographer): RedirectResponse
    {
        $validated = $request->validated();

        // Check for duplicate email
        if ($validated['email'] !== $photographer->email && 
            User::where('email', $validated['email'])->exists()) {
            return back()->with('error', 'Email sudah terdaftar.');
        }

        $changes = [];
        if ($validated['name'] !== $photographer->name) {
            $changes['name'] = ['from' => $photographer->name, 'to' => $validated['name']];
        }
        if ($validated['email'] !== $photographer->email) {
            $changes['email'] = ['from' => $photographer->email, 'to' => $validated['email']];
        }

        $photographer->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($changes)) {
            AdminAuditLog::logAction(
                auth()->id(),
                'photographer_updated',
                'photographer',
                $photographer->id,
                "Photographer information updated",
                $changes
            );
        }

        return redirect()->route('admin.photographers.show', $photographer)
                       ->with('success', 'Data photographer berhasil diperbarui.');
    }

    /**
     * Toggle photographer status (active/inactive).
     */
    public function toggleStatus(User $photographer): RedirectResponse
    {
        $oldStatus = $photographer->status;
        $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';
        
        $photographer->update(['status' => $newStatus]);

        AdminAuditLog::logAction(
            auth()->id(),
            'photographer_status_changed',
            'photographer',
            $photographer->id,
            "Photographer status changed to '{$newStatus}'",
            ['status' => ['from' => $oldStatus, 'to' => $newStatus]]
        );

        return back()->with('success', "Photographer status berhasil diubah menjadi {$newStatus}.");
    }
}
