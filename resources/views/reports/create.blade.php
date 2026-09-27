<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($errors->has('report'))
                    <div
                        class="mb-6 rounded-md border border-red-200
                               bg-red-50 p-4 text-sm text-red-700"
                    >
                        {{ $errors->first('report') }}
                    </div>
                @endif

                <p class="text-sm text-gray-600">
                    Опишите причину жалобы. Она будет отправлена
                    администраторам на рассмотрение.
                </p>

                <form
                    method="POST"
                    action="@switch($reportableType)
                        @case('user')
                            {{ route('reports.users.store', $reportable) }}
                            @break

                        @case('event')
                            {{ route('reports.events.store', $reportable) }}
                            @break

                        @case('message')
                            {{ route('reports.messages.store', $reportable) }}
                            @break
                    @endswitch"
                    class="mt-6 space-y-6"
                >
                    @csrf

                    <div>
                        <x-input-label
                            for="reason"
                            value="Причина"
                        />

                        <select
                            id="reason"
                            name="reason"
                            required
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm
                                   focus:border-indigo-500
                                   focus:ring-indigo-500"
                        >
                            <option value="">
                                Выберите причину
                            </option>

                            @foreach ($reasons as $reason)
                                <option
                                    value="{{ $reason->value }}"
                                    @selected(
                                        old('reason') === $reason->value
                                    )
                                >
                                    @switch($reason->value)
                                        @case('spam')
                                            Спам
                                            @break

                                        @case('harassment')
                                            Оскорбления или преследование
                                            @break

                                        @case('inappropriate_content')
                                            Неприемлемый контент
                                            @break

                                        @case('scam')
                                            Мошенничество
                                            @break

                                        @case('other')
                                            Другое
                                            @break
                                    @endswitch
                                </option>
                            @endforeach
                        </select>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('reason')"
                        />
                    </div>

                    <div>
                        <x-input-label
                            for="description"
                            value="Описание"
                        />

                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            maxlength="2000"
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm
                                   focus:border-indigo-500
                                   focus:ring-indigo-500"
                            placeholder="Расскажите подробнее о проблеме..."
                        >{{ old('description') }}</textarea>

                        <div class="mt-1 text-xs text-gray-500">
                            Необязательно. Максимум 2000 символов.
                        </div>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('description')"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a
                            href="{{ $backUrl }}"
                            class="text-sm text-gray-600
                                   hover:text-gray-900"
                        >
                            Отмена
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2
                                   rounded-md bg-red-600
                                   font-semibold text-xs text-white
                                   uppercase tracking-widest
                                   hover:bg-red-500"
                        >
                            Отправить жалобу
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>