<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlatGantiController extends Controller
{
    private function table()
    {
        return DB::connection('oracle')->table('ALAT_GANTI');
    }

    private function activeScope($query)
    {
        return $query->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'");
    }

    public function list()
    {
        $items = $this->activeScope($this->table())
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NAME as "NAME"'),
                DB::raw('QUANTITY as "QUANTITY"')
            )
            ->orderBy('NAME')
            ->get();

        return view('pages.alat-ganti.alat-ganti-list', [
            'title' => 'Senarai Alat Ganti',
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
        ], [
            'name.required' => 'Sila masukkan nama alat ganti.',
            'quantity.required' => 'Sila masukkan kuantiti.',
            'quantity.integer' => 'Kuantiti mesti nombor bulat.',
            'quantity.min' => 'Kuantiti tidak boleh kurang daripada 0.',
        ]);

        $name = trim($data['name']);

        $exists = $this->activeScope($this->table())
            ->whereRaw('UPPER(TRIM(NAME)) = ?', [strtoupper($name)])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Alat ganti ini telah wujud.');
        }

        $nostaff = session('nostaff') ?? 'SYSTEM';

        $this->table()->insert([
            'NAME' => $name,
            'QUANTITY' => (int) $data['quantity'],
            'CREATED_BY' => $nostaff,
            'CREATED_AT' => now('Asia/Kuala_Lumpur'),
            'EDITED_BY' => $nostaff,
            'EDITED_AT' => now('Asia/Kuala_Lumpur'),
            'STATUS_DATA' => 'ACTIVE',
        ]);

        return redirect()->route('alat-ganti-list')->with('success', 'Alat ganti berjaya ditambah.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
        ], [
            'name.required' => 'Sila masukkan nama alat ganti.',
            'quantity.required' => 'Sila masukkan kuantiti.',
            'quantity.integer' => 'Kuantiti mesti nombor bulat.',
            'quantity.min' => 'Kuantiti tidak boleh kurang daripada 0.',
        ]);

        $record = $this->activeScope($this->table())->where('ID', $id)->first();

        if (!$record) {
            return back()->with('error', 'Rekod alat ganti tidak dijumpai.');
        }

        $name = trim($data['name']);

        $exists = $this->activeScope($this->table())
            ->whereRaw('UPPER(TRIM(NAME)) = ?', [strtoupper($name)])
            ->where('ID', '<>', $id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Alat ganti ini telah wujud.');
        }

        $this->table()->where('ID', $id)->update([
            'NAME' => $name,
            'QUANTITY' => (int) $data['quantity'],
            'EDITED_BY' => session('nostaff') ?? 'SYSTEM',
            'EDITED_AT' => now('Asia/Kuala_Lumpur'),
        ]);

        return redirect()->route('alat-ganti-list')->with('success', 'Alat ganti berjaya dikemaskini.');
    }

    public function delete($id)
    {
        $record = $this->activeScope($this->table())->where('ID', $id)->first();

        if (!$record) {
            return back()->with('error', 'Rekod alat ganti tidak dijumpai.');
        }

        $this->table()->where('ID', $id)->update([
            'STATUS_DATA' => 'DELETE',
            'EDITED_BY' => session('nostaff') ?? 'SYSTEM',
            'EDITED_AT' => now('Asia/Kuala_Lumpur'),
        ]);

        return redirect()->route('alat-ganti-list')->with('success', 'Alat ganti berjaya dipadam.');
    }
}
