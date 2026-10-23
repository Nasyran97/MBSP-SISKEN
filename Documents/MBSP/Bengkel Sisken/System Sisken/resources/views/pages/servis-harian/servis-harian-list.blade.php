@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="{ swapOpen: false, swapId: null, swapPlat: '' }">
    <div class="mb-4">
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
        <p class="mt-1 text-sm text-gray-500">
            Kenderaan yang dijadualkan untuk servis pada {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}{{ $isToday ? ' (hari ini)' : '' }}.
            Klik kenderaan untuk membuka borang servis.
            @unless($isToday)
                <a href="{{ route('servis-harian') }}" class="ml-1 text-blue-600 hover:underline">Papar hari ini</a>
            @endunless
        </p>
    </div>

    <x-common.component-card :title="$isToday ? 'Senarai Servis Hari Ini' : 'Senarai Servis'">
        <div class="overflow-x-auto p-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-700">
                        <th class="px-4 py-3">Bil</th>
                        <th class="px-4 py-3">Sesi</th>
                        <th class="px-4 py-3">No Plat</th>
                        <th class="px-4 py-3">Kenderaan</th>
                        <th class="px-4 py-3">Pemandu</th>
                        <th class="px-4 py-3">Depo</th>
                        <th class="px-4 py-3">No Arahan Kerja</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $i => $e)
                        @php
                            $status = $e->status ?? null;
                            $badge = match($status) {
                                'COMPLETE' => ['Selesai', 'bg-green-100 text-green-800'],
                                'SUBMITTED' => ['Menunggu Kelulusan', 'bg-amber-100 text-amber-800'],
                                'DRAFT' => ['Dalam Proses', 'bg-blue-100 text-blue-800'],
                                default => ['Belum Mula', 'bg-gray-100 text-gray-700'],
                            };
                        @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">{{ $e->sesi === 'PAGI' ? 'Pagi' : 'Petang' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $e->no_plat }}</td>
                            <td class="px-4 py-3">{{ trim(($e->buatan ?? '') . ' ' . ($e->model ?? '')) ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $e->driver ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $e->depo ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $e->no_arahan_kerja ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('servis-harian-show', $e->id) }}"
                                       class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                        Buka Borang
                                    </a>

                                    @if($canSwap && $status === null)
                                        <button type="button"
                                                @click="swapOpen = true; swapId = {{ $e->id }}; swapPlat = @js($e->no_plat); $dispatch('swap-open', { id: {{ $e->id }} })"
                                                class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Tukar Tarikh
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                Tiada kenderaan dijadualkan untuk servis pada tarikh ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    @if($canSwap)
        {{-- Teleported to <body> so the overlay sits above the sidemenu/header and blocks all background clicks. --}}
        <template x-teleport="body">
        <div x-show="swapOpen" x-cloak
             x-effect="document.body.style.overflow = swapOpen ? 'hidden' : ''"
             class="fixed inset-0 flex items-center justify-center bg-black/50 p-4"
             style="z-index: 2147483000;"
             @keydown.escape.window="swapOpen = false">
            <div class="max-h-[95vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl" @click.outside="swapOpen = false">
                <h4 class="mb-1 text-lg font-semibold">Tukar Tarikh Servis</h4>
                <p class="mb-4 text-sm text-gray-500">
                    Tarikh dan sesi <span class="font-semibold" x-text="swapPlat"></span> akan ditukar dengan kenderaan yang dipilih.
                </p>

                <form method="POST" :action="'{{ url('/servis-harian') }}/' + swapId + '/swap'"
                      x-data="swapPicker(@js($swapCandidates))"
                      @swap-open.window="open($event.detail.id)">
                    @csrf
                    <input type="hidden" name="target_id" :value="targetId">

                    <label class="mb-2 block text-sm font-medium text-gray-700">Cari no plat</label>
                    <select x-ref="search" class="w-full"></select>

                    <p class="mt-2 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-800" x-show="searchedInfo" x-text="searchedInfo"></p>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div class="lg:col-span-2">
                            <div class="mb-3 flex items-center justify-between">
                                <button type="button" @click="prev()" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">&lsaquo;</button>
                                <h5 class="font-semibold" x-text="monthNames[month] + ' ' + year"></h5>
                                <button type="button" @click="next()" class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">&rsaquo;</button>
                            </div>

                            <div class="grid grid-cols-7 text-center text-xs font-semibold uppercase text-gray-500">
                                <template x-for="d in ['Ahd','Isn','Sel','Rab','Kha','Jum','Sab']"><div class="py-1" x-text="d"></div></template>
                            </div>

                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="cell in cells()" :key="cell.key">
                                    <div @click="cell.date && cell.count && pickDate(cell.date)"
                                         class="min-h-[64px] rounded-lg border p-1 text-sm"
                                         :class="[
                                            !cell.date ? 'border-transparent' : (cell.count ? 'cursor-pointer border-gray-200 hover:bg-gray-50' : 'border-gray-100 text-gray-400'),
                                            cell.date && selectedDate === cell.date ? 'ring-2 ring-blue-500' : ''
                                         ]">
                                        <div x-show="cell.date" x-text="cell.day"></div>
                                        <template x-if="cell.pagi > 0">
                                            <div class="mt-0.5 rounded bg-blue-100 px-1 text-[10px] leading-4 text-blue-800" x-text="'Pagi: ' + cell.pagi"></div>
                                        </template>
                                        <template x-if="cell.petang > 0">
                                            <div class="mt-0.5 rounded bg-green-100 px-1 text-[10px] leading-4 text-green-800" x-text="'Petang: ' + cell.petang"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 p-3">
                            <h5 class="mb-2 text-sm font-semibold">
                                <span x-text="selectedDate ? fmt(selectedDate) : 'Butiran tarikh'"></span>
                            </h5>

                            <p class="text-sm text-gray-500" x-show="!selectedDate">Klik tarikh pada kalendar untuk melihat kenderaan.</p>

                            <template x-for="sesi in ['PAGI', 'PETANG']" :key="sesi">
                                <div class="mb-3" x-show="selectedDate && carsOn(selectedDate, sesi).length">
                                    <div class="mb-1 text-xs font-semibold uppercase text-gray-500" x-text="sesi === 'PAGI' ? 'Sesi Pagi' : 'Sesi Petang'"></div>
                                    <template x-for="c in carsOn(selectedDate, sesi)" :key="c.id">
                                        <label class="mb-1 flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm hover:bg-gray-50"
                                               :class="String(targetId) === String(c.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                            <input type="radio" class="accent-blue-600" :checked="String(targetId) === String(c.id)" @change="pickCar(c.id)">
                                            <span class="font-medium" x-text="c.plat"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" @click="swapOpen = false" class="rounded-lg border px-5 py-2.5 text-sm hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="!targetId"
                                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Tukar</button>
                    </div>
                </form>
            </div>
        </div>
        </template>
    @endif
</div>

<style>[x-cloak] { display: none !important; } .swal2-container { z-index: 2147483600 !important; }</style>

@if($canSwap)
<script>
function swapPicker(candidates) {
    const pad = n => String(n).padStart(2, '0');
    const monthNames = ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];
    const fmtShort = d => d.split('-').reverse().join('/');

    return {
        all: candidates,
        monthNames,
        currentId: null,
        year: new Date().getFullYear(),
        month: new Date().getMonth(),
        selectedDate: null,
        targetId: '',
        searchedInfo: '',
        tom: null,

        init() {
            this.tom = new TomSelect(this.$refs.search, {
                create: false,
                maxOptions: 2000,
                placeholder: 'Cari no plat...',
                searchField: ['text'],
                onChange: (v) => this.pickPlat(v)
            });
        },

        others() { return this.all.filter(c => String(c.id) !== String(this.currentId)); },

        open(id) {
            this.currentId = id;
            this.selectedDate = null;
            this.targetId = '';
            this.searchedInfo = '';

            this.tom.clear(true);
            this.tom.clearOptions();
            this.tom.addOptions(this.others().map(c => ({ value: String(c.id), text: c.plat })));
            this.tom.refreshOptions(false);

            const list = this.others();
            const today = new Date();
            const first = list[0];

            // Open on the current month if it has cars; otherwise the first month that does.
            const ym = today.getFullYear() + '-' + pad(today.getMonth() + 1);
            const hit = list.find(c => c.tarikh.startsWith(ym)) || first;

            if (hit) {
                this.year = Number(hit.tarikh.slice(0, 4));
                this.month = Number(hit.tarikh.slice(5, 7)) - 1;
            }
        },

        carsOn(date, sesi) { return this.others().filter(c => c.tarikh === date && c.sesi === sesi); },

        cells() {
            const first = new Date(this.year, this.month, 1).getDay();
            const days = new Date(this.year, this.month + 1, 0).getDate();
            const out = [];

            for (let i = 0; i < first; i++) out.push({ key: 'b' + i, date: null });

            for (let d = 1; d <= days; d++) {
                const date = this.year + '-' + pad(this.month + 1) + '-' + pad(d);
                const pagi = this.carsOn(date, 'PAGI').length;
                const petang = this.carsOn(date, 'PETANG').length;

                out.push({ key: date, date, day: d, pagi, petang, count: pagi + petang });
            }

            return out;
        },

        prev() { if (this.month > 0) this.month--; },
        next() { if (this.month < 11) this.month++; },

        pickDate(date) {
            this.selectedDate = date;
            this.targetId = '';
            this.searchedInfo = '';
            this.tom.clear(true);
        },

        pickCar(id) {
            this.targetId = String(id);
            this.searchedInfo = '';
            this.tom.setValue(String(id), true);
        },

        // Chosen from the search box: jump the calendar to that car's date.
        pickPlat(id) {
            const c = this.others().find(x => String(x.id) === String(id));

            if (!c) {
                this.targetId = '';
                this.searchedInfo = '';
                return;
            }

            this.targetId = String(c.id);
            this.selectedDate = c.tarikh;
            this.year = Number(c.tarikh.slice(0, 4));
            this.month = Number(c.tarikh.slice(5, 7)) - 1;
            this.searchedInfo = c.plat + ' dijadualkan pada ' + fmtShort(c.tarikh) + ' (' + (c.sesi === 'PAGI' ? 'Pagi' : 'Petang') + ').';
        },

        fmt(d) {
            const [y, m, day] = d.split('-').map(Number);
            const dn = ['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'][new Date(y, m - 1, day).getDay()];
            return dn + ', ' + day + ' ' + monthNames[m - 1] + ' ' + y;
        },
    };
}
</script>
@endif

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

@if(session('error') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ icon: 'error', title: 'Ralat', text: @json(session('error') ?? $errors->first()), confirmButtonText: 'OK', heightAuto: false });
});
</script>
@endif
@endsection
