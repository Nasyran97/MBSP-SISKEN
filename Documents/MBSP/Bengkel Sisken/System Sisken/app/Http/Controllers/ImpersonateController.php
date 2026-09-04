<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImpersonateController extends Controller
{
    public function start(Request $request)
    {
        $this->ensureRealUserIsSuperAdmin();

        $targetNostaff = trim((string) $request->input('nostaff'));

        if ($targetNostaff === '') {
            return back()->with('impersonate_error', 'Sila masukkan No Staf.');
        }

        $target = DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $targetNostaff)
            ->first();

        if (!$target) {
            return back()->with('impersonate_error', 'Akaun No Staf tersebut tidak dijumpai.');
        }

        if (!$request->session()->has('impersonate_original')) {
            $request->session()->put('impersonate_original', [
                'user_id' => session('user_id'),
                'nostaff' => session('nostaff'),
                'nama'    => session('nama'),
                'email'   => session('email'),
            ]);
        }

        session([
            'user_id' => $target->id,
            'nostaff' => $target->nostaff,
            'nama'    => $target->nama,
            'email'   => $target->email,
        ]);

        return back()->with(
            'impersonate_success',
            'Kini menyemak sistem sebagai No Staf ' . $target->nostaff . ' (' . $target->nama . ').'
        );
    }

    public function stop(Request $request)
    {
        $original = $request->session()->get('impersonate_original');

        if (!$original) {
            return back();
        }

        session([
            'user_id' => $original['user_id'],
            'nostaff' => $original['nostaff'],
            'nama'    => $original['nama'],
            'email'   => $original['email'],
        ]);

        $request->session()->forget('impersonate_original');

        return back()->with('impersonate_success', 'Kembali ke akaun asal.');
    }

    private function ensureRealUserIsSuperAdmin()
    {
        $original = session('impersonate_original');
        $realNostaff = $original['nostaff'] ?? session('nostaff');

        if (!$realNostaff) {
            abort(403, 'Sila log masuk semula.');
        }

        $realUser = DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $realNostaff)
            ->first();

        if (!$realUser) {
            abort(403, 'Akaun tidak dijumpai.');
        }

        $moduleRole = json_decode($realUser->module_role ?? $realUser->MODULE_ROLE ?? '[]', true);
        $isSuperAdmin = false;

        if (is_array($moduleRole)) {
            foreach ($moduleRole as $item) {
                $module = strtoupper(trim($item['module'] ?? ''));
                $role = (int) ($item['role'] ?? 0);

                if ($module === 'SUPERADMIN' && $role === 1) {
                    $isSuperAdmin = true;
                    break;
                }
            }
        }

        if (!$isSuperAdmin) {
            abort(403, 'Hanya SUPERADMIN dibenarkan menukar akaun ujian.');
        }
    }
}
