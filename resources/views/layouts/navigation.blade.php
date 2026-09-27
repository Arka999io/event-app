@php
    $unreadNotificationsCount = Auth::check()
        ? Auth::user()->unreadNotifications()->count()
        : 0;
@endphp
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('events.index') }}">
                        <x-application-logo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-5 sm:-my-px sm:ms-8 sm:flex">
                    <x-nav-link
                        :href="route('events.index')"
                        :active="request()->routeIs('events.*')"
                    >
                        Мероприятия
                    </x-nav-link>

                    @auth
                        <x-nav-link
                            :href="route('applications.received')"
                            :active="request()->routeIs('applications.received')"
                        >
                            Отклики
                        </x-nav-link>

                        <x-nav-link
                            :href="route('applications.sent')"
                            :active="request()->routeIs('applications.sent')"
                        >
                            Приглашения
                        </x-nav-link>

                        <x-nav-link
                            :href="route('conversations.index')"
                            :active="request()->routeIs('conversations.*')"
                        >
                            Сообщения
                        </x-nav-link>

                        <x-nav-link
                            :href="route('notifications.index')"
                            :active="request()->routeIs('notifications.*')"
                        >
                            <span class="inline-flex items-center gap-2">
                                Уведомления

                                @if ($unreadNotificationsCount > 0)
                                    <span
                                        class="inline-flex min-w-5 h-5
                                            items-center justify-center
                                            rounded-full bg-red-600 px-1.5
                                            text-xs font-semibold text-white"
                                    >
                                        {{ $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </span>
                        </x-nav-link>

                        <x-nav-link
                            :href="route('dashboard')"
                            :active="request()->routeIs('dashboard')"
                        >
                            Личный кабинет
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Desktop Authentication -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2
                                       border border-transparent text-sm
                                       leading-4 font-medium rounded-md
                                       text-gray-500 bg-white
                                       hover:text-gray-700
                                       focus:outline-none
                                       transition ease-in-out duration-150"
                            >
                                <div>
                                    {{ Auth::user()->name }}
                                </div>

                                <div class="ms-1">
                                    <svg
                                        class="fill-current h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Профиль
                            </x-dropdown-link>

                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf

                                <x-dropdown-link
                                    :href="route('logout')"
                                    onclick="event.preventDefault();
                                             this.closest('form').submit();"
                                >
                                    Выйти
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-4">
                        <a
                            href="{{ route('login') }}"
                            class="text-sm text-gray-700 hover:text-gray-900"
                        >
                            Войти
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center px-4 py-2
                                   bg-gray-800 border border-transparent
                                   rounded-md font-semibold text-xs
                                   text-white uppercase tracking-widest
                                   hover:bg-gray-700"
                        >
                            Регистрация
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2
                           rounded-md text-gray-400 hover:text-gray-500
                           hover:bg-gray-100 focus:outline-none
                           focus:bg-gray-100 focus:text-gray-500
                           transition duration-150 ease-in-out"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            :class="{'hidden': open, 'inline-flex': ! open}"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{'hidden': ! open, 'inline-flex': open}"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div
        :class="{'block': open, 'hidden': ! open}"
        class="hidden sm:hidden"
    >
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link
                :href="route('events.index')"
                :active="request()->routeIs('events.*')"
            >
                Мероприятия
            </x-responsive-nav-link>

            @auth

                <x-responsive-nav-link
                    :href="route('applications.received')"
                    :active="request()->routeIs('applications.received')"
                >
                    Отклики
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('applications.sent')"
                    :active="request()->routeIs('applications.sent')"
                >
                    Приглашения
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('conversations.index')"
                    :active="request()->routeIs('conversations.*')"
                >
                    Сообщения
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('notifications.index')"
                    :active="request()->routeIs('notifications.*')"
                >
                    <span class="inline-flex items-center gap-2">
                        Уведомления

                        @if ($unreadNotificationsCount > 0)
                            <span
                                class="inline-flex min-w-5 h-5
                                    items-center justify-center
                                    rounded-full bg-red-600 px-1.5
                                    text-xs font-semibold text-white"
                            >
                                {{ $unreadNotificationsCount }}
                            </span>
                        @endif
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')"
                >
                    Личный кабинет
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Authentication -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="font-medium text-sm text-gray-500">
                        {{ Auth::user()->email }}
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link
                        :href="route('profile.edit')"
                    >
                        Профиль
                    </x-responsive-nav-link>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <x-responsive-nav-link
                            :href="route('logout')"
                            onclick="event.preventDefault();
                                     this.closest('form').submit();"
                        >
                            Выйти
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="space-y-1">
                    <x-responsive-nav-link
                        :href="route('login')"
                    >
                        Войти
                    </x-responsive-nav-link>

                    <x-responsive-nav-link
                        :href="route('register')"
                    >
                        Регистрация
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>