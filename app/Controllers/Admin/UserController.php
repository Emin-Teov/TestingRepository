<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Enums\Permission;
use App\Models\User;
use App\Models\UserType;
use Framework\Auth\Auth;
use Framework\Auth\Hash;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;

final readonly class UserController
{
    public function __construct(private Auth $auth)
    {
    }

    public function index(Request $request): Response
    {
        $search = $request->string('q');
        $users = User::query()->search(['username', 'full_name'], $search)->orderBy('username')->paginate(20, $request->integer('page', 1));

        return view('admin/users/index', [
            'users' => $users,
            'types' => UserType::findMany(array_map(static fn (User $user) => $user->usertype_id, $users->items)),
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        User::create([...$data, 'password' => Hash::make($data['password']), 'is_active' => $request->has('is_active')]);

        return to_route('admin.users.index')->with('success', __('users.created'));
    }

    public function edit(int $id): Response
    {
        return $this->form(User::findOrFail($id));
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $user = User::findOrFail($id);
        $data = [...$request->validate($this->rules($user)), 'is_active' => $request->has('is_active')];

        if ($user->id === $this->auth->id()) {
            if (!$data['is_active']) {
                return back()->with('error', __('users.cannot_deactivate_self'));
            }

            if (!UserType::findOrFail((int) $data['usertype_id'])->allows(Permission::ManageUsers)) {
                return back()->with('error', __('users.cannot_demote_self'));
            }
        }

        // An empty password field keeps the current password.
        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return to_route('admin.users.index')->with('success', __('users.updated'));
    }

    /**
     * Stock entries of a deleted user stay, without an author (ON DELETE SET NULL).
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === $this->auth->id()) {
            return back()->with('error', __('users.cannot_delete_self'));
        }

        $user->delete();

        return back()->with('success', __('users.deleted'));
    }

    private function form(?User $user): Response
    {
        return view('admin/users/form', ['user' => $user, 'types' => UserType::query()->orderBy('name')->get()]);
    }

    private function rules(?User $user = null): array
    {
        return [
            'username' => 'required|alpha_dash|min:2|max:55|unique:users,username' . ($user ? ",{$user->id}" : ''),
            'full_name' => 'nullable|string|max:100',
            'usertype_id' => 'required|integer|exists:usertypes,id',
            'password' => ($user ? 'nullable' : 'required') . '|string|min:8|max:255|confirmed',
        ];
    }
}
