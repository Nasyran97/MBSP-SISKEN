@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Kemaskini kenderaan untuk pemandu ini.
            </p>
        </div>

        <a href="{{ route('pemandu-list') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-common.component-card title="Kemaskini Kenderaan Pemandu">
        @php
            $userId = $pemandu->USER_ID ?? $pemandu->user_id ?? null;

            $selectedVehicleIds = collect(old('vehicle_id', $currentVehicleIds ?? []))
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();
        @endphp

        <form method="POST" action="{{ route('pemandu-update-vehicle', $userId) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    No Staff
                </label>

                <input type="text"
                       value="{{ $pemandu->NOSTAFF ?? $pemandu->nostaff ?? '-' }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                       disabled>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Nama
                </label>

                <input type="text"
                       value="{{ $pemandu->NAMA ?? $pemandu->nama ?? '-' }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                       disabled>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Jabatan
                </label>

                <input type="text"
                       value="{{ $pemandu->JABATAN ?? $pemandu->jabatan ?? '-' }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                       disabled>
            </div>

            <div class="mb-4">
                <label for="no_telefon" class="mb-2 block text-sm font-medium text-gray-700">
                    No Telefon
                </label>

                <input type="text"
                       name="no_telefon"
                       id="no_telefon"
                       value="{{ old('no_telefon', $pemandu->NO_TELEFON ?? $pemandu->no_telefon ?? '') }}"
                       maxlength="20"
                       placeholder="cth: 012-3456789"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                       required>

                @error('no_telefon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="vehicle_id"class="mb-2 block text-sm font-medium text-gray-700">
                    No Plat
                </label>

                <select name="vehicle_id[]" id="vehicle_id"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    multiple
                    required>

                    @foreach($vehicles as $v)
                        @php
                            $vehicleId = $v->ID ?? $v->id ?? null;
                            $noPlat = $v->NO_PLAT ?? $v->no_plat ?? '-';
                        @endphp

                        <option value="{{ $vehicleId }}"
                            {{ in_array((string) $vehicleId, $selectedVehicleIds, true) ? 'selected' : '' }}>
                            {{ $noPlat }}
                        </option>
                    @endforeach
                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Boleh pilih lebih daripada satu no plat.
                </p>

                @error('vehicle_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectedVehicleIds = @json($selectedVehicleIds ?? []);

    if (typeof TomSelect !== 'undefined') {
        const vehicleTom = new TomSelect('#vehicle_id', {
            create: false,
            allowEmptyOption: false,
            placeholder: 'Cari dan pilih no plat...',
            maxOptions: 5000,
            plugins: ['remove_button'],
            searchField: ['text'],
            sortField: {
                field: 'text',
                direction: 'asc'
            }
        });

        selectedVehicleIds.forEach(function (id) {
            if (vehicleTom.options[id]) {
                vehicleTom.addItem(id, true);
            }
        });

        vehicleTom.refreshItems();
    }
});
</script>
@endsection