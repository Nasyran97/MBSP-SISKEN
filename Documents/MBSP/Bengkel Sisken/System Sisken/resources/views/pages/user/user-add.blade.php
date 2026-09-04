@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Sila pilih jabatan, nama staf, modul dan peranan bagi setiap modul.</p>
        </div>

        <a href="{{ route('user-list') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="mb-0 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('user-store') }}" method="POST">
            @csrf

            {{-- Step 1: Department --}}
            <div class="mb-4">
                <label for="department" class="mb-2 block text-sm font-medium text-gray-700">
                    Jabatan
                </label>
                <select name="department" id="department" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm">
                    <option value="">-- Sila Pilih Jabatan --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept['CODE'] ?? '' }}" {{ old('department') == ($dept['CODE'] ?? '') ? 'selected' : '' }}>
                            {{ $dept['DESCRIPTION'] ?? '' }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="jabatan" id="jabatan" value="{{ old('jabatan') }}">
            </div>

            {{-- Step 2: Staff --}}
            <div class="mb-4">
                <label for="nostaff" class="mb-2 block text-sm font-medium text-gray-700">
                    Nama Staf
                </label>
                <select name="nostaff" id="nostaff" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-50" disabled>
                    <option value="">-- Sila Pilih Jabatan Dahulu --</option>
                </select>
                <input type="hidden" name="nama" id="nama" value="{{ old('nama') }}">
            
                @error('nostaff')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Module + Role --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Modul & Role</label>
                    <button type="button" id="addRow" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        + Tambah Modul
                    </button>
                </div>

                <div id="moduleRoleWrapper">
                    @php
                        $oldModuleRole = old('module_role', [['module' => '', 'role' => '']]);
                    @endphp

                    @foreach($oldModuleRole as $index => $item)
                        <div class="module-role-row grid grid-cols-12 gap-3 mb-3">
                            <div class="col-span-5">
                                <select name="module_role[{{ $index }}][module]"
                                    class="module-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                                    {{ $index === 0 ? 'required' : '' }}>
                                    <option value="">-- Sila Pilih Modul --</option>
                                    @foreach($modules as $module)
                                        <option value="{{ strtoupper($module->name) }}"
                                            {{ strtoupper($item['module'] ?? '') === strtoupper($module->name) ? 'selected' : '' }}>
                                            {{ $module->name }}
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
                                </select>
                            </div>

                            <div class="col-span-2">
                                <button type="button" class="removeRow w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700">
                                    Padam
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('module_role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const namaInput = document.getElementById('nama');
        const departmentSelect = document.getElementById('department');
        const jabatanInput = document.getElementById('jabatan');
        const staffSelect = document.getElementById('nostaff');
        const wrapper = document.getElementById('moduleRoleWrapper');
        const addRowBtn = document.getElementById('addRow');
    
        const SUPERADMIN_MODULE = 'SUPERADMIN';
        const ADMIN_ROLE_NAME = 'ADMIN';
    
        let staffTom = null;
        let rowIndex = wrapper.querySelectorAll('.module-role-row').length;
    
        const oldNoStaff = @json(old('nostaff'));
    
        function initStaffSearch() {
            if (staffTom) {
                staffTom.destroy();
            }
    
            staffTom = new TomSelect('#nostaff', {
                create: false,
                allowEmptyOption: true,
                placeholder: 'Cari nama staf...',
                maxOptions: 5000,
                searchField: ['text'],
                sortField: {
                    field: 'text',
                    direction: 'asc'
                },
                onChange: function(value) {
                    const selectedOption = staffSelect.querySelector(`option[value="${value}"]`);
                    if (namaInput) {
                        namaInput.value = selectedOption ? (selectedOption.dataset.nama || '') : '';
                    }
                }
            });
        }
    
        function normalize(value) {
            return String(value || '').trim().toUpperCase();
        }
    
        function isSuperadminSelected() {
            let found = false;
    
            wrapper.querySelectorAll('.module-select').forEach(function (select) {
                if (normalize(select.value) === SUPERADMIN_MODULE) {
                    found = true;
                }
            });
    
            return found;
        }
    
        function removeHiddenRoleInput(roleSelect) {
            const row = roleSelect.closest('.module-role-row');
            const hidden = row.querySelector('.role-hidden-input');
    
            if (hidden) {
                hidden.remove();
            }
        }
    
        function syncHiddenRoleInput(roleSelect) {
            const row = roleSelect.closest('.module-role-row');
            let hidden = row.querySelector('.role-hidden-input');
    
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.className = 'role-hidden-input';
                hidden.name = roleSelect.name;
                row.appendChild(hidden);
            }
    
            hidden.value = roleSelect.value;
        }
    
        function disableRoleSelectButSubmit(roleSelect) {
            syncHiddenRoleInput(roleSelect);
    
            roleSelect.disabled = true;
            roleSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
        }
    
        function enableRoleSelect(roleSelect) {
            roleSelect.disabled = false;
            roleSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
    
            removeHiddenRoleInput(roleSelect);
        }
    
        function selectAdminRole(roleSelect) {
            const adminOption = Array.from(roleSelect.options).find(function (option) {
                return normalize(option.textContent) === ADMIN_ROLE_NAME;
            });
    
            if (adminOption) {
                roleSelect.value = adminOption.value;
                syncHiddenRoleInput(roleSelect);
            }
        }
    
        function removeOtherRowsExcept(currentRow) {
            wrapper.querySelectorAll('.module-role-row').forEach(function (row) {
                if (row !== currentRow) {
                    row.remove();
                }
            });
        }
    
        function setAddRowButtonDisabled(disabled) {
            addRowBtn.disabled = disabled;
            addRowBtn.classList.toggle('opacity-50', disabled);
            addRowBtn.classList.toggle('cursor-not-allowed', disabled);
        }
    
        function updateModuleOptions() {
            const moduleSelects = wrapper.querySelectorAll('.module-select');
            const selectedModules = [];
    
            moduleSelects.forEach(function (select) {
                if (select.value) {
                    selectedModules.push(normalize(select.value));
                }
            });
    
            const hasSuperadmin = selectedModules.includes(SUPERADMIN_MODULE);
    
            moduleSelects.forEach(function (select) {
                const currentValue = normalize(select.value);
    
                Array.from(select.options).forEach(function (option) {
                    if (!option.value) return;
    
                    const optionValue = normalize(option.value);
    
                    if (optionValue === currentValue) {
                        option.disabled = false;
                    } else {
                        option.disabled = selectedModules.includes(optionValue);
                    }
                });
            });
    
            if (hasSuperadmin) {
                setAddRowButtonDisabled(true);
                return;
            }
    
            const totalModuleOptions = moduleSelects.length
                ? Array.from(moduleSelects[0].options).filter(function (option) {
                    return option.value;
                }).length
                : 0;
    
            const uniqueSelectedCount = [...new Set(selectedModules)].length;
    
            setAddRowButtonDisabled(uniqueSelectedCount >= totalModuleOptions);
        }
    
        function applySuperadminMode(row) {
            const moduleSelect = row.querySelector('.module-select');
            const roleSelect = row.querySelector('.role-select');
    
            if (normalize(moduleSelect.value) === SUPERADMIN_MODULE) {
                removeOtherRowsExcept(row);
                selectAdminRole(roleSelect);
                disableRoleSelectButSubmit(roleSelect);
                setAddRowButtonDisabled(true);
            } else {
                enableRoleSelect(roleSelect);
                updateModuleOptions();
            }
        }
    
        function loadRoles(moduleName, roleSelect, selectedRole = '', forceAdmin = false) {
            roleSelect.innerHTML = '<option value="">-- Loading Role --</option>';
    
            if (!moduleName) {
                roleSelect.innerHTML = '<option value="">-- Sila Pilih Role --</option>';
                enableRoleSelect(roleSelect);
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
                        option.value = role.id;
                        option.textContent = role.name;
    
                        if (String(role.id) === String(selectedRole)) {
                            option.selected = true;
                        }
    
                        roleSelect.appendChild(option);
                    });
                }
    
                if (normalize(moduleName) === SUPERADMIN_MODULE || forceAdmin) {
                    selectAdminRole(roleSelect);
                    disableRoleSelectButSubmit(roleSelect);
                } else {
                    enableRoleSelect(roleSelect);
                }
    
                updateModuleOptions();
            })
            .catch(() => {
                roleSelect.innerHTML = '<option value="">-- Ralat Ambil Role --</option>';
                enableRoleSelect(roleSelect);
            });
        }
    
        function bindModuleChange(row) {
            const moduleSelect = row.querySelector('.module-select');
            const roleSelect = row.querySelector('.role-select');
    
            moduleSelect.addEventListener('change', function () {
                const selectedModule = normalize(this.value);
    
                roleSelect.innerHTML = '<option value="">-- Loading Role --</option>';
    
                if (selectedModule === SUPERADMIN_MODULE) {
                    removeOtherRowsExcept(row);
                    loadRoles(this.value, roleSelect, '', true);
                    setAddRowButtonDisabled(true);
                } else {
                    enableRoleSelect(roleSelect);
                    loadRoles(this.value, roleSelect);
                    updateModuleOptions();
                }
            });
    
            roleSelect.addEventListener('change', function () {
                if (roleSelect.disabled) {
                    syncHiddenRoleInput(roleSelect);
                }
            });
        }
    
        function loadStaffByDepartment(departmentCode, selectedNoStaff = '') {
            if (staffTom) {
                staffTom.destroy();
                staffTom = null;
            }
    
            staffSelect.innerHTML = '<option value="">-- Loading Nama Staf --</option>';
            staffSelect.disabled = true;
    
            if (!departmentCode) {
                staffSelect.innerHTML = '<option value="">-- Sila Pilih Jabatan Dahulu --</option>';
                staffSelect.disabled = true;
                return;
            }
    
            fetch("{{ route('get-staff-by-department') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    department_code: departmentCode
                })
            })
            .then(response => response.json())
            .then(data => {
                staffSelect.innerHTML = '<option value="">-- Sila Pilih Nama Staf --</option>';
    
                if (data.success && Array.isArray(data.staffs) && data.staffs.length > 0) {
                    data.staffs.forEach(staff => {
                        const noStaff = staff.NOSTAFF ?? staff.nostaff ?? '';
                        const namaStaff = staff.NAMASTAFF ?? staff.namastaff ?? '';
    
                        const option = document.createElement('option');
                        option.value = noStaff;
                        option.textContent = noStaff + ' - ' + namaStaff;
                        option.dataset.nama = namaStaff;
    
                        if (String(noStaff) === String(selectedNoStaff)) {
                            option.selected = true;
                        }
    
                        staffSelect.appendChild(option);
                    });
    
                    staffSelect.disabled = false;
    
                    initStaffSearch();
    
                    const selectedOption = staffSelect.options[staffSelect.selectedIndex];
                    if (selectedOption) {
                        namaInput.value = selectedOption.dataset.nama || '';
                    }
                } else {
                    staffSelect.innerHTML = '<option value="">-- Tiada Staf Dijumpai --</option>';
                    staffSelect.disabled = true;
                    namaInput.value = '';
                }
            })
            .catch(() => {
                staffSelect.innerHTML = '<option value="">-- Ralat Semasa Ambil Data Staf --</option>';
                staffSelect.disabled = true;
            });
        }
    
        staffSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            namaInput.value = selectedOption ? (selectedOption.dataset.nama || '') : '';
        });
    
        document.querySelectorAll('.module-role-row').forEach(function (row) {
            bindModuleChange(row);
    
            const moduleSelect = row.querySelector('.module-select');
            const roleSelect = row.querySelector('.role-select');
            const selectedRole = roleSelect.dataset.selectedRole || '';
    
            if (moduleSelect.value) {
                const isSuperadmin = normalize(moduleSelect.value) === SUPERADMIN_MODULE;
                loadRoles(moduleSelect.value, roleSelect, selectedRole, isSuperadmin);
    
                if (isSuperadmin) {
                    removeOtherRowsExcept(row);
                    setAddRowButtonDisabled(true);
                }
            }
        });
    
        updateModuleOptions();
    
        if (departmentSelect.value) {
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
    
            if (jabatanInput && selectedOption) {
                jabatanInput.value = selectedOption.text || '';
            }
    
            loadStaffByDepartment(departmentSelect.value, oldNoStaff);
        }
    
        departmentSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const departmentCode = this.value;
            const departmentName = selectedOption ? selectedOption.text : '';
    
            if (jabatanInput) {
                jabatanInput.value = departmentName && departmentCode ? departmentName : '';
            }
    
            loadStaffByDepartment(this.value);
        });
    
        addRowBtn.addEventListener('click', function () {
            if (addRowBtn.disabled || isSuperadminSelected()) {
                return;
            }
    
            const row = document.createElement('div');
            row.className = 'module-role-row grid grid-cols-12 gap-3 mb-3';
    
            row.innerHTML = `
                <div class="col-span-5">
                    <select name="module_role[${rowIndex}][module]" class="module-select w-full rounded-lg border border-gray-300 px-4 py-3 text-sm">
                        <option value="">-- Sila Pilih Modul --</option>
                        @foreach($modules as $module)
                            @php
                                $moduleName = $module->NAME ?? $module->name ?? '';
                            @endphp
                            <option value="{{ strtoupper($moduleName) }}">{{ strtoupper($moduleName) }}</option>
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