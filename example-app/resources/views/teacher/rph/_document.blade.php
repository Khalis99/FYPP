{{-- i18n:off --}}
{{--
    Read-only RPH laid out like the KPM daily lesson plan template (labels in Bahasa Melayu).
    Expects $document (RphDocument with subject, schoolClass, teacher.user loaded).
--}}
@php
    $content = \App\Services\Rph\RphContent::normalize($document->content ?? []);
    $sections = \App\Services\Rph\RphContent::SECTIONS;
    $date = $document->lesson_date->copy()->locale('ms');
    $students = $document->student_count ?: '____';
@endphp

<div class="rph">
    <div class="rph-header">
        <p class="rph-brand">{{ config('schoolhub.school_name') }}</p>
        <h1>RPH {{ \Illuminate\Support\Str::upper($document->subject?->name) }} {{ $document->schoolClass?->name }}</h1>
    </div>

    <table class="rph-table">
        <tr><th colspan="4" class="rph-bar">RANCANGAN PENGAJARAN HARIAN</th></tr>
        <tr>
            <th>MATA PELAJARAN</th><td>{{ $document->subject?->name }}</td>
            <th>MINGGU</th><td>{{ $document->week ?: '' }}</td>
        </tr>
        <tr>
            <th>MASA</th><td>{{ $document->timeRange() ?? '' }} ({{ $document->duration_minutes }} minit)</td>
            <th>TARIKH / HARI</th><td>{{ $date->translatedFormat('j F Y') }} / {{ $date->translatedFormat('l') }}</td>
        </tr>
        <tr>
            <th>UNIT</th><td>{{ $document->unit }}</td>
            <th>KELAS</th><td>{{ $document->schoolClass?->name }}</td>
        </tr>
        <tr>
            <th>TEMA</th><td>{{ $document->theme }}</td>
            <th>BIL. MURID</th><td>{{ $document->student_count }}</td>
        </tr>
        <tr>
            <th>TAJUK</th><td>{{ $document->topic }}</td>
            <th>GURU</th><td>{{ $document->teacher->user->name }}</td>
        </tr>

        @include('teacher.rph._section', ['key' => 'learning_standards'])

        <tr><th colspan="4" class="rph-bar">{{ \Illuminate\Support\Str::upper($sections['learning_objectives'][2]) }}</th></tr>
        <tr>
            <td colspan="4">
                <p class="rph-lead">Pada akhir pengajaran dan pembelajaran murid dapat:</p>
                @include('teacher.rph._list', ['items' => $content['learning_objectives']])
            </td>
        </tr>

        @include('teacher.rph._section', ['key' => 'previous_knowledge'])
        @include('teacher.rph._section', ['key' => 'teaching_aids'])
        @include('teacher.rph._section', ['key' => 'teaching_activities'])

        <tr>
            <th colspan="2" class="rph-subbar">{{ \Illuminate\Support\Str::upper($sections['teacher_activities'][2]) }}</th>
            <th colspan="2" class="rph-subbar">{{ \Illuminate\Support\Str::upper($sections['student_activities'][2]) }}</th>
        </tr>
        <tr>
            <td colspan="2">@include('teacher.rph._list', ['items' => $content['teacher_activities']])</td>
            <td colspan="2">@include('teacher.rph._list', ['items' => $content['student_activities']])</td>
        </tr>

        @include('teacher.rph._section', ['key' => 'assessment'])
        @include('teacher.rph._section', ['key' => 'closure'])

        <tr><th colspan="4" class="rph-bar">REFLEKSI</th></tr>
        <tr>
            <td colspan="4">
                @if ($content['reflection'] !== '')
                    <p class="rph-text">{{ $content['reflection'] }}</p>
                @else
                    {{-- Fill-in lines from the KPM template, completed by hand after the lesson --}}
                    <p>_____/{{ $students }} murid dapat mencapai objektif pembelajaran yang ditetapkan.</p>
                    <p>_____/{{ $students }} murid boleh melengkapkan latihan yang diberikan.</p>
                    <p>_____/{{ $students }} murid memerlukan latihan lanjut dan bimbingan guru.</p>
                    <p class="rph-note">Catatan: Pengajaran dan pembelajaran tidak dapat dijalankan pada hari ini dan akan diteruskan pada sesi pembelajaran seterusnya kerana:</p>
                    <div class="rph-lines"></div><div class="rph-lines"></div>
                @endif
            </td>
        </tr>
    </table>
</div>