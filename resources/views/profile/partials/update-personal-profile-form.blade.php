<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Личная информация
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Эта информация будет отображаться в вашем публичном профиле.
        </p>
    </header>

    <form
        method="POST"
        action="{{ route('profile.personal.update') }}"
        class="mt-6 space-y-6"
    >
        @csrf
        @method('PATCH')

        <div>
            <x-input-label
                for="first_name"
                value="Имя"
            />

            <x-text-input
                id="first_name"
                name="first_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('first_name', $user->profile?->first_name)"
                required
                autocomplete="given-name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('first_name')"
            />
        </div>

        <div>
            <x-input-label
                for="last_name"
                value="Фамилия"
            />

            <x-text-input
                id="last_name"
                name="last_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('last_name', $user->profile?->last_name)"
                autocomplete="family-name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('last_name')"
            />
        </div>

        <div>
            <x-input-label
                for="birth_date"
                value="Дата рождения"
            />

            <x-text-input
                id="birth_date"
                name="birth_date"
                type="date"
                class="mt-1 block w-full"
                :value="old(
                    'birth_date',
                    $user->profile?->birth_date?->format('Y-m-d')
                )"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('birth_date')"
            />
        </div>

        <div>
            <x-input-label
                for="gender"
                value="Пол"
            />

            <select
                id="gender"
                name="gender"
                class="mt-1 block w-full rounded-md border-gray-300
                       shadow-sm focus:border-indigo-500
                       focus:ring-indigo-500"
            >
                <option value="">
                    Не указан
                </option>

                <option
                    value="male"
                    @selected(
                        old('gender', $user->profile?->gender) === 'male'
                    )
                >
                    Мужской
                </option>

                <option
                    value="female"
                    @selected(
                        old('gender', $user->profile?->gender) === 'female'
                    )
                >
                    Женский
                </option>
            </select>

            <x-input-error
                class="mt-2"
                :messages="$errors->get('gender')"
            />
        </div>

        <div>
            <x-input-label
                for="city"
                value="Город"
            />

            <x-text-input
                id="city"
                name="city"
                type="text"
                class="mt-1 block w-full"
                :value="old('city', $user->profile?->city)"
                autocomplete="address-level2"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('city')"
            />
        </div>

        <div>
            <x-input-label
                for="bio"
                value="О себе"
            />

            <textarea
                id="bio"
                name="bio"
                rows="6"
                maxlength="2000"
                class="mt-1 block w-full rounded-md border-gray-300
                       shadow-sm focus:border-indigo-500
                       focus:ring-indigo-500"
                placeholder="Расскажите немного о себе..."
            >{{ old('bio', $user->profile?->bio) }}</textarea>

            <div class="mt-1 text-xs text-gray-500">
                Максимум 2000 символов.
            </div>

            <x-input-error
                class="mt-2"
                :messages="$errors->get('bio')"
            />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>
                Сохранить
            </x-primary-button>

            @if (session('status') === 'personal-profile-updated')
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