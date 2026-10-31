<?php

use App\Models\User;
use App\Support\TranslationKeys;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

test('the login page is in English by default and offers the language switch', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Log in to Pelita')
        ->assertSee('data-language-switch', false)
        ->assertSee(route('locale.update', 'ms'), false);
});

test('choosing Bahasa Melayu switches the login page and is remembered', function () {
    $this->from(route('login'))
        ->post(route('locale.update', 'ms'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'ms')
        ->assertCookie('locale');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Log masuk ke Pelita')
        ->assertSee('Kata laluan')
        ->assertSee('Lupa kata laluan?')
        ->assertDontSee('Log in to Pelita')
        ->assertSee(route('locale.update', 'en'), false);

    $this->from(route('login'))->post(route('locale.update', 'en'));
    $this->get(route('login'))->assertSee('Log in to Pelita');
});

test('the saved cookie keeps the language for a visitor with no session', function () {
    $this->withCookie('locale', 'ms')
        ->get(route('login'))
        ->assertSee('Log masuk ke Pelita');
});

test('only known languages are accepted', function () {
    $this->post(route('locale.update', 'fr'))->assertNotFound();
    $this->post(route('locale.update', 'xx'))->assertNotFound();
});

test('an unknown language in the session falls back to English', function () {
    $this->withSession(['locale' => 'fr'])->get(route('login'))->assertSee('Log in to Pelita');
});

test('the teacher portal appears in Bahasa Melayu with Malay dates', function () {
    Storage::fake('local');
    $this->seed(DemoDataSeeder::class);
    Carbon::setTestNow(Carbon::parse('2026-10-05 09:00')); // a Monday

    $teacher = User::where('email', 'aisyah@schoolhub.test')->sole();

    $this->actingAs($teacher)->withSession(['locale' => 'ms'])
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertSee('Selamat pagi, Cikgu Nur Aisyah')
        ->assertSee('Papan Pemuka')
        ->assertSee('Jadual Waktu')
        ->assertSee('Isnin, 5 Oktober 2026')
        ->assertSee('<html lang="ms"', false)
        ->assertDontSee('Good morning');

    $this->actingAs($teacher)->withSession(['locale' => 'ms'])
        ->get(route('teacher.timetable.index'))
        ->assertOk()
        ->assertSee('Isnin')
        ->assertSee('Jadual Waktu Mengajar Saya');
});

test('form errors appear in Bahasa Melayu', function () {
    $this->withSession(['locale' => 'ms'])
        ->from(route('login'))
        ->post(route('login.store'), ['email' => '', 'password' => ''])
        ->assertSessionHasErrors(['email' => 'Sila masukkan alamat e-mel anda.']);
});

test('every piece of interface text has a Bahasa Melayu translation', function () {
    expect(TranslationKeys::missing('ms'))->toBe([]);
});

test('every translation is used by the interface', function () {
    $translated = array_keys(json_decode(file_get_contents(lang_path('ms.json')), true));
    $used = TranslationKeys::all();

    // Dynamic labels (enums, notification titles, greetings, JS text) are looked up by variable, so they are exempt.
    $dynamic = ['Notes', 'Slides', 'Worksheet', 'Reference', 'Other', 'Draft', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
        'Assignment due soon', 'New submission', 'Notification', 'Good morning', 'Good afternoon', 'Good evening', 'Show password', 'Hide password', 'Lighting the way for teachers'];

    expect(array_values(array_diff($translated, $used, $dynamic)))->toBe([]);
});

test('switching language while signed in saves it to the account', function () {
    $user = teacherTeaching()->user;

    expect($user->locale)->toBeNull();

    $this->actingAs($user)->from(route('profile.show'))
        ->post(route('locale.update', 'ms'))
        ->assertRedirect(route('profile.show'));

    expect($user->fresh()->locale)->toBe('ms');
});

test('a saved account language applies on a new device with no session or cookie', function () {
    $user = teacherTeaching()->user;
    $user->update(['locale' => 'ms']);

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Profil Saya')
        ->assertSee('<html lang="ms"', false);
});

test('the saved account language wins over a different cookie', function () {
    $user = teacherTeaching()->user;
    $user->update(['locale' => 'ms']);

    $this->actingAs($user)->withCookie('locale', 'en')
        ->get(route('profile.show'))
        ->assertSee('Profil Saya')
        ->assertDontSee('My Profile');
});

test('the profile page lets the user pick a language', function () {
    $user = teacherTeaching()->user;

    $this->actingAs($user)->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Choose the language Pelita uses for you.')
        ->assertSee('English')
        ->assertSee('Bahasa Melayu')
        ->assertSee(route('locale.update', 'ms'), false);

    $user->update(['locale' => 'ms']);

    $this->actingAs($user->fresh())->get(route('profile.show'))
        ->assertSee('Pilih bahasa yang digunakan Pelita untuk anda.')
        ->assertSee('aria-pressed="true"', false);
});
