<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nostaff'  => 'required|string',
            'password' => 'required|string',
        ]);

        $baseUrl   = 'https://apimbsp.mbsp.gov.my/public/APIS_63804ca61b9cc/user/log';
        $apiKey    = '63804ca61ba0d';
        $apiToken  = 'M1S6JTW0MLFRYFQKLWRDA095';

        $nostaffInput   = trim($request->nostaff);
        $hashedPassword = md5($request->password);

        // 1. Authenticate with external API
        $response = Http::timeout(10)->connectTimeout(5)->withBasicAuth('enforce', 'enforce@mbsp123')
            ->asForm()
            ->post($baseUrl, [
                $apiKey    => $apiToken,
                'nostaff'  => $nostaffInput,
                'password' => $hashedPassword,
            ]);

        if ($response->failed()) {
            return back()->withErrors([
                'login' => 'Gagal berhubung dengan sistem login.',
            ])->withInput();
        }

        $responseData = $response->json();

        if (!isset($responseData['status']) || $responseData['status'] !== 'success' || empty($responseData['data'])) {
            return back()->withErrors([
                'login' => 'Maaf. No pekerja atau kata laluan salah.',
            ])->withInput();
        }

        // 2. Get staff profile
        $staffUrl    = 'https://apimbsp.mbsp.gov.my/public/APIS_633bb3e5a094b/join_staff/staff';
        $staffApiKey = '633bb3e5a0993';
        $staffToken  = 'LBTXN72WW3JCS13W1WIUE485';

        $staffResponse = Http::timeout(10)->connectTimeout(5)->withBasicAuth('enforce', 'enforce@mbsp123')
            ->asForm()
            ->post($staffUrl, [
                $staffApiKey => $staffToken,
                'nostaff'    => $nostaffInput,
            ]);

        if ($staffResponse->failed()) {
            return back()->withErrors([
                'login' => 'Gagal mendapatkan maklumat pekerja.',
            ])->withInput();
        }

        $staffData = $staffResponse->json('data.0');

        if (!$staffData || empty($staffData['NOSTAFF'])) {
            return back()->withErrors([
                'login' => 'Maklumat pekerja tidak dijumpai.',
            ])->withInput();
        }

        $nostaff = trim($staffData['NOSTAFF']);

        // 3. Check local users table
        $user = DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $nostaff)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'login' => 'Akaun belum didaftarkan. Sila hubungi admin sistem.',
            ])->withInput();
        }

        if (strtoupper($user->status_data ?? '') !== 'ACTIVE') {
            return back()->withErrors([
                'login' => 'Akaun anda tidak aktif. Sila hubungi admin sistem.',
            ])->withInput();
        }

        // 4. Generate token
        $plainToken = Str::random(60);

        // 5. Update local user info
        DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $nostaff)
            ->update([
                'nama'             => $staffData['NAMASTAFF'] ?? null,
                'jawatan'          => $staffData['JAWATANSTAFF'] ?? null,
                'kod_jawatan'      => $staffData['KOD_JAWATAN'] ?? null,
                'jabatan'          => $staffData['DESCJABATANSTAFF'] ?? null,
                'kod_jabatan'      => $staffData['KODJABATANSTAFF'] ?? null,
                'email'            => $staffData['EMEL'] ?? null,
                'token'            => hash('sha256', $plainToken),
                'token_expired_at' => now()->addHours(8),
                'updated_at'       => now(),
            ]);

        $request->session()->regenerate();

        session([
            'user_id'       => $user->id,
            'nostaff'       => $user->nostaff,
            'nama'          => $user->nama,
            'email'         => $user->email,
            'login_token'   => $plainToken,
        ]);

        $request->session()->save();

        $modules = [];
        $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);
        if (is_array($moduleRole)) {
            foreach ($moduleRole as $item) {
                $modules[] = strtoupper(trim($item['module'] ?? ''));
            }
        }

        if (in_array('SUPERADMIN', $modules)) {
            $defaultRoute = 'user-list';
        } elseif (in_array('INSURANS', $modules)) {
            $defaultRoute = 'cukai-jalan-info-list';
        } elseif (in_array('PENYELENGGARAAN', $modules)) {
            $defaultRoute = 'penyelenggaraan-list';
        } else {
            $defaultRoute = 'cukai-jalan-info-list';
        }

        $redirectUrl = $request->input('redirectUrl') ?: route($defaultRoute);

        return redirect($redirectUrl)->with('success', 'Login berjaya.');

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Login berjaya.',
        //     'user' => $userData,
        //     'modules' => $modules,
        //     'token' => $plainToken,
        // ], 200);
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'nostaff'  => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     $baseUrl   = 'https://apimbsp.mbsp.gov.my/public/APIS_63804ca61b9cc/user/log';
    //     $apiKey    = '63804ca61ba0d';
    //     $apiToken  = 'M1S6JTW0MLFRYFQKLWRDA095';

    //     $nostaffInput   = trim($request->nostaff);
    //     $hashedPassword = md5($request->password);

    //     // 1. Authenticate with external API
    //     $response = Http::withBasicAuth('enforce', 'enforce@mbsp123')
    //         ->asForm()
    //         ->post($baseUrl, [
    //             $apiKey    => $apiToken,
    //             'nostaff'  => $nostaffInput,
    //             'password' => $hashedPassword,
    //         ]);

    //     if ($response->failed()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal berhubung dengan sistem login.',
    //         ], 500);
    //     }

    //     $responseData = $response->json();

    //     if (!isset($responseData['status']) || $responseData['status'] !== 'success' || empty($responseData['data'])) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Maaf. No staf atau kata laluan salah.',
    //         ], 401);
    //     }

    //     // 2. Get staff profile
    //     $staffUrl    = 'https://apimbsp.mbsp.gov.my/public/APIS_633bb3e5a094b/join_staff/staff';
    //     $staffApiKey = '633bb3e5a0993';
    //     $staffToken  = 'LBTXN72WW3JCS13W1WIUE485';

    //     $staffResponse = Http::withBasicAuth('enforce', 'enforce@mbsp123')
    //         ->asForm()
    //         ->post($staffUrl, [
    //             $staffApiKey => $staffToken,
    //             'nostaff'    => $nostaffInput,
    //         ]);

    //     if ($staffResponse->failed()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal mendapatkan maklumat staf.',
    //         ], 500);
    //     }

    //     $staffData = $staffResponse->json('data.0');

    //     if (!$staffData || empty($staffData['NOSTAFF'])) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Maklumat staf tidak dijumpai.',
    //         ], 404);
    //     }

    //     $nostaff = trim($staffData['NOSTAFF']);

    //     // 3. Check local users table
    //     $user = DB::connection('oracle')
    //         ->table('users')
    //         ->where('nostaff', $nostaff)
    //         ->first();

    //     if (!$user) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Akaun belum didaftarkan. Sila hubungi admin sistem.',
    //         ], 403);
    //     }

    //     if (strtoupper($user->status_data ?? '') !== 'ACTIVE') {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Akaun anda tidak aktif. Sila hubungi admin sistem.',
    //         ], 403);
    //     }

    //     // 4. Generate token
    //     $plainToken = Str::random(60);

    //     // 5. Update local user info
    //     DB::connection('oracle')
    //         ->table('users')
    //         ->where('nostaff', $nostaff)
    //         ->update([
    //             'nama'             => $staffData['NAMASTAFF'] ?? null,
    //             'jawatan'          => $staffData['JAWATANSTAFF'] ?? null,
    //             'kod_jawatan'      => $staffData['KOD_JAWATAN'] ?? null,
    //             'jabatan'          => $staffData['DESCJABATANSTAFF'] ?? null,
    //             'kod_jabatan'      => $staffData['KODJABATANSTAFF'] ?? null,
    //             'email'            => $staffData['EMEL'] ?? null,
    //             'token'            => hash('sha256', $plainToken),
    //             'token_expired_at' => now()->addHours(8),
    //             'updated_at'       => now(),
    //         ]);

    //     // 6. Reload updated user
    //     $updatedUser = DB::connection('oracle')
    //         ->table('users')
    //         ->select(
    //             'id',
    //             'nostaff',
    //             'nama',
    //             'jawatan',
    //             'kod_jawatan',
    //             'jabatan',
    //             'kod_jabatan',
    //             'email',
    //             'role_id',
    //             'status_data',
    //             'token_expired_at'
    //         )
    //         ->where('nostaff', $nostaff)
    //         ->first();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Login berjaya.',
    //         'token' => $plainToken,
    //         'user' => $updatedUser,
    //     ], 200);
    // }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'user_id',
            'nostaff',
            'nama',
            'email',
            'login_token',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin')
            ->with('error', 'Sila masukkan No Staf dan kata laluan untuk log masuk semula.');
    }
}