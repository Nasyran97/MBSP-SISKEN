<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BengkelPanelController extends Controller
{
    public function list()
    {
        $panels = DB::connection('oracle')
            ->table('BENGKEL_PANEL')
            ->where(function ($q) {
                $q->whereNull('STATUS_DATA')->orWhere('STATUS_DATA', '!=', 'DELETED');
            })
            ->orderBy('NAMA_PANEL')
            ->get();

        return view('pages.bengkel-panel.bengkel-panel-list', [
            'title' => 'Senarai Bengkel Panel',
            'panels' => $panels,
        ]);
    }

    public function add()
    {
        return view('pages.bengkel-panel.bengkel-panel-add', [
            'title' => 'Tambah Bengkel Panel',
        ]);
    }

    public function nextKod(Request $request)
    {
        return response()->json(['kod' => $this->generateKod((string) $request->query('nama'))]);
    }

    /**
     * Kod = first letter of nama + (highest existing number for that letter + 1), e.g. K3 -> K4.
     */
    private function generateKod(string $nama): ?string
    {
        $letter = strtoupper(mb_substr(trim($nama), 0, 1));

        if ($letter === '' || !preg_match('/^[A-Z0-9]$/', $letter)) {
            return null;
        }

        $max = DB::connection('oracle')
            ->table('BENGKEL_PANEL')
            ->whereRaw('UPPER(KOD_PANEL) LIKE ?', [$letter . '%'])
            ->pluck('kod_panel')
            ->map(fn ($kod) => preg_match('/^' . $letter . '(\d{1,4})$/i', trim($kod), $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        return $letter . ($max + 1);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_panel' => 'required|string|max:255',
            'nama_ringkas' => 'nullable|string|max:100',
            'kategori' => 'nullable|in:BUMIPUTERA,BUKAN BUMIPUTERA',
            'servis' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'poskod' => 'nullable|string|max:10',
            'bandar' => 'nullable|string|max:100',
            'daerah' => 'nullable|string|max:100',
            'negeri' => 'nullable|string|max:100',
            'no_tel' => 'nullable|string|max:50',
            'no_fax' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ], [
            'nama_panel.required' => 'Nama bengkel panel wajib diisi.',
            'email.email' => 'Format emel tidak sah.',
        ]);

        $nama = trim($request->nama_panel);

        $exists = DB::connection('oracle')
            ->table('BENGKEL_PANEL')
            ->whereRaw('UPPER(TRIM(NAMA_PANEL)) = ?', [strtoupper($nama)])
            ->where(function ($q) {
                $q->whereNull('STATUS_DATA')->orWhere('STATUS_DATA', '!=', 'DELETED');
            })
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'Bengkel panel ini telah wujud.');
        }

        DB::connection('oracle')->table('BENGKEL_PANEL')->insert([
            'NAMA_PANEL'   => $nama,
            'KOD_PANEL'    => $this->generateKod($nama),
            'NAMA_RINGKAS' => $request->nama_ringkas,
            'KATEGORI'     => $request->kategori,
            'SERVIS'       => $request->servis,
            'ALAMAT'       => $request->alamat,
            'POSKOD'       => $request->poskod,
            'BANDAR'       => $request->bandar,
            'DAERAH'       => $request->daerah,
            'NEGERI'       => $request->negeri,
            'NO_TEL'       => $request->no_tel,
            'NO_FAX'       => $request->no_fax,
            'EMAIL'        => $request->email,
            'CREATED_BY'   => session('nostaff'),
            'CREATED_AT'   => now('Asia/Kuala_Lumpur'),
            'STATUS_DATA'  => 'ACTIVE',
        ]);

        return redirect()->route('bengkel-panel-list')
            ->with('success', 'Bengkel panel berjaya ditambah.');
    }

    public function destroy($id)
    {
        DB::connection('oracle')
            ->table('BENGKEL_PANEL')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETED',
                'EDITED_AT'   => now('Asia/Kuala_Lumpur'),
                'EDITED_BY'   => session('nostaff'),
            ]);

        return redirect()->route('bengkel-panel-list')
            ->with('success', 'Bengkel panel berjaya dibuang.');
    }
}
