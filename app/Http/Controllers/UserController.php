<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use App\Services\ActivityLoggerService;

/**
 * Controller for managing users and roles.
 * Following PSR-12 standards.
 */
class UserController extends Controller
{
    protected ActivityLoggerService $activityLogger;

    public function __construct(ActivityLoggerService $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $roles = Role::whereIn('name', ['Super Admin', 'Kasir', 'Marketing'])->get();
            $users = User::role($roles)->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->editColumn('role', function ($item) {
                    return $item->getRoleNames()->first() ?? '-';
                })
                ->addColumn('actions', function ($item) {
                    return
                        '<div class="dropdown text-end">
                        <button type="button" class="btn btn-secondary btn-sm btn-active-light-primary rotate" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start" data-bs-toggle="dropdown">
                            Actions
                            <span class="svg-icon svg-icon-3 rotate-180 ms-3 me-0">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"></path>
                                </svg>
                            </span>
                        </button>
                        <div class="dropdown-menu menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-100px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="' . route('users.edit', $item->id) . '" class="menu-link px-3">
                                    Edit Profile
                                </a>
                            </div>
                            <div class="menu-item px-3">
                                <a class="menu-link px-3 delete-confirm" data-id="' . $item->id . '" role="button">Hapus</a>
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.user.create', [
            'user' => new User(),
            'roles' => Role::whereIn('name', ['Super Admin', 'Kasir', 'Marketing'])->get(),
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $user->assignRole($request->role);

                $this->activityLogger->log(
                    'User Created',
                    'User ' . auth()->user()->name . ' created a new user: ' . $user->name . ' (ID: ' . $user->id . ') with role ' . $request->role,
                    auth()->id()
                );
            });

            return redirect()->route('users.index')->with('success', 'User created successfully');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to create user: ' . $th->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing a user.
     *
     * @param string $id
     * @return View
     */
    public function edit(string $id): View
    {
        $user = User::findOrFail($id);
        $roles = Role::whereIn('name', ['Super Admin', 'Kasir', 'Marketing'])->get();

        return view('admin.user.edit', compact('user', 'roles'));
    }

    /**
     * Update a user.
     *
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string|exists:roles,name',
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

                if ($request->filled('password')) {
                    $user->update(['password' => Hash::make($request->password)]);
                }

                $user->syncRoles([$request->role]);

                $this->activityLogger->log(
                    'User Updated',
                    'User ' . auth()->user()->name . ' updated user: ' . $user->name . ' (ID: ' . $user->id . ') to role ' . $request->role,
                    auth()->id()
                );
            });

            return redirect()->route('users.index')->with('success', 'User updated successfully');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to update user: ' . $th->getMessage())->withInput();
        }
    }

    /**
     * Remove a user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $userName = $user->name; // Get name before deletion
            $userId = $user->id; // Get ID before deletion
            $user->delete();

            $this->activityLogger->log(
                'User Deleted',
                'User ' . auth()->user()->name . ' deleted user: ' . $userName . ' (ID: ' . $userId . ')',
                auth()->id()
            );
            return response()->json([
                'status' => 'success',
                'message' => 'User Deleted!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
