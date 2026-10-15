<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::connection('oracle')
            ->table('users')
            ->get();

        $modules = DB::connection('oracle')
            ->table('modules')
            ->where('status_data', 'ACTIVE')
            ->get()
            ->keyBy(function ($item) {
                return strtoupper(trim($item->name));
            });

        $roles = DB::connection('oracle')
            ->table('roles')
            ->where('status_data', 'ACTIVE')
            ->get();

        foreach ($users as $user) {
            $moduleRoleRaw = $user->MODULE_ROLE ?? $user->module_role ?? '[]';
            $decoded = json_decode($moduleRoleRaw, true);

            $user->module_role_display = [];

            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $moduleName = strtoupper(trim($item['module'] ?? ''));
                    $roleId = $item['role'] ?? null;

                    if (!$moduleName || !$roleId) {
                        continue;
                    }

                    $moduleRecord = $modules->get($moduleName);

                    if (!$moduleRecord) {
                        $user->module_role_display[] = [
                            'module' => $moduleName,
                            'role' => 'Role tidak dijumpai'
                        ];
                        continue;
                    }

                    $roleRecord = $roles->first(function ($role) use ($moduleRecord, $roleId) {
                        return (string) $role->id === (string) $roleId
                            && (string) $role->modules_id === (string) $moduleRecord->id;
                    });

                    $user->module_role_display[] = [
                        'module' => $moduleRecord->name ?? $moduleName,
                        'role' => $roleRecord->name ?? 'Role tidak dijumpai'
                    ];
                }
            }
        }

        return view('pages.user.user-list', [
            'title' => 'Senarai Pengguna',
            'users' => $users,
        ]);
    }

    public function create()
    {
        $departments = [];
        $modules = [];

        try {
            $response = Http::withBasicAuth(
                    config('services.mbsp.username'),
                    config('services.mbsp.password')
                )
                ->asForm()
                ->post(config('services.mbsp.dept_url'), [
                    config('services.mbsp.dept_key') => config('services.mbsp.dept_value'),
                ]);

            $data = $response->json();

            if (isset($data['data']) && is_array($data['data'])) {
                $departments = $data['data'];
            } elseif (is_array($data)) {
                $departments = $data;
            }

        } catch (\Exception $e) {
            Log::error('Department API exception', [
                'message' => $e->getMessage(),
            ]);
        }

        $modules = DB::connection('oracle')
            ->table('modules')
            ->where('status_data', 'ACTIVE')
            ->orderBy('name')
            ->get();

        return view('pages.user.user-add', [
            'title' => 'Tambah Pengguna',
            'departments' => $departments,
            'modules' => $modules,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department' => 'required',
            'jabatan' => 'required',
            'nostaff' => 'required',
            'module_role' => 'required|array|min:1',
            'module_role.0.module' => 'required',
            'module_role.0.role' => 'required',
        ], [
            'department.required' => 'Sila pilih jabatan.',
            'jabatan.required' => 'Jabatan tidak sah.',
            'nostaff.required' => 'Sila pilih nama staf.',
            'module_role.required' => 'Sila pilih sekurang-kurangnya satu modul dan role.',
            'module_role.0.module.required' => 'Modul pertama wajib dipilih.',
            'module_role.0.role.required' => 'Role pertama wajib dipilih.',
        ]);
        
        $nama = $request->nama;
        if (empty($nama)) {
            return back()->withErrors([
                'nostaff' => 'Nama staf tidak sah.'
            ])->withInput();
        }

        $existingUser = DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $request->nostaff)
            ->first();

        if ($existingUser) {
            return back()
                ->withErrors(['nostaff' => 'Staff sudah didaftarkan.'])
                ->withInput();
        }

        $moduleRoles = [];

        foreach ($request->module_role as $index => $item) {
            $module = trim($item['module'] ?? '');
            $role = trim($item['role'] ?? '');

            if (($module && !$role) || (!$module && $role)) {
                return back()
                    ->withErrors(["module_role.$index" => 'Setiap modul yang dipilih mesti mempunyai role.'])
                    ->withInput();
            }

            if ($module && $role) {
                $moduleRoles[] = [
                    'module' => strtoupper($module),
                    'role' => (int) $role,
                ];
            }
        }

        if (empty($moduleRoles)) {
            return back()
                ->withErrors(['module_role' => 'Sila pilih sekurang-kurangnya satu modul dan role.'])
                ->withInput();
        }

        $modules = array_column($moduleRoles, 'module');
        if (count($modules) !== count(array_unique($modules))) {
            return back()
                ->withErrors(['module_role' => 'Modul yang sama tidak boleh dipilih lebih daripada sekali.'])
                ->withInput();
        }

        DB::connection('oracle')->table('users')->insert([
            'nostaff' => $request->nostaff,
            'nama' => $nama,
            'kod_jabatan' => $request->department,
            'jabatan' => $request->jabatan,
            'module_role' => json_encode($moduleRoles),
            'status_data' => 'ACTIVE',
        ]);

        return redirect()->route('user-list')->with('success', 'Pengguna berjaya ditambah.');
        // return redirect()->route('user-add')->with('success', 'Pengguna berjaya ditambah.');Davebatista1_D
    }

    public function getStaffByDepartment(Request $request)
    {
        $departmentCode = $request->department_code;
        $staffs = [];

        Log::info('Get staff by department request', [
            'department_code' => $departmentCode,
        ]);

        try {
            $response = Http::withBasicAuth(
                    config('services.mbsp.username'),
                    config('services.mbsp.password')
                )
                ->asForm()
                ->post(config('services.mbsp.staff_url'), [
                    config('services.mbsp.staff_key') => config('services.mbsp.staff_value'),
                    'kodjabatanstaff' => $departmentCode,
                ]);

            Log::info('Staff API status', [
                'status' => $response->status(),
            ]);

            Log::info('Staff API body', [
                'body' => $response->body(),
            ]);

            $data = $response->json();

            Log::info('Staff API decoded json', [
                'data' => $data,
            ]);

            if (isset($data['data']) && is_array($data['data'])) {
                $staffs = $data['data'];
            } elseif (is_array($data)) {
                $staffs = $data;
            }

            Log::info('Staff final array', [
                'staffs' => $staffs,
                'count' => count($staffs),
            ]);

            return response()->json([
                'success' => true,
                'staffs' => $staffs,
            ]);
        } catch (\Exception $e) {
            Log::error('Staff API exception', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'staffs' => [],
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = DB::connection('oracle')
            ->table('users')
            ->where('id', $id)
            ->first();

        $modules = DB::connection('oracle')
            ->table('modules')
            ->where('status_data', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $moduleRoles = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

        if (!is_array($moduleRoles)) {
            $moduleRoles = [];
        }

        foreach ($moduleRoles as &$item) {
            $item['module'] = strtoupper(trim($item['module'] ?? ''));
            $item['role'] = (string) ($item['role'] ?? '');

            $module = DB::connection('oracle')
                ->table('modules')
                ->whereRaw('UPPER(NAME) = ?', [$item['module']])
                ->where('status_data', 'ACTIVE')
                ->first();

            $item['roles_list'] = [];

            if ($module) {
                $item['roles_list'] = DB::connection('oracle')
                    ->table('roles')
                    ->where('modules_id', $module->id)
                    ->where('status_data', 'ACTIVE')
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('pages.user.user-edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'modules' => $modules,
            'moduleRoles' => $moduleRoles,
        ]);
    }

    public function getRolesByModule(Request $request)
    {
        $moduleName = strtoupper(trim($request->module_name));

        $module = DB::connection('oracle')
            ->table('modules')
            ->whereRaw('UPPER(NAME) = ?', [$moduleName])
            ->where('status_data', 'ACTIVE')
            ->first();

        if (!$module) {
            return response()->json([
                'success' => false,
                'roles' => [],
            ]);
        }

        $roles = DB::connection('oracle')
            ->table('roles')
            ->where('modules_id', $module->id)
            ->where('status_data', 'ACTIVE')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'module_role' => 'required|array|min:1',
            'module_role.0.module' => 'required',
            'module_role.0.role' => 'required',
        ], [
            'module_role.required' => 'Sila pilih sekurang-kurangnya satu modul dan role.',
            'module_role.0.module.required' => 'Modul pertama wajib dipilih.',
            'module_role.0.role.required' => 'Role pertama wajib dipilih.',
        ]);

        $moduleRoles = [];

        foreach ($request->module_role as $index => $item) {
            $module = trim($item['module'] ?? '');
            $role = trim($item['role'] ?? '');

            // if one filled but the other empty -> reject
            if (($module && !$role) || (!$module && $role)) {
                return back()->withErrors([
                    "module_role.$index" => 'Setiap modul yang dipilih mesti mempunyai role.'
                ])->withInput();
            }

            // only save complete pairs
            if ($module && $role) {
                $moduleRoles[] = [
                    'module' => strtoupper($module),
                    'role' => (int) $role,
                ];
            }
        }

        if (empty($moduleRoles)) {
            return back()->withErrors([
                'module_role' => 'Sila pilih sekurang-kurangnya satu modul dan role.'
            ])->withInput();
        }

        $modules = array_column($moduleRoles, 'module');
        if (count($modules) !== count(array_unique($modules))) {
            return back()->withErrors([
                'module_role' => 'Modul yang sama tidak boleh dipilih lebih daripada sekali.'
            ])->withInput();
        }

        DB::connection('oracle')
            ->table('users')
            ->where('id', $id)
            ->update([
                'module_role' => json_encode($moduleRoles),
            ]);

        return redirect()->route('user-list')->with('success', 'Pengguna berjaya dikemaskini.');
    }

    public function delete($id)
    {
        DB::connection('oracle')
            ->table('users')
            ->where('id', $id)
            ->update([
                'status_data' => 'DELETE',
            ]);

        return redirect()->route('user-list')->with('success', 'Pengguna berjaya dipadam.');
    }

    public function activate($id)
    {
        DB::connection('oracle')
            ->table('users')
            ->where('id', $id)
            ->update([
                'status_data' => 'ACTIVE',
            ]);

        return redirect()->route('user-list')->with('success', 'Pengguna berjaya diaktifkan semula.');
    }
}