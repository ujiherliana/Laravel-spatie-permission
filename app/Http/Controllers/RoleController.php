<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-role|edit-role|delete-role', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-role', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-role', ['only' => ['destroy']]);
    }
    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::orderBy('id', 'desc')->paginate(3)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('roles.create', [
            'permissions' => Permission::get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->name]);
        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();

        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
            ->withSuccess('new role is added succssfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): View
    {
        $rolePermissions = Permission::join("role_has_permissions", "permission_id", "=", "id")
            ->where("role_id", $role->id)
            ->select('name')
            ->get();

        return view('roles.show', [
            'role' => $role,
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        if ($role->name == 'Super Admin') {
            abort(403, 'Super Admin role can not be edited');
        }

        $rolePermissions = DB::table("role_has_permissions")->where("role_id", $role->id)
            ->pluck('permission_id')
            ->all();

        return view('role.edit', [
            'role' => $role,
            'permissions' => Permission::get(),
            'rolePerrmissions' => $rolePermissions
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $input = $request->only('name');

        $role->update($input);

        $permissions = Permission::whereIn('id', $request->permissions)
            ->get(['name'])->toArray();
        $role->syncPermissions($permissions);

        return redirect()->back()
            ->withSuccess('role is updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name == 'Super Admin') {
            abort(403, 'Super Admin can not be deleted');
        }
        if (auth()->user()->hasRole($role->name)) {
            abort(403, 'Can not delete self assigned role');
        }
        $role->delete();
        return redirect()->route('roles.index')
            ->withSuccess('Role is deleted successfully');
    }
}
