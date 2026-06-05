<nav x-data="{ open: false }" class="bg-white shadow-sm border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Sidebar Toggle for Mobile -->
                <button
                    class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Settings Dropdown -->
            <div class="flex items-center">
                @if(auth()->check() && auth()->user()->role === 'mechanic')
                    @php
                        $notifications = \App\Models\MwsPart::whereHas('steps', function ($query) {
                            $query->whereJsonContains('man', auth()->user()->nik);
                        })
                        ->orderBy('updated_at', 'desc')
                        ->limit(5)
                        ->get();
                    @endphp
                    <div class="me-4 relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                        <button @click="open = ! open" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                            <i class="fas fa-bell text-xl"></i>
                            @if($notifications->count() > 0)
                                <span class="absolute top-1 right-1 inline-block w-2.5 h-2.5 bg-red-600 rounded-full border-2 border-white"></span>
                            @endif
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
                             style="display: none;">
                            <div class="px-4 py-2 border-b border-gray-100 font-semibold text-gray-800 text-sm">
                                Notifikasi Penugasan MWS
                            </div>
                            @if($notifications->count() > 0)
                                @foreach($notifications as $notif)
                                    <a href="{{ route('mws.show', $notif->id) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-50 last:border-0">
                                        <div class="font-medium text-blue-600">{{ $notif->part_id }}</div>
                                        <div class="text-xs text-gray-600 truncate text-start">{{ $notif->title }}</div>
                                        <div class="text-[10px] text-gray-400 mt-1 text-start">Ditugaskan pada step kerja MWS ini</div>
                                    </a>
                                @endforeach
                            @else
                                <div class="px-4 py-4 text-center text-xs text-gray-500">
                                    Tidak ada penugasan baru.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center">
                                <i class="fas fa-user-circle me-2 text-lg"></i>
                                <span>{{ auth()->user()->name ?? 'Guest' }}</span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fas fa-user me-2"></i>{{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>{{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
