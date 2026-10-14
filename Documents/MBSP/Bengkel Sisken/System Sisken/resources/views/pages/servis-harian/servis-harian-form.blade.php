@extends('layouts.app')

@section('content')
@php
    $fmtDt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : null;
    $status = $form->status ?? null;
    $telefon = $form->no_telefon ?? ($driver['telefon'] ?? null);
    $driverName = $driver['nama'] ?? null;
    $depo = $form->depo ?? $entry->depo;
    $noArahan = $form->no_arahan_kerja ?? null;
@endphp

<div class="container">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">Borang Servis Kenderaan</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $entry->plat }} &middot; {{ \Carbon\Carbon::parse($entry->tarikh)->format('d/m/Y') }} ({{ $entry->sesi === 'PAGI' ? 'Pagi' : 'Petang' }})
            </p>
        </div>
        <a href="{{ $backUrl }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">Kembali</a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if(!$form && !$canEdit)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Borang belum dimulakan. Hanya ketua kumpulan servis boleh mengisi borang ini.
        </div>
    @elseif(!$canEdit && $status === 'DRAFT')
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Borang sedang diisi oleh ketua kumpulan servis.
        </div>
    @endif

    <form method="POST" action="{{ route('servis-harian-save', $entry->id) }}">
        @csrf

        <x-common.component-card title="Maklumat Kenderaan">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach([
                    ['No Arahan Kerja', $noArahan ?? 'Dijana selepas borang dihantar'],
                    ['No Plat', $entry->plat],
                    ['Kenderaan', trim(($entry->buatan ?? '') . ' ' . ($entry->model ?? '')) ?: '-'],
                    ['Pemandu', $driverName ?? '-'],
                    ['No Telefon', $telefon ?: '-'],
                    ['Depo', $depo ?: '-'],
                    ['Tarikh', \Carbon\Carbon::parse($entry->tarikh)->format('d/m/Y')],
                ] as [$label, $value])
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input type="text" value="{{ $value }}" readonly
                               class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-sm">
                    </div>
                @endforeach

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Odometer (km)</label>
                    <input type="number" name="odometer" min="0" value="{{ old('odometer', $form->odometer ?? '') }}"
                           @disabled(!$canEdit)
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm disabled:bg-gray-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Masa Masuk</label>
                    <input type="text" name="masa_masuk" readonly placeholder="Pilih masa" value="{{ old('masa_masuk', $form->masa_masuk ?? '') }}"
                           @disabled(!$canEdit)
                           class="time-picker w-full cursor-pointer rounded-lg border border-gray-300 px-4 py-3 text-sm disabled:bg-gray-100">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Masa Keluar</label>
                    <input type="text" name="masa_keluar" readonly placeholder="Pilih masa" value="{{ old('masa_keluar', $form->masa_keluar ?? '') }}"
                           @disabled(!$canEdit)
                           class="time-picker w-full cursor-pointer rounded-lg border border-gray-300 px-4 py-3 text-sm disabled:bg-gray-100">
                </div>
            </div>
        </x-common.component-card>

        <div class="mt-6"></div>

        <x-common.component-card title="Checklist Servis">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-700">
                            <th class="px-3 py-3">Bil</th>
                            <th class="px-3 py-3">Item</th>
                            <th class="px-3 py-3 text-center">OK</th>
                            <th class="px-3 py-3 text-center">Not OK</th>
                            <th class="px-3 py-3 text-center">Kosongkan</th>
                            <th class="px-3 py-3">Catatan</th>
                            <th class="px-3 py-3">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checklist as $i => $c)
                            @php
                                $it = $items[$c->id] ?? null;
                                // Default OK for a form not saved yet; afterwards show exactly what was saved (blank = cleared).
                                $keputusan = old('items') !== null
                                    ? old("items.{$c->id}.keputusan")
                                    : ($it ? $it->keputusan : 'OK');
                            @endphp
                            <tr class="border-b border-gray-100">
                                <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 font-medium">{{ $c->nama }}</td>
                                <td class="px-3 py-2 text-center">
                                    <input type="radio" name="items[{{ $c->id }}][keputusan]" value="OK"
                                           class="h-4 w-4 accent-green-600" @checked($keputusan === 'OK') @disabled(!$canEdit)>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <input type="radio" name="items[{{ $c->id }}][keputusan]" value="NOT OK"
                                           class="h-4 w-4 accent-red-600" @checked($keputusan === 'NOT OK') @disabled(!$canEdit)>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($canEdit)
                                        <button type="button" title="Kosongkan pilihan (tidak berkaitan)"
                                                onclick="this.closest('tr').querySelectorAll('input[type=radio]').forEach(r => r.checked = false)"
                                                class="rounded border border-gray-300 px-2 py-0.5 text-xs text-gray-500 hover:bg-gray-50">&times;</button>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $c->id }}][catatan]" maxlength="500"
                                           value="{{ old("items.{$c->id}.catatan", $it->catatan ?? '') }}" @disabled(!$canEdit)
                                           class="w-full min-w-[180px] rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $c->id }}][tindakan]" maxlength="500"
                                           value="{{ old("items.{$c->id}.tindakan", $it->tindakan ?? '') }}" @disabled(!$canEdit)
                                           class="w-full min-w-[180px] rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($canEdit)
                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit" name="action" value="save"
                            class="rounded-lg border border-blue-600 px-6 py-3 text-sm font-medium text-blue-600 hover:bg-blue-50">Simpan</button>
                    <button type="submit" name="action" value="submit" id="btn-submit"
                            class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">Hantar untuk Kelulusan</button>
                </div>
            @endif
        </x-common.component-card>
    </form>

    @if($form && in_array($status, ['SUBMITTED', 'APPROVED'], true))
        <div class="mt-6"></div>

        <x-common.component-card title="Kelulusan">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach([
                    ['jkjg', 'JK / JG', $approvers[$form->jkjg_user_id] ?? null, $form->jkjg_approved_at, $canApproveJkjg],
                    ['driver', 'Pemandu', $driverName, $form->driver_approved_at, $canApproveDriver],
                    ['jurutera', 'Penolong Jurutera', $approvers[$form->jurutera_user_id] ?? null, $form->jurutera_approved_at, $canApproveJurutera],
                ] as [$type, $label, $name, $at, $can])
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="mb-2 text-sm font-semibold text-gray-700">{{ $label }}</div>

                        @if($at)
                            <div class="text-sm font-medium text-green-700">Diluluskan</div>
                            <div class="text-sm text-gray-700">{{ $name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $fmtDt($at) }}</div>
                        @elseif($type === 'driver' && !$driverName)
                            <div class="text-sm text-gray-500">Tiada pemandu didaftarkan.</div>
                        @else
                            <div class="mb-3 text-sm text-amber-700">Menunggu kelulusan{{ $type === 'driver' && $driverName ? ' (' . $driverName . ')' : '' }}</div>

                            @if($can)
                                <button type="submit" form="approve-{{ $type }}"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Lulus</button>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </x-common.component-card>

        @foreach(['jkjg', 'driver', 'jurutera'] as $type)
            <form id="approve-{{ $type }}" method="POST" action="{{ route('servis-harian-approve', [$entry->id, $type]) }}" class="hidden">@csrf</form>
        @endforeach
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Click-to-pick time (24h, no typing).
    document.querySelectorAll('.time-picker:not(:disabled)').forEach(function (el) {
        flatpickr(el, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            minuteIncrement: 5,
            allowInput: false
        });
    });

    const btn = document.getElementById('btn-submit');

    if (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Hantar borang untuk kelulusan? Borang tidak boleh diubah selepas dihantar.')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<style>.swal2-container { z-index: 2147483600 !important; }</style>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ icon: 'success', title: 'Berjaya', text: @json(session('success')), confirmButtonText: 'OK', heightAuto: false });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ icon: 'error', title: 'Ralat', text: @json(session('error')), confirmButtonText: 'OK', heightAuto: false });
});
</script>
@endif
@endsection
