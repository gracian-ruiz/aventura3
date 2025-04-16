<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Contenedor Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('users.index') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
            </div>

            <!-- Menú de Navegación (Escritorio) -->
            <div class="hidden md:flex space-x-8">
                @if(Auth::user()->role === 'admin')
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">Usuarios</x-nav-link>
                    <x-nav-link :href="route('presupuestos.index')" :active="request()->routeIs('presupuestos.index')">Presupuestos</x-nav-link>
                    <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.index')">Orden de taller</x-nav-link>
                    <x-nav-link :href="route('revisions.index')" :active="request()->routeIs('revisions.*')">Revisiones</x-nav-link>
                    <x-nav-link :href="route('bikes.index')" :active="request()->routeIs('bikes.index')">Bicicletas</x-nav-link>
                    <x-nav-link :href="route('components.index')" :active="request()->routeIs('components.*')">Componentes</x-nav-link>
                    <x-nav-link :href="route('avisos.index')" :active="request()->routeIs('avisos.*')">Mensajes</x-nav-link>
                    <x-nav-link :href="route('appointments.historico')" :active="request()->routeIs('appointments.historico')">Histórico Citas</x-nav-link>
                @endif
            </div>

            <!-- Botón de Usuario (Escritorio) -->
            <div class="hidden md:flex items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Botón de Menú (Móvil) -->
            <div class="md:hidden flex items-center">
                <button @click="open = ! open" class="p-2 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú Desplegable (Móvil) -->
    <div :class="{ 'block': open, 'hidden': !open }" class="md:hidden bg-white border-t border-gray-200">
        <div class="py-2">
            @if(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">Usuarios</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('presupuestos.index')" :active="request()->routeIs('presupuestos.index')">Presupuestos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.index')">Citas</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('revisions.index')" :active="request()->routeIs('revisions.*')">Revisiones</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bikes.index')" :active="request()->routeIs('bikes.index')">Bicicletas</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('components.index')" :active="request()->routeIs('components.*')">Componentes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('avisos.index')" :active="request()->routeIs('avisos.*')">Mensajes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('appointments.historico')" :active="request()->routeIs('appointments.historico')">Histórico Citas</x-responsive-nav-link>
            @endif
        </div>

        <!-- Opciones de Usuario (Móvil) -->
        <div class="border-t border-gray-200 py-3">
            <div class="px-4">
                <div class="text-gray-800 font-medium">{{ Auth::user()->name }}</div>
                <div class="text-gray-500 text-sm">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Perfil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
