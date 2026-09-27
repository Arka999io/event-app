<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Enums\Gender;
use App\Enums\GenderPreference;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Application;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Message;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        /*
         |--------------------------------------------------------------------------
         | Пользователи
         |--------------------------------------------------------------------------
         */

        $admin = User::query()->create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => $password,
        ]);

        $admin->forceFill([
            'is_admin' => true,
        ])->save();

        $anna = User::query()->create([
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'email_verified_at' => now(),
            'password' => $password,
        ]);

        $ivan = User::query()->create([
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'email_verified_at' => now(),
            'password' => $password,
        ]);

        $maria = User::query()->create([
            'name' => 'Мария',
            'email' => 'maria@example.com',
            'email_verified_at' => now(),
            'password' => $password,
        ]);

        $alex = User::query()->create([
            'name' => 'Алексей',
            'email' => 'alex@example.com',
            'email_verified_at' => now(),
            'password' => $password,
        ]);

        $blocked = User::query()->create([
            'name' => 'Заблокированный пользователь',
            'email' => 'blocked@example.com',
            'email_verified_at' => now(),
            'password' => $password,
        ]);

        $blocked->forceFill([
            'is_banned' => true,
            'banned_at' => now()->subDays(2),
            'ban_reason' => 'Демонстрационная блокировка за нарушение правил.',
        ])->save();

        /*
         |--------------------------------------------------------------------------
         | Профили
         |--------------------------------------------------------------------------
         |
         | User::booted() уже создаёт Profile автоматически,
         | поэтому здесь обновляем существующие записи.
         */

        $admin->profile()->update([
            'first_name' => 'Администратор',
            'last_name' => 'Системы',
            'birth_date' => '1990-01-01',
            'gender' => Gender::Male->value,
            'bio' => 'Демонстрационный аккаунт администратора.',
            'city' => 'Москва',
        ]);

        $anna->profile()->update([
            'first_name' => 'Анна',
            'last_name' => 'Соколова',
            'birth_date' => '1997-04-12',
            'gender' => Gender::Female->value,
            'bio' => 'Люблю настольные игры, кино и знакомиться с новыми людьми.',
            'city' => 'Москва',
        ]);

        $ivan->profile()->update([
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'birth_date' => '1995-08-21',
            'gender' => Gender::Male->value,
            'bio' => 'Люблю походы, прогулки и активный отдых.',
            'city' => 'Москва',
        ]);

        $maria->profile()->update([
            'first_name' => 'Мария',
            'last_name' => 'Орлова',
            'birth_date' => '1999-02-17',
            'gender' => Gender::Female->value,
            'bio' => 'Кино, искусство, уютные встречи и интересные люди.',
            'city' => 'Москва',
        ]);

        $alex->profile()->update([
            'first_name' => 'Алексей',
            'last_name' => 'Волков',
            'birth_date' => '1993-11-05',
            'gender' => Gender::Male->value,
            'bio' => 'Фотограф-любитель. Иногда организую прогулки по городу.',
            'city' => 'Москва',
        ]);

        $blocked->profile()->update([
            'first_name' => 'Сергей',
            'last_name' => 'Демо',
            'birth_date' => '1992-06-14',
            'gender' => Gender::Male->value,
            'bio' => 'Демонстрационный заблокированный пользователь.',
            'city' => 'Москва',
        ]);

        /*
         |--------------------------------------------------------------------------
         | Категории
         |--------------------------------------------------------------------------
         */

        $boardGames = Category::query()
            ->where('slug', 'board-games')
            ->firstOrFail();

        $outdoors = Category::query()
            ->where('slug', 'outdoors')
            ->firstOrFail();

        $movieNight = Category::query()
            ->where('slug', 'movie-night')
            ->firstOrFail();

        $art = Category::query()
            ->where('slug', 'art')
            ->firstOrFail();

        $houseParty = Category::query()
            ->where('slug', 'house-party')
            ->firstOrFail();

        /*
         |--------------------------------------------------------------------------
         | Мероприятия
         |--------------------------------------------------------------------------
         */

        $boardGameEvent = Event::query()->create([
            'organizer_id' => $anna->id,
            'category_id' => $boardGames->id,
            'title' => 'Вечер настольных игр',
            'description' => 'Собираемся небольшой компанией поиграть в современные настольные игры. Новичкам тоже рады.',
            'starts_at' => now()->addDays(5)->setTime(19, 0),
            'ends_at' => now()->addDays(5)->setTime(23, 0),
            'meeting_address' => 'У входа в антикафе',
            'venue_address' => 'ул. Тверская, 15',
            'min_age' => 18,
            'max_age' => 35,
            'gender_preference' => GenderPreference::Any->value,
            'spots_total' => 8,
            'spots_taken' => 2,
            'status' => EventStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $walkingEvent = Event::query()->create([
            'organizer_id' => $ivan->id,
            'category_id' => $outdoors->id,
            'title' => 'Вечерняя прогулка по центру',
            'description' => 'Неспешная прогулка по центру города с кофе и разговорами.',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHours(2),
            'meeting_address' => 'У главного входа в парк',
            'venue_address' => 'Центр Москвы',
            'min_age' => 18,
            'max_age' => null,
            'gender_preference' => GenderPreference::Any->value,
            'spots_total' => 6,
            'spots_taken' => 2,
            'status' => EventStatus::Ongoing->value,
            'published_at' => now()->subDays(3),
        ]);

        $movieEvent = Event::query()->create([
            'organizer_id' => $anna->id,
            'category_id' => $movieNight->id,
            'title' => 'Киновечер с обсуждением',
            'description' => 'Посмотрели фильм, заказали пиццу и после просмотра обсудили впечатления.',
            'starts_at' => now()->subDays(10)->setTime(18, 30),
            'ends_at' => now()->subDays(10)->setTime(22, 30),
            'meeting_address' => null,
            'venue_address' => 'ул. Арбат, 20',
            'min_age' => 18,
            'max_age' => 40,
            'gender_preference' => GenderPreference::Any->value,
            'spots_total' => 6,
            'spots_taken' => 3,
            'status' => EventStatus::Completed->value,
            'published_at' => now()->subDays(20),
        ]);

        $hikingEvent = Event::query()->create([
            'organizer_id' => $ivan->id,
            'category_id' => $outdoors->id,
            'title' => 'Поход выходного дня',
            'description' => 'Небольшой однодневный поход для тех, кто любит природу и активный отдых.',
            'starts_at' => now()->subDays(20)->setTime(9, 0),
            'ends_at' => now()->subDays(20)->setTime(18, 0),
            'meeting_address' => 'У станции метро',
            'venue_address' => 'Подмосковье',
            'min_age' => 20,
            'max_age' => 45,
            'gender_preference' => GenderPreference::Any->value,
            'spots_total' => 8,
            'spots_taken' => 2,
            'status' => EventStatus::Completed->value,
            'published_at' => now()->subDays(30),
        ]);

        Event::query()->create([
            'organizer_id' => $alex->id,
            'category_id' => $art->id,
            'title' => 'Фотопрогулка по старому городу',
            'description' => 'Черновик будущей прогулки для любителей городской фотографии.',
            'starts_at' => now()->addDays(14)->setTime(15, 0),
            'ends_at' => now()->addDays(14)->setTime(18, 0),
            'meeting_address' => null,
            'venue_address' => 'Центр Москвы',
            'min_age' => null,
            'max_age' => null,
            'gender_preference' => GenderPreference::Any->value,
            'spots_total' => 10,
            'spots_taken' => 0,
            'status' => EventStatus::Draft->value,
            'published_at' => null,
        ]);

        $cancelledEvent = Event::query()->create([
            'organizer_id' => $blocked->id,
            'category_id' => $houseParty->id,
            'title' => 'Отменённая домашняя вечеринка',
            'description' => 'Это мероприятие отменено после блокировки организатора администрацией.',
            'starts_at' => now()->addDays(3)->setTime(20, 0),
            'ends_at' => now()->addDays(4)->setTime(1, 0),
            'meeting_address' => null,
            'venue_address' => 'Адрес скрыт',
            'min_age' => 18,
            'max_age' => null,
            'gender_preference' => GenderPreference::Any->value,
            'spots_total' => 10,
            'spots_taken' => 0,
            'status' => EventStatus::Cancelled->value,
            'published_at' => now()->subDays(4),
        ]);

        /*
         |--------------------------------------------------------------------------
         | Заявки
         |--------------------------------------------------------------------------
         */

        $this->application(
            $movieEvent,
            $maria,
            ApplicationStatus::Accepted,
            'Буду рада присоединиться к киновечеру.'
        );

        $this->application(
            $movieEvent,
            $ivan,
            ApplicationStatus::Accepted,
            'Люблю кино и обсуждения после просмотра.'
        );

        $this->application(
            $hikingEvent,
            $anna,
            ApplicationStatus::Accepted,
            'Давно хотела выбраться в небольшой поход.'
        );

        $this->application(
            $hikingEvent,
            $alex,
            ApplicationStatus::Accepted,
            'Возьму с собой фотоаппарат.'
        );

        $this->application(
            $boardGameEvent,
            $ivan,
            ApplicationStatus::Accepted,
            'Люблю настольные игры.'
        );

        $this->application(
            $boardGameEvent,
            $maria,
            ApplicationStatus::Pending,
            'Хотела бы присоединиться.'
        );

        $this->application(
            $boardGameEvent,
            $alex,
            ApplicationStatus::Waitlisted,
            'Если появится место — с удовольствием приду.'
        );

        $this->application(
            $walkingEvent,
            $anna,
            ApplicationStatus::Accepted,
            'Присоединюсь к прогулке.'
        );

        $this->application(
            $walkingEvent,
            $maria,
            ApplicationStatus::Rejected,
            'Буду рада прогуляться.'
        );

        $this->application(
            $walkingEvent,
            $alex,
            ApplicationStatus::Withdrawn,
            'Сначала планировал прийти, но планы изменились.'
        );

        /*
         |--------------------------------------------------------------------------
         | Отзывы
         |--------------------------------------------------------------------------
         */

        Review::query()->create([
            'event_id' => $movieEvent->id,
            'author_id' => $maria->id,
            'subject_id' => $anna->id,
            'rating' => 5,
            'comment' => 'Очень уютный вечер. Анна всё отлично организовала!',
        ]);

        Review::query()->create([
            'event_id' => $movieEvent->id,
            'author_id' => $ivan->id,
            'subject_id' => $anna->id,
            'rating' => 4,
            'comment' => 'Хорошая организация и приятная компания.',
        ]);

        Review::query()->create([
            'event_id' => $movieEvent->id,
            'author_id' => $anna->id,
            'subject_id' => $maria->id,
            'rating' => 5,
            'comment' => 'Мария была очень приятной и дружелюбной участницей.',
        ]);

        Review::query()->create([
            'event_id' => $movieEvent->id,
            'author_id' => $anna->id,
            'subject_id' => $ivan->id,
            'rating' => 5,
            'comment' => 'Иван пришёл вовремя и отлично поддерживал обсуждение.',
        ]);

        Review::query()->create([
            'event_id' => $hikingEvent->id,
            'author_id' => $anna->id,
            'subject_id' => $ivan->id,
            'rating' => 5,
            'comment' => 'Отличный маршрут и очень хорошая организация похода.',
        ]);

        Review::query()->create([
            'event_id' => $hikingEvent->id,
            'author_id' => $ivan->id,
            'subject_id' => $anna->id,
            'rating' => 5,
            'comment' => 'Анна отлично проявила себя в походе.',
        ]);

        Review::query()->create([
            'event_id' => $hikingEvent->id,
            'author_id' => $alex->id,
            'subject_id' => $ivan->id,
            'rating' => 4,
            'comment' => 'Хорошая прогулка, красивые места и дружная компания.',
        ]);

        /*
         |--------------------------------------------------------------------------
         | Чаты
         |--------------------------------------------------------------------------
         */

        $movieMariaConversation = Conversation::query()->create([
            'event_id' => $movieEvent->id,
            'user_id' => $maria->id,
        ]);

        $this->seedConversation(
            $movieMariaConversation,
            $anna,
            $maria,
            [
                ['participant', 'Привет! Я отправила заявку на киновечер.'],
                ['organizer', 'Привет! Видела, буду рада тебя видеть.'],
                ['participant', 'Подскажи, пожалуйста, во сколько лучше прийти?'],
                ['organizer', 'Можно приходить к 18:20, начнём примерно в 18:30.'],
                ['participant', 'Хорошо. Нужно что-нибудь взять с собой?'],
                ['organizer', 'Нет, всё основное будет. Если хочешь — можешь взять что-нибудь к чаю.'],
                ['participant', 'Отлично, тогда возьму печенье.'],
                ['organizer', 'Супер :)'],
                ['participant', 'А какой фильм в итоге выбрали?'],
                ['organizer', 'Решили посмотреть детектив, который обсуждали в описании.'],
                ['participant', 'Звучит интересно.'],
                ['organizer', 'После фильма ещё немного посидим и обсудим.'],
                ['participant', 'Я как раз люблю такие обсуждения.'],
                ['organizer', 'Тогда точно будет интересно.'],
                ['participant', 'Я уже выезжаю.'],
                ['organizer', 'Хорошо, напиши, если не найдёшь вход.'],
                ['participant', 'Кажется, я возле нужного дома.'],
                ['organizer', 'Да, вход со стороны двора.'],
                ['participant', 'Нашла, спасибо!'],
                ['organizer', 'Отлично, поднимайся.'],
                ['participant', 'Спасибо за вечер, всё было здорово!'],
                ['organizer', 'Спасибо, что пришла!'],
                                ['participant', 'Обязательно приду ещё на какой-нибудь киновечер.'],
                ['organizer', 'Буду рада видеть тебя снова!'],
                ['participant', 'Хорошего вечера!'],
                ['organizer', 'И тебе!'],
            ]
        );

        /*
         * Второй чат по завершённому киновечеру.
         */
        $movieIvanConversation = Conversation::query()->create([
            'event_id' => $movieEvent->id,
            'user_id' => $ivan->id,
        ]);

        $this->seedConversation(
            $movieIvanConversation,
            $anna,
            $ivan,
            [
                [
                    'participant',
                    'Привет! Моя заявка ещё актуальна?',
                ],
                [
                    'organizer',
                    'Привет! Да, конечно. Я тебя уже пригласила.',
                ],
                [
                    'participant',
                    'Отлично, спасибо.',
                ],
                [
                    'organizer',
                    'Начинаем в 18:30.',
                ],
                [
                    'participant',
                    'Буду вовремя.',
                ],
                [
                    'organizer',
                    'До встречи!',
                ],
            ]
        );

        /*
         * Чат по текущей прогулке.
         */
        $walkingAnnaConversation = Conversation::query()->create([
            'event_id' => $walkingEvent->id,
            'user_id' => $anna->id,
        ]);

        $this->seedConversation(
            $walkingAnnaConversation,
            $ivan,
            $anna,
            [
                [
                    'participant',
                    'Привет! Прогулка сегодня точно состоится?',
                ],
                [
                    'organizer',
                    'Привет! Да, всё по плану.',
                ],
                [
                    'participant',
                    'Я буду минут за десять до начала.',
                ],
                [
                    'organizer',
                    'Отлично. Встречаемся у главного входа.',
                ],
                [
                    'participant',
                    'Хорошо, до встречи.',
                ],
            ]
        );

        /*
         * Чат по будущему вечеру настольных игр.
         */
        $boardIvanConversation = Conversation::query()->create([
            'event_id' => $boardGameEvent->id,
            'user_id' => $ivan->id,
        ]);

        $this->seedConversation(
            $boardIvanConversation,
            $anna,
            $ivan,
            [
                [
                    'participant',
                    'Привет! Какие игры планируются?',
                ],
                [
                    'organizer',
                    'Привет! Будут Кодовые имена, Каркассон и несколько небольших игр.',
                ],
                [
                    'participant',
                    'Отлично, Каркассон как раз люблю.',
                ],
                [
                    'organizer',
                    'Тогда тебе точно понравится.',
                ],
            ]
        );

        /*
         |--------------------------------------------------------------------------
         | Демонстрационные жалобы
         |--------------------------------------------------------------------------
         */

        /*
         * Жалоба на пользователя.
         */
        Report::query()->create([
            'reporter_id' => $maria->id,
            'reportable_type' => $alex->getMorphClass(),
            'reportable_id' => $alex->id,
            'reason' => ReportReason::Other->value,
            'description' => 'Демонстрационная жалоба на пользователя для проверки панели модерации.',
            'status' => ReportStatus::Pending->value,
        ]);

        /*
         * Жалоба на мероприятие.
         */
        Report::query()->create([
            'reporter_id' => $ivan->id,
            'reportable_type' => $cancelledEvent->getMorphClass(),
            'reportable_id' => $cancelledEvent->id,
            'reason' => ReportReason::InappropriateContent->value,
            'description' => 'Демонстрационная жалоба на мероприятие.',
            'status' => ReportStatus::Pending->value,
        ]);

        /*
         * Жалоба на конкретное сообщение.
         *
         * Берём сообщение примерно из середины длинного чата,
         * чтобы в Filament было видно сообщения и до, и после.
         */
        $reportedMessage = $movieMariaConversation
            ->messages()
            ->orderBy('id')
            ->skip(12)
            ->firstOrFail();

        Report::query()->create([
            'reporter_id' => $maria->id,
            'reportable_type' => $reportedMessage->getMorphClass(),
            'reportable_id' => $reportedMessage->id,
            'reason' => ReportReason::Harassment->value,
            'description' => 'Демонстрационная жалоба на сообщение. Используется для проверки отображения контекста переписки.',
            'status' => ReportStatus::Pending->value,
        ]);
    }

    private function application(
        Event $event,
        User $applicant,
        ApplicationStatus $status,
        ?string $coverLetter = null,
    ): Application {
        return Application::query()->create([
            'event_id' => $event->id,
            'applicant_id' => $applicant->id,
            'cover_letter' => $coverLetter,
            'status' => $status->value,
        ]);
    }

    /**
     * @param array<int, array{0: string, 1: string}> $messages
     */
    private function seedConversation(
        Conversation $conversation,
        User $organizer,
        User $participant,
        array $messages,
    ): void {
        $createdAt = Carbon::now()
            ->subDays(10)
            ->setTime(17, 30);

        foreach ($messages as $index => [$senderType, $body]) {
            $sender = $senderType === 'organizer'
                ? $organizer
                : $participant;

            Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $body,
                'read_at' => $index < count($messages) - 2
                    ? $createdAt->copy()->addMinutes($index * 3 + 1)
                    : null,
                'created_at' => $createdAt
                    ->copy()
                    ->addMinutes($index * 3),
                'updated_at' => $createdAt
                    ->copy()
                    ->addMinutes($index * 3),
            ]);
        }
    }
}