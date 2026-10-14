@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="serviceCalendar(@js($holidays), @js($entries), {{ $year }})">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Jadual service kenderaan aktif (1 kenderaan setahun) oleh kumpulan {{ $groupName }}.
                Isnin - Khamis: 3 pagi, 2 petang. Jumaat: 3 pagi.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('kalendar-service', ['year' => $year - 1]) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">&laquo; {{ $year - 1 }}</a>
            <span class="px-3 text-lg font-semibold">{{ $year }}</span>
            <a href="{{ route('kalendar-service', ['year' => $year + 1]) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">{{ $year + 1 }} &raquo;</a>

            @if($canGenerate)
                <form id="generate-form" method="POST" action="{{ route('kalendar-service-generate') }}">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="ml-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Jana Service {{ $year }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">Cari no plat</label>
                <select x-ref="search" class="w-full"></select>
                <p class="mt-2 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-800" x-show="searchInfo" x-text="searchInfo"></p>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <button type="button" @click="prev()" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">&lsaquo; Sebelum</button>
                <h4 class="text-xl font-semibold" x-text="monthNames[month] + ' ' + year"></h4>
                <button type="button" @click="next()" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">Seterusnya &rsaquo;</button>
            </div>

            <div class="grid grid-cols-7 text-center text-xs font-semibold uppercase text-gray-500">
                <template x-for="d in ['Ahd','Isn','Sel','Rab','Kha','Jum','Sab']"><div class="py-2" x-text="d"></div></template>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <template x-for="cell in cells()" :key="cell.key">
                    <div @click="cell.date && select(cell.date)"
                         class="min-h-[84px] rounded-lg border p-1.5 text-sm"
                         :class="[
                            !cell.date ? 'border-transparent' : 'cursor-pointer border-gray-200 hover:bg-gray-50',
                            cell.date && selected === cell.date ? 'ring-2 ring-blue-500' : '',
                            cell.date && cell.holidays.length ? 'bg-red-50' : ''
                         ]">
                        <div class="flex items-center justify-between" x-show="cell.date">
                            <span :class="cell.date === today ? 'rounded-full bg-blue-600 px-1.5 text-white' : (cell.weekend ? 'text-red-500' : '')" x-text="cell.day"></span>
                        </div>
                        <template x-for="h in cell.holidays" :key="h.name">
                            <div class="mt-1 truncate rounded px-1 text-[11px] leading-4"
                                 :class="h.scope === 'PENANG' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700'"
                                 :title="h.name" x-text="h.name"></div>
                        </template>
                        <template x-if="cell.pagi > 0">
                            <div class="mt-1 rounded bg-blue-100 px-1 text-[11px] leading-4 text-blue-800" x-text="'Pagi: ' + cell.pagi + ' kenderaan'"></div>
                        </template>
                        <template x-if="cell.petang > 0">
                            <div class="mt-1 rounded bg-green-100 px-1 text-[11px] leading-4 text-green-800" x-text="'Petang: ' + cell.petang + ' kenderaan'"></div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
                <span><span class="mr-1 inline-block h-3 w-3 rounded bg-blue-100"></span>Sesi Pagi</span>
                <span><span class="mr-1 inline-block h-3 w-3 rounded bg-green-100"></span>Sesi Petang</span>
                <span><span class="mr-1 inline-block h-3 w-3 rounded bg-red-100"></span>Cuti Kebangsaan</span>
                <span><span class="mr-1 inline-block h-3 w-3 rounded bg-amber-100"></span>Cuti Negeri Pulau Pinang</span>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="mb-3 text-lg font-semibold">Butiran <span x-text="selected ? fmt(selected) : year"></span></h4>

                <template x-if="selected">
                    <div>
                        <template x-for="h in holidaysOn(selected)" :key="h.name">
                            <div class="mb-2 rounded-lg border border-red-200 bg-red-50 p-2 text-sm" x-text="h.name"></div>
                        </template>

                        <template x-for="sesi in ['PAGI', 'PETANG']" :key="sesi">
                            <div class="mb-4" x-show="entriesOn(selected, sesi).length">
                                <div class="mb-1 text-sm font-semibold" x-text="sesi === 'PAGI' ? 'Sesi Pagi' : 'Sesi Petang'"></div>
                                <ul class="divide-y text-sm">
                                    <template x-for="e in entriesOn(selected, sesi)" :key="e.plat">
                                        <li class="py-2">
                                            <div class="font-medium" x-text="e.plat"></div>
                                            <div class="text-xs text-gray-500" x-text="e.model"></div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <a x-show="entriesOn(selected, 'PAGI').length || entriesOn(selected, 'PETANG').length"
                           :href="'{{ route('servis-harian') }}?tarikh=' + selected"
                           class="mt-2 inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Buka Servis Harian
                        </a>

                        <p class="text-sm text-gray-500"
                           x-show="!holidaysOn(selected).length && !entriesOn(selected, 'PAGI').length && !entriesOn(selected, 'PETANG').length">
                            Tiada service dijadualkan pada tarikh ini.
                        </p>
                    </div>
                </template>

                <p class="text-sm text-gray-500" x-show="!selected">
                    <span x-text="entries.length"></span> kenderaan dijadualkan untuk tahun <span x-text="year"></span>.
                    Klik pada tarikh untuk melihat senarai kenderaan.
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="mb-3 text-lg font-semibold">Senarai Semakan Servis</h4>
                <ol class="grid max-h-[360px] grid-cols-1 gap-x-4 gap-y-1 overflow-y-auto text-sm sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                    @forelse($checklist as $i => $item)
                        <li class="flex gap-2"><span class="w-6 text-right text-gray-400">{{ $i + 1 }}.</span><span>{{ $item }}</span></li>
                    @empty
                        <li class="text-gray-500">Tiada senarai semakan.</li>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
function serviceCalendar(holidays, entries, year) {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');

    const byDate = {};
    entries.forEach(e => {
        (byDate[e.date] = byDate[e.date] || []).push(e);
    });

    return {
        holidays, entries, year,
        month: (now.getFullYear() === year) ? now.getMonth() : 0,
        selected: (now.getFullYear() === year) ? now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) : null,
        today: now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()),
        searchInfo: '',
        init() {
            const tom = new TomSelect(this.$refs.search, {
                create: false,
                maxOptions: 5000,
                placeholder: 'Cari no plat...',
                searchField: ['text'],
                options: this.entries.map(e => ({ value: e.plat, text: e.plat })),
                onChange: (plat) => {
                    const e = this.entries.find(x => x.plat === plat);

                    if (!e) { this.searchInfo = ''; return; }

                    this.month = Number(e.date.slice(5, 7)) - 1;
                    this.selected = e.date;
                    this.searchInfo = e.plat + ' dijadualkan pada ' + e.date.split('-').reverse().join('/') + ' (' + (e.sesi === 'PAGI' ? 'Pagi' : 'Petang') + ').';
                }
            });
        },
        monthNames: ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'],
        cells() {
            const first = new Date(this.year, this.month, 1).getDay();
            const days = new Date(this.year, this.month + 1, 0).getDate();
            const out = [];
            for (let i = 0; i < first; i++) out.push({ key: 'b' + i, date: null, holidays: [] });
            for (let d = 1; d <= days; d++) {
                const date = this.year + '-' + pad(this.month + 1) + '-' + pad(d);
                const dow = new Date(this.year, this.month, d).getDay();
                out.push({
                    key: date, date, day: d, weekend: dow === 0 || dow === 6,
                    holidays: this.holidaysOn(date),
                    pagi: this.entriesOn(date, 'PAGI').length,
                    petang: this.entriesOn(date, 'PETANG').length,
                });
            }
            return out;
        },
        holidaysOn(date) { return this.holidays.filter(h => h.date === date); },
        entriesOn(date, sesi) { return (byDate[date] || []).filter(e => e.sesi === sesi); },
        prev() { if (this.month > 0) this.month--; },
        next() { if (this.month < 11) this.month++; },
        select(d) { this.selected = d; },
        fmt(d) {
            const [y, m, day] = d.split('-').map(Number);
            const dn = ['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'][new Date(y, m - 1, day).getDay()];
            return dn + ', ' + day + ' ' + this.monthNames[m - 1] + ' ' + y;
        },
    };
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('generate-form');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'question',
                title: 'Jana kalendar servis?',
                text: 'Jadual sedia ada untuk tahun ini (jika ada) akan diganti dengan jadual baharu.',
                showCancelButton: true,
                confirmButtonText: 'Ya, jana',
                cancelButtonText: 'Batal',
                heightAuto: false,
                allowOutsideClick: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>

<style>.swal2-container { z-index: 2147483600 !important; }</style>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Berjaya',
        text: @json(session('success')),
        confirmButtonText: 'OK'
    });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ icon: 'error', title: 'Ralat', text: @json(session('error')), confirmButtonText: 'OK', heightAuto: false, allowOutsideClick: false });
});
</script>
@endif
@endsection
