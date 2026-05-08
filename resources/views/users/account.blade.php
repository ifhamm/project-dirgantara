@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="p-6">
            {{-- Header --}}
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-users-cog text-blue-600"></i>
                        Kelola Akun Pengguna
                    </h2>
                    <p class="text-sm text-gray-600">Daftar akun yang terdaftar dalam sistem.</p>
                </div>
                <button onclick="openModal('add')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition shadow-md">
                    <i class="fas fa-plus"></i> Tambah Akun
                </button>
            </div>

            {{-- Filter & Search Section --}}
            <div class="bg-white p-4 rounded-xl shadow-sm mb-6 border border-gray-100">
                <form action="{{ route('users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="Cari akun pengguna..." autocomplete="off">

                            @if (request('search'))
                                <a href="{{ route('users.index', request()->except('search')) }}"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-red-500 hover:text-red-700 flex items-center justify-center no-underline"
                                    title="Hapus pencarian">
                                    <i class="fas fa-times-circle text-base"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <select name="role" onchange="this.form.submit()"
                            class="block w-full py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Semua Role</option>
                            <option value="mechanic" {{ request('role') == 'mechanic' ? 'selected' : '' }}>Mechanic</option>
                            <option value="quality inspector"
                                {{ request('role') == 'quality inspector' ? 'selected' : '' }}>Quality Inspector</option>
                            <option value="quality cvdr" {{ request('role') == 'quality cvdr' ? 'selected' : '' }}>Quality
                                CVDR</option>
                        </select>
                    </div>
                    @if (request('role'))
                        <a href="{{ route('users.index', request()->except('role')) }}"
                            class="text-red-500 hover:text-red-700 flex items-center justify-center no-underline"
                            title="Hapus filter role">
                            <i class="fas fa-filter-circle-xmark text-lg"></i>
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table Section --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-500 border-collapse">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-center">
                                    @php
                                        $currentSortBy = request('sort_by');
                                        $currentSortOrder = request('sort_order');
                                        $isActive = $currentSortBy == 'nik';

                                        if ($isActive && $currentSortOrder == 'asc') {
                                            $nextSortBy = 'nik';
                                            $nextSortOrder = 'desc';
                                        } elseif ($isActive && $currentSortOrder == 'desc') {
                                            $nextSortBy = null;
                                            $nextSortOrder = null;
                                        } else {
                                            $nextSortBy = 'nik';
                                            $nextSortOrder = 'asc';
                                        }

                                        $queryParams = request()->except(['sort_by', 'sort_order', 'page']);
                                        if ($nextSortBy) {
                                            $queryParams['sort_by'] = $nextSortBy;
                                            $queryParams['sort_order'] = $nextSortOrder;
                                        }
                                    @endphp
                                    <a href="{{ route('users.index', $queryParams) }}"
                                        class="flex items-center gap-1 hover:text-blue-600 {{ $isActive ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        NIK
                                        @if ($isActive && $currentSortOrder == 'asc')
                                            <i class="fas fa-sort-up text-xs"></i>
                                        @elseif($isActive && $currentSortOrder == 'desc')
                                            <i class="fas fa-sort-down text-xs"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300 text-xs"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    @php
                                        $currentSortBy = request('sort_by');
                                        $currentSortOrder = request('sort_order');
                                        $isActive = $currentSortBy == 'name';

                                        if ($isActive && $currentSortOrder == 'asc') {
                                            $nextSortBy = 'name';
                                            $nextSortOrder = 'desc';
                                        } elseif ($isActive && $currentSortOrder == 'desc') {
                                            $nextSortBy = null;
                                            $nextSortOrder = null;
                                        } else {
                                            $nextSortBy = 'name';
                                            $nextSortOrder = 'asc';
                                        }

                                        $queryParams = request()->except(['sort_by', 'sort_order', 'page']);
                                        if ($nextSortBy) {
                                            $queryParams['sort_by'] = $nextSortBy;
                                            $queryParams['sort_order'] = $nextSortOrder;
                                        }
                                    @endphp
                                    <a href="{{ route('users.index', $queryParams) }}"
                                        class="flex items-center gap-1 hover:text-blue-600 {{ $isActive ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        Nama
                                        @if ($isActive && $currentSortOrder == 'asc')
                                            <i class="fas fa-sort-up text-xs"></i>
                                        @elseif($isActive && $currentSortOrder == 'desc')
                                            <i class="fas fa-sort-down text-xs"></i>
                                        @else
                                            <i class="fas fa-sort text-gray-300 text-xs"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-4 text-left">Email</th>
                                <th class="px-6 py-4 text-center">Role</th>
                                @can('is-superadmin')
                                <th class="px-6 py-4 text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition border-b">
                                    <td class="px-6 py-4 text-sm text-gray-900 text-left">{{ $user->nik }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-left">{{ $user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-left">{{ $user->email ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-semibold inline-block min-w-[100px] text-center
                                            {{ match ($user->role) {
                                                'admin' => 'bg-green-100 text-green-700',
                                                'mechanic' => 'bg-orange-100 text-orange-700',
                                                'quality2' => 'bg-blue-100 text-blue-700',
                                                'quality1' => 'bg-teal-100 text-teal-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            } }}">

                                            {{ match ($user->role) {
                                                'admin' => 'PPC',
                                                'mechanic' => 'Mechanic',
                                                'quality2' => 'Quality Inspector',
                                                'quality1' => 'Quality CVDR',
                                                default => ucfirst($user->role),
                                            } }}
                                        </span>
                                    </td>
                                    @can('is-superadmin')
                                    <td class="px-6 py-4 text-center space-x-2">
                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                            onclick="editUser('{{ $user->id }}', '{{ $user->nik }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}')"
                                            class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {{-- Tombol Hapus --}}
                                        <button type="button"
                                            onclick="openDeleteModal('{{ $user->id }}', '{{ $user->name }}')"
                                            class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            id="delete-form-{{ $user->id }}" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">Data tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    <div id="userModal" class="fixed inset-0 z-[2000] hidden overflow-y-auto bg-black bg-opacity-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden relative" id="modalContent">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                    <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Tambah Pengguna</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i
                            class="fas fa-times text-xl"></i></button>
                </div>
                <form id="userForm" method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4" novalidate>
                    @csrf
                    <input type="hidden" id="methodField" name="_method" value="POST">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIK</label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}"
                            class="w-full rounded-lg border-gray-300 text-sm" maxlength="16">
                        @error('nik')
                            <p class="blade-error text-red-500 text-[13px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-lg border-gray-300 text-sm @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="blade-error text-red-500 text-[13px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role<span
                                class="text-red-500">*</span></label>
                        <select id="role" name="role" required required onchange="toggleAdminFields()"
                            class="w-full rounded-lg border-gray-300 text-sm @error('role') border-red-500 @enderror">
                            <option value="" id="placeholderRole" disabled selected>Pilih Role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : 'PPC' }}>PPC</option>
                            <option value="mechanic" {{ old('role') == 'mechanic' ? 'selected' : '' }}>Mechanic</option>
                            <option value="quality2" {{ old('role') == 'quality2' ? 'selected' : 'Quality Inspector' }}>
                                Quality Inspector</option>
                            <option value="quality1" {{ old('role') == 'quality1' ? 'selected' : 'Quality CVDR' }}>
                                Quality CVDR</option>
                        </select>
                        @error('role')
                            <p class="blade-error text-red-500 text-[13px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field Khusus Admin (Hidden by Default) --}}
                    <div id="adminFields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email<span
                                    class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="w-full rounded-lg border-gray-300 text-sm">
                            @error('email')
                                <p class="text-red-500 text-[13px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="password" name="password"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Isi jika ingin set password">
                            @error('password')
                                <p class="text-red-500 text-[13px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-md">Simpan</button>
                    </div>
                </form>
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <strong class="font-bold">Oops, ada yang salah!</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const userModal = document.getElementById('userModal');
        let searchTimer;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    this.closest('form').submit();
                }, 700);
            });
        }

        function openModal(mode) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');
            const placeholderRole = document.getElementById('placeholderRole');

            modal.classList.remove('hidden');

            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'Tambah Pengguna';
                form.action = "{{ route('users.store') }}";
                document.getElementById('methodField').value = 'POST';
                document.getElementById('passwordHint').classList.add('hidden');

                userForm.reset();
                clearErrorVisuals();

                document.getElementById('nik').value = '';
                document.getElementById('name').value = '';
                document.getElementById('email').value = '';
                document.getElementById('passwordInput').value = '';
                document.getElementById('passwordConfirmationInput').value = '';

                if (placeholderRole) {
                    placeholderRole.selected = true;
                }
            }
        }

        function editUser(id, nik, name, email, role) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');

            modal.classList.remove('hidden');
            document.getElementById('modalTitle').innerText = 'Edit Pengguna';

            form.action = "/users/" + id;
            document.getElementById('methodField').value = 'PUT';

            if (typeof clearErrorVisuals === "function") {
                clearErrorVisuals();
            }

            document.getElementById('nik').value = (nik && nik !== 'null') ? nik : '';
            document.getElementById('name').value = name;
            document.getElementById('email').value = (email && email !== 'null') ? email : '';
            document.getElementById('role').value = role;

            document.getElementById('password').value = '';

            toggleAdminFields();
        }

        function clearErrorVisuals() {
            document.querySelectorAll('.error-msg').forEach(el => el.remove());
            document.querySelectorAll('.blade-error').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
        }

        function toggleAdminFields() {
            const role = document.getElementById('role').value;
            const adminFields = document.getElementById('adminFields');

            if (role === 'admin') {
                adminFields.classList.remove('hidden');
            } else {
                adminFields.classList.add('hidden');
            }
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
            clearErrorVisuals();
        }

        function openDeleteModal(id, name) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus akun ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        @if ($errors->any())
            userModal.classList.remove('hidden');

            if ("{{ old('_method') }}" === "PUT") {
                document.getElementById('modalTitle').innerText = 'Edit Pengguna';
                document.getElementById('passwordHint').classList.remove('hidden');
            }
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    showConfirmButton: true
                });
            @endif
        });
    </script>
@endsection
