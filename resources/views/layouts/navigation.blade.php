{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Left -->
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}"
                   class="text-lg font-semibold text-gray-800">
                    {{ config('app.name', 'Finance') }}
                </a>

                <!-- Navigation Links -->
                @php
                    $isDashboard = request()->routeIs('dashboard');
                @endphp

                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium
                          {{ $isDashboard ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                    Dashboard
                </a>
            </div>

            <!-- Right -->
            <div class="flex items-center gap-4 text-sm">
                @auth
                    <span class="text-gray-700">
                        {{ Auth::user()->name }}
                    </span>

                    <a href="{{ route('profile.edit') }}"
                       class="text-gray-600 hover:text-gray-900">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-gray-600 hover:text-gray-900">
                            Log Out
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>
