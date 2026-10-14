@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="holidayCalendar(@js($holidays), {{ $year }})">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Cuti umum kebangsaan dan cuti negeri Pulau Pinang.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kalendar-cuti', ['year' => $year - 1]) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">&laquo; {{ $year - 1 }}</a>
            <span class="px-3 text-lg font-semibold">{{ $year }}</span>
            <a href="{{ route('kalendar-cuti', ['year' => $year + 1]) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">{{ $year + 1 }} &raquo;</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
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
                    </div>
                </template>
            </div>

            <div class="mt-4 flex gap-4 text-xs text-gray-600">
                <span><span class="mr-1 inline-block h-3 w-3 rounded bg-red-100"></span>Cuti Kebangsaan</span>
                <span><span class="mr-1 inline-block h-3 w-3 rounded bg-amber-100"></span>Cuti Negeri Pulau Pinang</span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="mb-3 text-lg font-semibold">Butiran Cuti <span x-text="year"></span></h4>
            <template x-if="selectedHolidays().length">
                <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm">
                    <div class="mb-1 font-semibold" x-text="fmt(selected)"></div>
                    <template x-for="h in selectedHolidays()" :key="h.name">
                        <div>
                            <div x-text="h.name"></div>
                            <div class="text-xs text-gray-600" x-show="h.replacement" x-text="'Cuti ganti untuk ' + fmt(h.replacement_for)"></div>
                            <div class="text-xs text-gray-600" x-show="h.remarks" x-text="h.remarks"></div>
                        </div>
                    </template>
                </div>
            </template>

            <ul class="max-h-[560px] divide-y overflow-y-auto text-sm">
                <template x-for="h in holidays" :key="h.date + h.name">
                    <li class="cursor-pointer py-2 hover:bg-gray-50" @click="goto(h.date)">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-medium" x-text="h.name"></div>
                                <div class="text-xs text-gray-500" x-text="fmt(h.date)"></div>
                            </div>
                            <span class="whitespace-nowrap rounded-full px-2 py-0.5 text-[11px]"
                                  :class="h.scope === 'PENANG' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700'"
                                  x-text="h.scope === 'PENANG' ? 'Pulau Pinang' : 'Kebangsaan'"></span>
                        </div>
                    </li>
                </template>
                <li class="py-3 text-gray-500" x-show="!holidays.length">Tiada data cuti untuk tahun ini.</li>
            </ul>
        </div>
    </div>
</div>

<script>
function holidayCalendar(holidays, year) {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    return {
        holidays, year,
        month: (now.getFullYear() === year) ? now.getMonth() : 0,
        selected: (now.getFullYear() === year) ? now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) : null,
        today: now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()),
        monthNames: ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'],
        cells() {
            const first = new Date(this.year, this.month, 1).getDay();
            const days = new Date(this.year, this.month + 1, 0).getDate();
            const out = [];
            for (let i = 0; i < first; i++) out.push({ key: 'b' + i, date: null, holidays: [] });
            for (let d = 1; d <= days; d++) {
                const date = this.year + '-' + pad(this.month + 1) + '-' + pad(d);
                const dow = new Date(this.year, this.month, d).getDay();
                out.push({ key: date, date, day: d, weekend: dow === 0 || dow === 6,
                           holidays: this.holidays.filter(h => h.date === date) });
            }
            return out;
        },
        prev() { if (this.month > 0) this.month--; },
        next() { if (this.month < 11) this.month++; },
        select(d) { this.selected = d; },
        selectedHolidays() { return this.selected ? this.holidays.filter(h => h.date === this.selected) : []; },
        goto(d) { this.month = parseInt(d.substr(5, 2), 10) - 1; this.selected = d; },
        fmt(d) {
            if (!d) return '';
            const [y, m, day] = d.split('-').map(Number);
            const dn = ['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'][new Date(y, m - 1, day).getDay()];
            return dn + ', ' + day + ' ' + this.monthNames[m - 1] + ' ' + y;
        },
    };
}
</script>
@endsection
