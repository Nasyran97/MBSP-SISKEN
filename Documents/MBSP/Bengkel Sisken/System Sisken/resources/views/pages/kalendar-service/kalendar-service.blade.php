@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="serviceCalendar(@js($holidays), @js($entries), {{ $year }}, @js($today), @js($canManage), @js($slots))" @keydown.escape.window="closeMove()">

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
                         class="min-h-[96px] rounded-lg border p-1.5 text-sm"
                         :class="[
                            !cell.date ? 'border-transparent' : 'cursor-pointer hover:brightness-95',
                            cell.date && !cell.state ? 'border-gray-200' : '',
                            cell.state ? states[cell.state].cell : '',
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
                        <template x-if="cell.total > 0">
                            <div class="mt-1">
                                <div class="text-[11px] font-semibold leading-4" :class="states[cell.state].text"
                                     x-text="cell.done + '/' + cell.total + ' siap'"></div>
                                <div class="mt-0.5 h-1.5 w-full overflow-hidden rounded-full bg-white/70">
                                    <div class="h-full rounded-full" :class="states[cell.state].bar" :style="'width:' + (cell.done / cell.total * 100) + '%'"></div>
                                </div>
                                <div class="mt-1 text-[10px] leading-3 text-gray-600">
                                    <span x-show="cell.pagi">Pagi <b x-text="cell.pagi"></b></span>
                                    <span x-show="cell.pagi && cell.petang"> · </span>
                                    <span x-show="cell.petang">Petang <b x-text="cell.petang"></b></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="mt-4 space-y-2 text-xs text-gray-600">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="font-semibold text-gray-700">Status hari:</span>
                    <template x-for="key in ['BELUM', 'PROSES', 'SELESAI', 'TERTUNGGAK']" :key="key">
                        <span class="inline-flex items-center">
                            <span class="mr-1 inline-block h-3 w-3 rounded border" :class="states[key].cell"></span>
                            <span x-text="states[key].label"></span>
                        </span>
                    </template>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="font-semibold text-gray-700">Status kenderaan:</span>
                    <template x-for="key in ['BELUM', 'DRAFT', 'SUBMITTED', 'COMPLETE']" :key="key">
                        <span class="inline-flex items-center">
                            <span class="mr-1 inline-block h-2.5 w-2.5 rounded-full" :class="vehicleStatus[key].dot"></span>
                            <span x-text="vehicleStatus[key].label"></span>
                        </span>
                    </template>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="font-semibold text-gray-700">Cuti:</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded bg-red-100"></span>Cuti Kebangsaan</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded bg-amber-100"></span>Cuti Negeri Pulau Pinang</span>
                </div>
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
                                        <li class="flex items-center justify-between gap-2 py-2">
                                            <div>
                                                <div class="font-medium" x-text="e.plat"></div>
                                                <div class="text-xs text-gray-500" x-text="e.model"></div>
                                            </div>
                                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[11px] font-medium"
                                                  :class="vehicleStatus[e.status].badge">
                                                <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full" :class="vehicleStatus[e.status].dot"></span>
                                                <span x-text="vehicleStatus[e.status].label"></span>
                                            </span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <div class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-3" x-show="canManage && dayEntries(selected).length">
                            <div class="mb-2 text-xs font-semibold uppercase text-gray-500">Ubah jadual hari ini</div>

                            <template x-if="dayStarted(selected)">
                                <p class="text-xs text-amber-700">Hari ini sudah ada kenderaan yang sudah dimulakan servis, jadi tidak boleh dipindah atau ditukar.</p>
                            </template>

                            <div class="grid grid-cols-2 gap-2" x-show="!dayStarted(selected)">
                                <button type="button" @click="openMove('move')"
                                        class="group flex flex-col items-center gap-1 rounded-xl border border-blue-200 bg-white px-3 py-3 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-500 hover:bg-blue-50">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    Pindah Hari
                                    <span class="text-[11px] font-normal text-gray-500">ke hari kosong</span>
                                </button>
                                <button type="button" @click="openMove('swap')"
                                        class="group flex flex-col items-center gap-1 rounded-xl border border-indigo-200 bg-white px-3 py-3 text-sm font-semibold text-indigo-700 shadow-sm transition hover:border-indigo-500 hover:bg-indigo-50">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4L3 8l4 4M3 8h14M17 20l4-4-4-4M21 16H7"/></svg>
                                    Tukar Hari
                                    <span class="text-[11px] font-normal text-gray-500">dengan hari lain</span>
                                </button>
                            </div>
                        </div>

                        <form id="move-day-form" method="POST" action="{{ route('kalendar-service-move-day') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="from">
                            <input type="hidden" name="to">
                            <input type="hidden" name="mode">
                        </form>

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

    {{-- Pindah / Tukar Hari: pick the target day on a calendar --}}
    <div x-show="mv.open" x-cloak style="display:none; z-index:2147483000"
         class="fixed inset-0 flex items-center justify-center bg-gray-900/60 p-4"
         x-transition.opacity>
        <div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" @click.outside="closeMove()">
            <div class="flex items-start justify-between gap-4 px-6 py-4 text-white"
                 :class="mv.mode === 'move' ? 'bg-gradient-to-r from-blue-600 to-blue-500' : 'bg-gradient-to-r from-indigo-600 to-indigo-500'">
                <div>
                    <h3 class="text-xl font-bold" x-text="mv.mode === 'move' ? 'Pindah Hari Servis' : 'Tukar Hari Servis'"></h3>
                    <p class="mt-0.5 text-sm text-white/85"
                       x-text="mv.mode === 'move'
                            ? 'Pilih hari kosong (hijau) untuk memindahkan semua kenderaan.'
                            : 'Pilih hari yang sudah ada jadual (ungu) untuk ditukar ganti.'"></p>
                </div>
                <button type="button" @click="closeMove()" class="rounded-lg p-1 text-white/80 hover:bg-white/15 hover:text-white" aria-label="Tutup">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <div class="grid flex-1 grid-cols-1 gap-5 overflow-y-auto p-6 md:grid-cols-5">
                {{-- calendar --}}
                <div class="md:col-span-3">
                    <div class="mb-3 flex items-center justify-between">
                        <button type="button" @click="mvPrev()" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50" :disabled="mv.month === 0" :class="mv.month === 0 ? 'opacity-40' : ''">&lsaquo;</button>
                        <div class="font-semibold" x-text="monthNames[mv.month] + ' ' + year"></div>
                        <button type="button" @click="mvNext()" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50" :disabled="mv.month === 11" :class="mv.month === 11 ? 'opacity-40' : ''">&rsaquo;</button>
                    </div>

                    <div class="grid grid-cols-7 text-center text-[11px] font-semibold uppercase text-gray-500">
                        <template x-for="d in ['Ahd','Isn','Sel','Rab','Kha','Jum','Sab']"><div class="py-1" x-text="d"></div></template>
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="c in mvCells()" :key="c.key">
                            <button type="button"
                                    class="relative flex h-14 flex-col items-center justify-center rounded-lg border text-sm transition"
                                    :class="c.cls"
                                    :disabled="!c.date || !c.ok"
                                    :title="c.title"
                                    @click="c.ok && (mv.target = c.date)">
                                <span x-text="c.day"></span>
                                <span class="text-[10px] leading-3" x-show="c.sub" x-text="c.sub"></span>
                            </button>
                        </template>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-600">
                        <span class="inline-flex items-center"><span class="mr-1 inline-block h-3 w-3 rounded border-2 border-violet-500 bg-violet-100"></span>Hari asal</span>
                        <span class="inline-flex items-center" x-show="mv.mode === 'move'"><span class="mr-1 inline-block h-3 w-3 rounded border border-emerald-400 bg-emerald-50"></span>Boleh dipilih (kosong)</span>
                        <span class="inline-flex items-center" x-show="mv.mode === 'swap'"><span class="mr-1 inline-block h-3 w-3 rounded border border-indigo-400 bg-indigo-50"></span>Boleh dipilih (ada jadual)</span>
                        <span class="inline-flex items-center"><span class="mr-1 inline-block h-3 w-3 rounded border bg-gray-100"></span>Tidak boleh dipilih</span>
                        <span class="inline-flex items-center"><span class="mr-1 inline-block h-3 w-3 rounded bg-red-100"></span>Cuti</span>
                    </div>
                </div>

                {{-- summary --}}
                <div class="space-y-3 md:col-span-2">
                    <div class="rounded-xl border border-violet-200 bg-violet-50 p-3">
                        <div class="text-[11px] font-semibold uppercase text-violet-700">Hari asal</div>
                        <div class="font-semibold" x-text="mv.from ? fmt(mv.from) : ''"></div>
                        <template x-for="sesi in ['PAGI', 'PETANG']" :key="sesi">
                            <div class="mt-2" x-show="mv.from && entriesOn(mv.from, sesi).length">
                                <div class="text-xs font-medium text-gray-600" x-text="sesi === 'PAGI' ? 'Pagi' : 'Petang'"></div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <template x-for="e in (mv.from ? entriesOn(mv.from, sesi) : [])" :key="e.plat">
                                        <span class="rounded-md bg-white px-2 py-0.5 text-xs font-medium text-gray-800 ring-1 ring-violet-200" x-text="e.plat"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-center text-gray-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path x-show="mv.mode === 'move'" d="M12 5v14M6 13l6 6 6-6"/>
                            <path x-show="mv.mode === 'swap'" d="M8 4L4 8l4 4M4 8h16M16 20l4-4-4-4M20 16H4"/>
                        </svg>
                    </div>

                    <div class="rounded-xl border p-3" :class="mv.target ? (mv.mode === 'move' ? 'border-emerald-300 bg-emerald-50' : 'border-indigo-300 bg-indigo-50') : 'border-dashed border-gray-300 bg-gray-50'">
                        <div class="text-[11px] font-semibold uppercase" :class="mv.target ? 'text-gray-700' : 'text-gray-500'" x-text="mv.mode === 'move' ? 'Dipindah ke' : 'Ditukar dengan'"></div>

                        <p class="mt-1 text-sm text-gray-500" x-show="!mv.target">Belum dipilih. Klik satu tarikh pada kalendar.</p>

                        <div x-show="mv.target">
                            <div class="font-semibold" x-text="mv.target ? fmt(mv.target) : ''"></div>
                            <template x-if="mv.mode === 'swap' && mv.target">
                                <div>
                                    <template x-for="sesi in ['PAGI', 'PETANG']" :key="sesi">
                                        <div class="mt-2" x-show="entriesOn(mv.target, sesi).length">
                                            <div class="text-xs font-medium text-gray-600" x-text="sesi === 'PAGI' ? 'Pagi' : 'Petang'"></div>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                <template x-for="e in entriesOn(mv.target, sesi)" :key="e.plat">
                                                    <span class="rounded-md bg-white px-2 py-0.5 text-xs font-medium text-gray-800 ring-1 ring-indigo-200" x-text="e.plat"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <p class="mt-2 text-xs text-gray-600">Kedua-dua hari akan bertukar kenderaan.</p>
                                </div>
                            </template>
                            <p class="mt-1 text-xs text-gray-600" x-show="mv.mode === 'move' && mv.target">
                                <span x-text="mv.from ? dayEntries(mv.from).length : 0"></span> kenderaan akan dipindahkan dengan sesi yang sama.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t bg-gray-50 px-6 py-3">
                <button type="button" @click="closeMove()" class="rounded-lg border bg-white px-4 py-2 text-sm font-medium hover:bg-gray-100">Batal</button>
                <button type="button" @click="confirmMove()" :disabled="!mv.target"
                        class="rounded-lg px-5 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40"
                        :class="mv.mode === 'move' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                        x-text="mv.mode === 'move' ? 'Sahkan Pindah' : 'Sahkan Tukar'"></button>
            </div>
        </div>
    </div>
</div>

<script>
function serviceCalendar(holidays, entries, year, todayDate, canManage, slots) {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');

    const byDate = {};
    entries.forEach(e => {
        (byDate[e.date] = byDate[e.date] || []).push(e);
    });

    return {
        holidays, entries, year, canManage,
        // Colour per day and per vehicle, shared by the grid, the detail panel and the legend.
        states: {
            BELUM:      { label: 'Belum mula',       cell: 'border-sky-300 bg-sky-50',        text: 'text-sky-800',     bar: 'bg-sky-500' },
            PROSES:     { label: 'Sedang berjalan',  cell: 'border-yellow-400 bg-yellow-50',  text: 'text-yellow-800',  bar: 'bg-yellow-500' },
            SELESAI:    { label: 'Selesai',          cell: 'border-emerald-400 bg-emerald-100', text: 'text-emerald-800', bar: 'bg-emerald-600' },
            TERTUNGGAK: { label: 'Tertunggak',       cell: 'border-rose-400 bg-rose-100',     text: 'text-rose-800',    bar: 'bg-rose-500' },
        },
        vehicleStatus: {
            BELUM:     { label: 'Belum mula',          dot: 'bg-sky-500',     badge: 'bg-sky-100 text-sky-800' },
            DRAFT:     { label: 'Dalam proses',        dot: 'bg-yellow-500',  badge: 'bg-yellow-100 text-yellow-800' },
            SUBMITTED: { label: 'Menunggu kelulusan',  dot: 'bg-orange-500',  badge: 'bg-orange-100 text-orange-800' },
            COMPLETE:  { label: 'Selesai',             dot: 'bg-emerald-600', badge: 'bg-emerald-100 text-emerald-800' },
        },
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
                const day = this.dayEntries(date);
                const done = day.filter(e => e.status === 'COMPLETE').length;
                out.push({
                    key: date, date, day: d, weekend: dow === 0 || dow === 6,
                    holidays: this.holidaysOn(date),
                    pagi: this.entriesOn(date, 'PAGI').length,
                    petang: this.entriesOn(date, 'PETANG').length,
                    total: day.length, done,
                    state: this.dayState(date, day, done),
                });
            }
            return out;
        },
        dayEntries(date) { return byDate[date] || []; },
        dayStarted(date) { return this.dayEntries(date).some(e => e.status !== 'BELUM'); },
        dayState(date, day, done) {
            if (!day.length) return null;
            if (done === day.length) return 'SELESAI';
            if (date < todayDate) return 'TERTUNGGAK';
            return day.some(e => e.status !== 'BELUM') ? 'PROSES' : 'BELUM';
        },
        // ---- Pindah / Tukar Hari modal
        mv: { open: false, mode: 'move', from: null, target: null, month: 0 },
        slotsOf(date) { return slots[new Date(date + 'T00:00:00').getDay()] || null; },
        sesiCount(date, sesi) { return this.entriesOn(date, sesi).length; },
        // Do the cars of `src` fit the session slots of `dst`?
        fits(src, dst) {
            const s = this.slotsOf(dst) || {};
            return ['PAGI', 'PETANG'].every(k => this.sesiCount(src, k) <= (s[k] || 0));
        },
        mvInfo(date) {
            const from = this.mv.from;
            const swap = this.mv.mode === 'swap';

            if (date === from) return { ok: false, origin: true, reason: 'Hari asal' };
            if (date < todayDate) return { ok: false, reason: 'Tarikh sudah berlalu' };
            if (!this.slotsOf(date)) return { ok: false, reason: 'Hujung minggu' };
            if (this.holidaysOn(date).length) return { ok: false, reason: 'Hari cuti' };

            const has = this.dayEntries(date).length;

            if (!swap) {
                if (has) return { ok: false, reason: 'Sudah ada jadual (guna Tukar Hari)' };
                if (!this.fits(from, date)) return { ok: false, reason: 'Sesi petang tidak muat pada hari ini' };
                return { ok: true };
            }

            if (from < todayDate) return { ok: false, reason: 'Hari asal sudah berlalu; guna Pindah Hari' };
            if (!has) return { ok: false, reason: 'Tiada jadual untuk ditukar (guna Pindah Hari)' };
            if (this.dayStarted(date)) return { ok: false, reason: 'Ada kenderaan sudah dimulakan servis' };
            if (!this.fits(from, date) || !this.fits(date, from)) return { ok: false, reason: 'Sesi tidak muat apabila ditukar' };

            return { ok: true };
        },
        mvCells() {
            const m = this.mv.month;
            const first = new Date(this.year, m, 1).getDay();
            const days = new Date(this.year, m + 1, 0).getDate();
            const out = [];

            for (let i = 0; i < first; i++) out.push({ key: 'b' + i, date: null, cls: 'border-transparent' });

            for (let d = 1; d <= days; d++) {
                const date = this.year + '-' + pad(m + 1) + '-' + pad(d);
                const info = this.mvInfo(date);
                const n = this.dayEntries(date).length;
                const holiday = this.holidaysOn(date).length > 0;
                let cls, sub = '';

                if (info.origin) {
                    cls = 'border-2 border-violet-500 bg-violet-100 font-semibold text-violet-800 cursor-default';
                    sub = 'asal';
                } else if (info.ok) {
                    cls = (this.mv.mode === 'move'
                        ? 'border-emerald-400 bg-emerald-50 hover:bg-emerald-100'
                        : 'border-indigo-400 bg-indigo-50 hover:bg-indigo-100') + ' cursor-pointer font-medium';
                    sub = this.mv.mode === 'move' ? 'kosong' : n + ' kend.';
                } else if (holiday) {
                    cls = 'border-red-200 bg-red-50 text-red-400 cursor-not-allowed';
                } else {
                    cls = 'border-gray-100 bg-gray-100 text-gray-400 cursor-not-allowed';
                    sub = n ? n + ' kend.' : '';
                }

                if (this.mv.target === date) cls += ' ring-2 ring-blue-600 ring-offset-1 !bg-blue-600 !text-white !border-blue-600';

                out.push({ key: date, date, day: d, ok: info.ok, cls, sub, title: info.ok ? this.fmt(date) : info.reason });
            }

            return out;
        },
        openMove(mode) {
            if (!this.selected) return;
            this.mv = { open: true, mode, from: this.selected, target: null, month: Number(this.selected.slice(5, 7)) - 1 };
        },
        closeMove() { this.mv.open = false; },
        mvPrev() { if (this.mv.month > 0) this.mv.month--; },
        mvNext() { if (this.mv.month < 11) this.mv.month++; },
        confirmMove() {
            if (!this.mv.target) return;
            const f = document.getElementById('move-day-form');
            f.from.value = this.mv.from;
            f.to.value = this.mv.target;
            f.mode.value = this.mv.mode;
            f.submit();
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
                text: 'Kenderaan yang servisnya sudah dimulakan dikekalkan. Hanya kenderaan yang belum mula dijadualkan semula (dari hari ini jika tahun semasa).',
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
