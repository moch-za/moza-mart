<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <div id="brandIcon" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-200 mb-3 transition-colors duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Moza-Mart</h2>
        <p class="text-xs text-gray-500 mt-0.5">Sistem Manajemen Inventaris & Penjualan</p>
    </div>

    {{-- 3 Role Tabs: Admin, Owner, Staff --}}
    <div class="mb-5 p-1 bg-gray-100 rounded-xl grid grid-cols-3 gap-1" id="roleSelector">
        {{-- Tab Admin --}}
        <button type="button" id="tabAdmin" onclick="switchLoginMode('admin')"
            class="py-2 px-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 bg-white text-indigo-700 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Admin
        </button>

        {{-- Tab Owner --}}
        <button type="button" id="tabOwner" onclick="switchLoginMode('owner')"
            class="py-2 px-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 text-gray-500 hover:text-gray-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Owner
        </button>

        {{-- Tab Staff --}}
        <button type="button" id="tabStaff" onclick="switchLoginMode('staff')"
            class="py-2 px-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 text-gray-500 hover:text-gray-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Staff
        </button>
    </div>

    {{-- Banner Role Info --}}
    <div id="roleBanner" class="mb-5 p-3.5 rounded-xl border transition-all duration-200 bg-indigo-50 border-indigo-200 text-indigo-950">
        <div class="flex items-start gap-3">
            <div id="roleIconContainer" class="p-1.5 rounded-lg bg-indigo-100 text-indigo-600 mt-0.5">
                <svg id="roleIconSvg" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p id="roleTitle" class="text-xs font-bold uppercase tracking-wider text-indigo-900">Portal Login: Administrator</p>
                <p id="roleDesc" class="text-xs text-indigo-700 mt-0.5">Akses operasional manajer: kelola master produk, supplier, persetujuan PO, dan pembatalan transaksi.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Email / Username</label>
            <input id="email" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                type="text" name="email" value="{{ old('email') }}" placeholder="Masukkan email / username..." required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Password</label>
            <input id="password" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                type="password" name="password" placeholder="Masukkan password..." required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember_me" class="inline-flex items-center text-gray-600 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" name="remember">
                <span class="ms-2">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-indigo-600 hover:text-indigo-800 font-medium" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" id="submitBtn"
            class="w-full py-2.5 px-4 rounded-xl text-white font-medium text-sm transition-all shadow-sm bg-indigo-600 hover:bg-indigo-700">
            Masuk sebagai Admin
        </button>
    </form>

    <script>
        const activeTabClass   = "py-2 px-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 bg-white shadow-sm";
        const inactiveTabClass = "py-2 px-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 text-gray-500 hover:text-gray-800";

        function switchLoginMode(mode) {
            const tabAdmin          = document.getElementById('tabAdmin');
            const tabOwner          = document.getElementById('tabOwner');
            const tabStaff          = document.getElementById('tabStaff');
            const brandIcon         = document.getElementById('brandIcon');
            const roleBanner        = document.getElementById('roleBanner');
            const roleIconContainer = document.getElementById('roleIconContainer');
            const roleTitle         = document.getElementById('roleTitle');
            const roleDesc          = document.getElementById('roleDesc');
            const submitBtn         = document.getElementById('submitBtn');

            // Reset tab styles
            tabAdmin.className = inactiveTabClass;
            tabOwner.className = inactiveTabClass;
            tabStaff.className = inactiveTabClass;

            if (mode === 'admin') {
                tabAdmin.className = activeTabClass + " text-indigo-700";
                brandIcon.className = "inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-200 mb-3 transition-colors duration-200";

                roleBanner.className = "mb-5 p-3.5 rounded-xl border transition-all duration-200 bg-indigo-50 border-indigo-200 text-indigo-950";
                roleIconContainer.className = "p-1.5 rounded-lg bg-indigo-100 text-indigo-600 mt-0.5";
                roleTitle.className = "text-xs font-bold uppercase tracking-wider text-indigo-900";
                roleTitle.textContent = "Portal Login: Administrator";
                roleDesc.className = "text-xs text-indigo-700 mt-0.5";
                roleDesc.textContent = "Akses operasional manajer: kelola master produk, supplier, persetujuan PO, dan pembatalan transaksi.";

                submitBtn.className = "w-full py-2.5 px-4 rounded-xl text-white font-medium text-sm transition-all shadow-sm bg-indigo-600 hover:bg-indigo-700";
                submitBtn.textContent = "Masuk sebagai Admin";
            } else if (mode === 'owner') {
                tabOwner.className = activeTabClass + " text-purple-700";
                brandIcon.className = "inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-purple-600 text-white shadow-md shadow-purple-200 mb-3 transition-colors duration-200";

                roleBanner.className = "mb-5 p-3.5 rounded-xl border transition-all duration-200 bg-purple-50 border-purple-200 text-purple-950";
                roleIconContainer.className = "p-1.5 rounded-lg bg-purple-100 text-purple-600 mt-0.5";
                roleTitle.className = "text-xs font-bold uppercase tracking-wider text-purple-900";
                roleTitle.textContent = "Portal Login: Owner (Pemilik Toko)";
                roleDesc.className = "text-xs text-purple-700 mt-0.5";
                roleDesc.textContent = "Akses pemilik bisnis: pemantauan omset & laba penjualan, audit seluruh inventaris, dan hak kontrol penuh.";

                submitBtn.className = "w-full py-2.5 px-4 rounded-xl text-white font-medium text-sm transition-all shadow-sm bg-purple-600 hover:bg-purple-700";
                submitBtn.textContent = "Masuk sebagai Owner";
            } else {
                tabStaff.className = activeTabClass + " text-emerald-700";
                brandIcon.className = "inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-200 mb-3 transition-colors duration-200";

                roleBanner.className = "mb-5 p-3.5 rounded-xl border transition-all duration-200 bg-emerald-50 border-emerald-200 text-emerald-950";
                roleIconContainer.className = "p-1.5 rounded-lg bg-emerald-100 text-emerald-600 mt-0.5";
                roleTitle.className = "text-xs font-bold uppercase tracking-wider text-emerald-900";
                roleTitle.textContent = "Portal Login: Staff & Kasir";
                roleDesc.className = "text-xs text-emerald-700 mt-0.5";
                roleDesc.textContent = "Akses operasional staf: pencatatan kasir penjualan produk harian dan permohonan pembelian stok.";

                submitBtn.className = "w-full py-2.5 px-4 rounded-xl text-white font-medium text-sm transition-all shadow-sm bg-emerald-600 hover:bg-emerald-700";
                submitBtn.textContent = "Masuk sebagai Staff";
            }
        }

        // Cek jika ada query param role di URL (misal: /login?role=owner atau /login?role=staff)
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const roleParam = urlParams.get('role');
            if (roleParam && ['admin', 'owner', 'staff'].includes(roleParam)) {
                switchLoginMode(roleParam);
            }
        });
    </script>
</x-guest-layout>
