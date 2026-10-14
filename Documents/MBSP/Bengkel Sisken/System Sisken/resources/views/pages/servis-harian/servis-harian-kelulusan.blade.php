@extends('layouts.app')

@section('content')
@php
    $fmtDate = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '-';
    $fmtDt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : '-';
@endphp

<div class="container-fluid" x-data="{ tab: 'belum' }">
    <div class="mb-4">
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
        <p class="mt-1 text-sm text-gray-500">Senarai borang servis yang memerlukan atau telah mendapat kelulusan anda.</p>
    </div>

    <x-common.component-card title="Senarai Kelulusan">
        <div class="mb-4 flex gap-2 border-b border-gray-200">
            <button type="button" @click="tab = 'belum'"
                    class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold"
                    :class="tab === 'belum' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                Belum Dilulusakan
                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">{{ count($pending) }}</span>
            </button>
            <button type="button" @click="tab = 'sudah'"
                    class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold"
                    :class="tab === 'sudah' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                Sudah Diluluskan
                <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">{{ count($done) }}</span>
            </button>
        </div>

        <div x-show="tab === 'belum'" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-700">
                        <th class="px-4 py-3">Bil</th>
                        <th class="px-4 py-3">Tarikh Servis</th>
                        <th class="px-4 py-3">No Plat</th>
                        <th class="px-4 py-3">No Arahan Kerja</th>
                        <th class="px-4 py-3">Peranan Anda</th>
                        <th class="px-4 py-3">Menunggu Kelulusan</th>
                        <th class="px-4 py-3 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pending as $i => $r)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">{{ $fmtDate($r->tarikh) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $r->plat }}</td>
                            <td class="px-4 py-3">{{ $r->no_arahan_kerja ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $r->peranan }}</td>
                            <td class="px-4 py-3">{{ $r->menunggu }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('servis-harian-show', $r->kid) }}"
                                   class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">Semak &amp; Lulus</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tiada borang menunggu kelulusan anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="tab === 'sudah'" x-cloak class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-700">
                        <th class="px-4 py-3">Bil</th>
                        <th class="px-4 py-3">Tarikh Servis</th>
                        <th class="px-4 py-3">No Plat</th>
                        <th class="px-4 py-3">No Arahan Kerja</th>
                        <th class="px-4 py-3">Peranan Anda</th>
                        <th class="px-4 py-3">Tarikh Kelulusan</th>
                        <th class="px-4 py-3 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($done as $i => $r)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">{{ $fmtDate($r->tarikh) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $r->plat }}</td>
                            <td class="px-4 py-3">{{ $r->no_arahan_kerja ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $r->peranan }}</td>
                            <td class="px-4 py-3">{{ $fmtDt($r->diluluskan_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('servis-harian-show', $r->kid) }}"
                                   class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Anda belum meluluskan sebarang borang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

<style>
    [x-cloak] { display: none !important; }
    .swal2-container { z-index: 2147483600 !important; }
</style>
@endsection
