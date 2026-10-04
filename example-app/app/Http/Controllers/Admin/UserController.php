<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * All user accounts. New administrator accounts are created here;
 * teacher accounts are created from the Teachers page (they need a teacher profile).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'role', 'status']);

        $users = User::query()
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['role'] ?? null, fn (Builder $q, string $role) => $q->where('role', $role))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn (Builder $q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roleOptions' => collect(UserRole::cases())->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()])->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([...$request->accountData(), 'role' => UserRole::Admin]);

        return redirect()->route('admin.users.index')->with('success', 'Administrator account created.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        if ($user->isTeacher() && $user->teacher) {
            return redirect()->route('admin.teachers.edit', $user->teacher);
        }

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->accountData());

        return redirect()->route('admin.users.index')->with('success', 'Account updated.');
    }

    /**
     * Activate or deactivate an account. Deactivated users cannot log in.
     */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->name.' has been '.($user->is_active ? 'activated' : 'deactivated').'.');
    }
}
