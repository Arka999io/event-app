<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Аватар
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Загрузите фотографию профиля.
            Поддерживаются JPG, PNG и WebP размером до 5 МБ.
        </p>
    </header>

    <div class="mt-6">
        @if ($user->profile?->avatar)
            <img
                src="{{ $user->profile->avatar->url() }}"
                alt="{{ $user->name }}"
                class="h-32 w-32 rounded-full object-cover"
            >
        @else
            <div
                class="flex h-32 w-32 items-center justify-center
                       rounded-full bg-gray-200 text-4xl font-semibold
                       text-gray-700"
            >
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
        @endif
    </div>

    <form
        method="POST"
        action="{{ route('profile.avatar.update', [], false) }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-6"
    >
        @csrf

        <div>
            <x-input-label
                for="avatar"
                value="Выберите фотографию"
            />

            <input
                id="avatar"
                name="avatar"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                required
                class="mt-2 block w-full text-sm text-gray-600
                       file:mr-4 file:rounded-md file:border-0
                       file:bg-gray-800 file:px-4 file:py-2
                       file:text-xs file:font-semibold file:uppercase
                       file:tracking-widest file:text-white
                       hover:file:bg-gray-700"
            >

            <x-input-error
                class="mt-2"
                :messages="$errors->get('avatar')"
            />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>
                Сохранить аватар
            </x-primary-button>

            @if (session('status') === 'avatar-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >
                    Сохранено.
                </p>
            @endif
        </div>
    </form>
</section>