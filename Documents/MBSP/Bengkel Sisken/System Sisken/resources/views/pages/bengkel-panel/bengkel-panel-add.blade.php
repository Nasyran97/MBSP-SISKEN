@extends('layouts.app')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700';
    $sectionClass = 'mb-3 border-b border-gray-200 pb-2 text-sm font-semibold uppercase tracking-wide text-gray-500';
    $errClass = 'mt-1 text-xs text-red-600';
@endphp
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title ?? 'Tambah Bengkel Panel' }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Sila masukkan maklumat bengkel panel baharu. Ruangan bertanda <span class="text-red-500">*</span> wajib diisi.
            </p>
        </div>

        <a href="{{ route('bengkel-panel-list') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
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

    <x-common.component-card title="Maklumat Bengkel Panel">
        <form method="POST" action="{{ route('bengkel-panel-store') }}" class="space-y-8">
            @csrf

            {{-- Maklumat Asas --}}
            <section>
                <h4 class="{{ $sectionClass }}">Maklumat Asas</h4>
                <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-12">
                    <div class="md:col-span-8">
                        <label for="nama_panel" class="{{ $labelClass }}">Nama Bengkel <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_panel" id="nama_panel" value="{{ old('nama_panel') }}"
                               class="{{ $inputClass }}" placeholder="Nama penuh bengkel" required>
                        @error('nama_panel')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-4">
                        <label for="kod_panel" class="{{ $labelClass }}">Kod Panel</label>
                        <input type="text" id="kod_panel" value="" readonly tabindex="-1"
                               class="{{ $inputClass }} cursor-not-allowed !bg-gray-100 font-semibold"
                               placeholder="Dijana automatik">
                        <p class="mt-1 text-xs text-gray-500">Dijana automatik berdasarkan huruf pertama nama bengkel.</p>
                    </div>

                    <div class="md:col-span-6">
                        <label for="nama_ringkas" class="{{ $labelClass }}">Nama Ringkas</label>
                        <input type="text" name="nama_ringkas" id="nama_ringkas" value="{{ old('nama_ringkas') }}"
                               class="{{ $inputClass }}">
                        @error('nama_ringkas')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-6">
                        <label for="kategori" class="{{ $labelClass }}">Kategori</label>
                        <select name="kategori" id="kategori" class="{{ $inputClass }}">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach(['BUMIPUTERA', 'BUKAN BUMIPUTERA'] as $opt)
                                <option value="{{ $opt }}" {{ old('kategori') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('kategori')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-12">
                        <label for="servis" class="{{ $labelClass }}">Servis</label>
                        <input type="text" name="servis" id="servis" value="{{ old('servis') }}"
                               class="{{ $inputClass }}">
                        @error('servis')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Alamat --}}
            <section>
                <h4 class="{{ $sectionClass }}">Alamat</h4>
                <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-12">
                    <div class="md:col-span-12">
                        <label for="alamat" class="{{ $labelClass }}">Alamat</label>
                        <textarea name="alamat" id="alamat" rows="2" class="{{ $inputClass }}"
                                  placeholder="No. lot, jalan, taman">{{ old('alamat') }}</textarea>
                        @error('alamat')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="poskod" class="{{ $labelClass }}">Poskod</label>
                        <input type="text" name="poskod" id="poskod" value="{{ old('poskod') }}"
                               class="{{ $inputClass }}" inputmode="numeric" maxlength="10">
                        @error('poskod')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="bandar" class="{{ $labelClass }}">Bandar</label>
                        <input type="text" name="bandar" id="bandar" value="{{ old('bandar') }}"
                               class="{{ $inputClass }}">
                        @error('bandar')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="daerah" class="{{ $labelClass }}">Daerah</label>
                        <input type="text" name="daerah" id="daerah" value="{{ old('daerah') }}"
                               class="{{ $inputClass }}">
                        @error('daerah')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="negeri" class="{{ $labelClass }}">Negeri</label>
                        <input type="text" name="negeri" id="negeri" value="{{ old('negeri') }}"
                               class="{{ $inputClass }}">
                        @error('negeri')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Hubungan --}}
            <section>
                <h4 class="{{ $sectionClass }}">Maklumat Perhubungan</h4>
                <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-12">
                    <div class="md:col-span-4">
                        <label for="no_tel" class="{{ $labelClass }}">No. Telefon</label>
                        <input type="text" name="no_tel" id="no_tel" value="{{ old('no_tel') }}"
                               class="{{ $inputClass }}" placeholder="04-1234567">
                        @error('no_tel')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-4">
                        <label for="no_fax" class="{{ $labelClass }}">No. Fax</label>
                        <input type="text" name="no_fax" id="no_fax" value="{{ old('no_fax') }}"
                               class="{{ $inputClass }}">
                        @error('no_fax')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-4">
                        <label for="email" class="{{ $labelClass }}">Emel</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="{{ $inputClass }}" placeholder="nama@contoh.com">
                        @error('email')<p class="{{ $errClass }}">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <div class="flex justify-end gap-2 border-t border-gray-200 pt-5">
                <a href="{{ route('bengkel-panel-list') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

@push('scripts')
<script>
$(function () {
    const $nama = $('#nama_panel');
    const $kod = $('#kod_panel');
    let timer = null;
    let lastLetter = null;

    function refreshKod() {
        const nama = $nama.val().trim();
        const letter = nama.charAt(0).toUpperCase();

        if (!letter) { lastLetter = null; $kod.val(''); return; }
        if (letter === lastLetter) return;
        lastLetter = letter;

        $.getJSON(@json(route('bengkel-panel-next-kod')), { nama: nama })
            .done(function (res) { $kod.val(res.kod || ''); })
            .fail(function () { $kod.val(''); });
    }

    $nama.on('input', function () {
        clearTimeout(timer);
        timer = setTimeout(refreshKod, 250);
    });

    refreshKod();
});
</script>
@endpush

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Tidak Berjaya',
        text: @json(session('error')),
        confirmButtonText: 'OK'
    });
});
</script>
@endif

@endsection
