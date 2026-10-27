<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Enums\Permission;
use App\Models\User;
use App\Models\UserType;
use Framework\Auth\Auth;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * User types and the permissions each one grants.
 */
final readonly class UserTypeController
{
    public function __construct(private Auth $auth)
    {
    }

    public function index(Request $request): Response
    {
        $search = $request->string('q');
        $types = UserType::query()->search(['name', 'description'], $search)->orderBy('name')->paginate(20, $request->integer('page', 1));

        return view('admin/usertypes/index', ['types' => $types, 'search' => $search]);
    }

    public function create(): Response
    {
        return view('admin/usertypes/form', ['type' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        UserType::create([...$request->validate($this->rules()), ...$this->permissions($request)]);

        return to_route('admin.usertypes.index')->with('success', __('usertypes.created'));
    }

    public function edit(int $id): Response
    {
        return view('admin/usertypes/form', ['type' => UserType::findOrFail($id)]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $type = UserType::findOrFail($id);
        $data = $request->validate($this->rules($type));

        // The permissions of a locked type are fixed (it always has all of them).
        if (!$type->is_locked) {
            $permissions = $this->permissions($request);

            if ($type->id === $this->currentUser()->usertype_id && !$permissions[Permission::ManageUsertypes->column()]) {
                return back()->with('error', __('usertypes.cannot_lock_out_self'));
            }

            $data = [...$data, ...$permissions];
        }

        $type->update($data);

        return to_route('admin.usertypes.index')->with('success', __('usertypes.updated'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $type = UserType::findOrFail($id);

        if ($type->is_locked) {
            return back()->with('error', __('usertypes.locked'));
        }

        if ($type->usersCount() > 0) {
            return back()->with('error', __('usertypes.has_users'));
        }

        $type->delete();

        return back()->with('success', __('usertypes.deleted'));
    }

    /**
     * Checked boxes arrive as permissions[]=view_entries&permissions[]=...; unchecked ones are absent.
     *
     * @return array<string, bool> column => granted
     */
    private function permissions(Request $request): array
    {
        $granted = (array) $request->input('permissions', []);
        $columns = [];

        foreach (Permission::cases() as $permission) {
            $columns[$permission->column()] = in_array($permission->value, $granted, true);
        }

        return $columns;
    }

    private function currentUser(): User
    {
        return $this->auth->user();
    }

    private function rules(?UserType $type = null): array
    {
        return [
            'name' => 'required|string|min:2|max:55|unique:usertypes,name' . ($type ? ",{$type->id}" : ''),
            'description' => 'nullable|string|max:255',
        ];
    }
}
