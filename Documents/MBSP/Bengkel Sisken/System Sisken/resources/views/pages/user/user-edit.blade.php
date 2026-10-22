@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Kemaskini modul dan peranan pengguna.</p>
        </div>

        <a href="{{ route('user-list') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            Kembali
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="mb-0 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('user-update', $user->ID ?? $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">No Staff</label>
                <input type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                    value="{{ $user->NOSTAFF ?? $user->nostaff }}" readonly>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                    value="{{ $user->NAMA ?? $user->nama }}" readonly>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Modul & Role</label>
                    <button type="button" id="addRow" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        + Tambah
                    </button>
                </div>

                <div id="moduleRoleWrapper">
                    @forelse($moduleRoles as $index => $item)
                        <div class="module-role-row grid grid-cols-12 gap-3 mb-3">
                            <div class="col-span-5">
                                <select name="module_role[{{ $index }}][module]"
                                    class="module-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                                    {{ $index === 0 ? 'required' : '' }}>
                
                                    <option value="">-- Sila Pilih Modul --</option>
                
                                    @foreach($modules as $module)
                                        @php
                                            $moduleName = strtoupper(trim($module->NAME ?? $module->name ?? ''));
                                            $selectedModule = strtoupper(trim($item['module'] ?? ''));
                                        @endphp
                
                                        <option value="{{ $moduleName }}"
                                            {{ $moduleName === $selectedModule ? 'selected' : '' }}>
                                            {{ $module->NAME ?? $module->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                
                            <div class="col-span-5">
                                <select name="module_role[{{ $index }}][role]"
                                    class="role-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                                    data-selected-role="{{ $item['role'] ?? '' }}"
                                    {{ $index === 0 ? 'required' : '' }}>
                
                                    <option value="">-- Sila Pilih Role --</option>
                
                                    @foreach($item['roles_list'] ?? [] as $role)
                                        @php
                                            $roleId = (string) ($role->ID ?? $role->id ?? '');
                                            $roleName = $role->NAME ?? $role->name ?? '';
                                            $selectedRole = (string) ($item['role'] ?? '');
                                        @endphp
                
                                        <option value="{{ $roleId }}"
                                            {{ $roleId === $selectedRole ? 'selected' : '' }}>
                                            {{ $roleName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                
                            <div class="col-span-2">
                                <button type="button"
                                    class="removeRow w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700">
                                    Padam
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="module-role-row grid grid-cols-12 gap-3 mb-3">
                            <div class="col-span-5">
                                <select name="module_role[0][module]"
                                    class="module-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                                    required>
                
                                    <option value="">-- Sila Pilih Modul --</option>
                
                                    @foreach($modules as $module)
                                        @php
                                            $moduleName = strtoupper(trim($module->NAME ?? $module->name ?? ''));
                                        @endphp
                
                                        <option value="{{ $moduleName }}">
                                            {{ $module->NAME ?? $module->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                
                            <div class="col-span-5">
                                <select name="module_role[0][role]"
                                    class="role-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                                    data-selected-role=""
                                    required>
                
                                    <option value="">-- Sila Pilih Role --</option>
                                </select>
                            </div>
                
                            <div class="col-span-2">
                                <button type="button"
                                    class="removeRow w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700">
                                    Padam
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

            <div class="mt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('moduleRoleWrapper');
        const addRowBtn = document.getElementById('addRow');
        let rowIndex = {{ count($moduleRoles) > 0 ? count($moduleRoles) : 1 }};
    
        function updateModuleOptions() {
            const moduleSelects = wrapper.querySelectorAll('.module-select');
            const selectedModules = [];
    
            moduleSelects.forEach(select => {
                if (select.value) {
                    selectedModules.push(select.value);
                }
            });
    
            moduleSelects.forEach(select => {
                const currentValue = select.value;
    
                Array.from(select.options).forEach(option => {
                    if (!option.value) return;
    
                    if (option.value === currentValue) {
                        option.disabled = false;
                    } else {
                        option.disabled = false;
                    }
                });
            });
    
            addRowBtn.disabled = false;
            addRowBtn.classList.toggle('opacity-50', addRowBtn.disabled);
            addRowBtn.classList.toggle('cursor-not-allowed', addRowBtn.disabled);
        }
    
        function loadRoles(moduleName, roleSelect, selectedRole = '') {
            roleSelect.innerHTML = '<option value="">-- Loading Role --</option>';

            if (!moduleName) {
                roleSelect.innerHTML = '<option value="">-- Sila Pilih Role --</option>';
                return;
            }

            fetch("{{ route('get-roles-by-module') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    module_name: moduleName
                })
            })
            .then(response => response.json())
            .then(data => {
                roleSelect.innerHTML = '<option value="">-- Sila Pilih Role --</option>';

                if (data.success && Array.isArray(data.roles)) {
                    data.roles.forEach(role => {
                        const option = document.createElement('option');
                        option.value = role.id ?? role.ID;
                        option.textContent = role.name ?? role.NAME;

                        if (String(option.value) === String(selectedRole)) {
                            option.selected = true;
                        }

                        roleSelect.appendChild(option);
                    });
                }
            })
            .catch(() => {
                roleSelect.innerHTML = '<option value="">-- Ralat Ambil Role --</option>';
            });
        }
    
        function bindModuleChange(row) {
            const moduleSelect = row.querySelector('.module-select');
            const roleSelect = row.querySelector('.role-select');
    
            moduleSelect.addEventListener('change', function () {
                loadRoles(this.value, roleSelect);
                updateModuleOptions();
            });
        }
    
        document.querySelectorAll('.module-role-row').forEach(row => {
            bindModuleChange(row);

            const moduleSelect = row.querySelector('.module-select');
            const roleSelect = row.querySelector('.role-select');

            if (moduleSelect && roleSelect && moduleSelect.value) {
                const selectedRole = roleSelect.dataset.selectedRole || roleSelect.value || '';
                loadRoles(moduleSelect.value, roleSelect, selectedRole);
            }
        });
    
        updateModuleOptions();
    
        addRowBtn.addEventListener('click', function () {
            if (addRowBtn.disabled) return;
    
            const row = document.createElement('div');
            row.className = 'module-role-row grid grid-cols-12 gap-3 mb-3';
    
            row.innerHTML = `
                <div class="col-span-5">
                    <select name="module_role[${rowIndex}][module]" class="module-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm">
                        <option value="">-- Sila Pilih Modul --</option>
                        @foreach($modules as $module)
                            <option value="{{ strtoupper($module->name) }}">{{ $module->name }}</option>
                        @endforeach
                    </select>
                </div>
    
                <div class="col-span-5">
                    <select name="module_role[${rowIndex}][role]" class="role-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm">
                        <option value="">-- Sila Pilih Role --</option>
                    </select>
                </div>
    
                <div class="col-span-2">
                    <button type="button" class="removeRow w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700">
                        Padam
                    </button>
                </div>
            `;
    
            wrapper.appendChild(row);
            bindModuleChange(row);
            updateModuleOptions();
            rowIndex++;
        });
    
        wrapper.addEventListener('click', function (e) {
            if (e.target.classList.contains('removeRow')) {
                const rows = wrapper.querySelectorAll('.module-role-row');
                if (rows.length > 1) {
                    e.target.closest('.module-role-row').remove();
                    updateModuleOptions();
                }
            }
        });
    });
</script>
@endsection