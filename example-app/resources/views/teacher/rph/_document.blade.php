{{-- Read-only RPH document. Expects $document (RphDocument with subject, schoolClass, teacher.user loaded) --}}
@php
    $content = \App\Services\Rph\RphContent::normalize($document->content ?? []);
    $sections = \App\Services\Rph\RphContent::SECTIONS;
@endphp

<div class="rph">
    <div class="rph-header">
        <p class="rph-brand">SCHOOLHUB</p>
        <h1>Rancangan Pengajaran Harian (RPH)</h1>
        <p>{{ config('schoolhub.school_name') }}</p>
    </div>

    <table class="rph-table">
        <tr><th>Subject</th><td>{{ $document->subject?->name }}</td><th>Class</th><td>{{ $document->schoolClass?->name }}</td></tr>
        <tr><th>Date</th><td>{{ $document->lesson_date->format('l, j F Y') }}</td><th>Duration</th><td>{{ $document->duration_minutes }} minutes</td></tr>
        <tr><th>Topic</th><td>{{ $document->topic }}</td><th>No. of students</th><td>{{ $document->student_count ?: '—' }}</td></tr>
        <tr><th>Teacher</th><td colspan="3">{{ $document->teacher->user->name }}</td></tr>
    </table>

    @foreach (['learning_objectives', 'learning_standards', 'previous_knowledge', 'teaching_aids', 'teaching_activities'] as $key)
        @include('teacher.rph._section', ['key' => $key])
    @endforeach

    <div class="rph-section">
        <h2>Teacher &amp; Student Activities</h2>
        <table class="rph-table rph-activities">
            <tr><th>Teacher Activities</th><th>Student Activities</th></tr>
            <tr>
                <td>
                    @if ($content['teacher_activities'])
                        <ol>@foreach ($content['teacher_activities'] as $item)<li>{{ $item }}</li>@endforeach</ol>
                    @else — @endif
                </td>
                <td>
                    @if ($content['student_activities'])
                        <ol>@foreach ($content['student_activities'] as $item)<li>{{ $item }}</li>@endforeach</ol>
                    @else — @endif
                </td>
            </tr>
        </table>
    </div>

    @foreach (['assessment', 'closure'] as $key)
        @include('teacher.rph._section', ['key' => $key])
    @endforeach

    <div class="rph-section">
        <h2>{{ $sections['reflection'][0] }}</h2>
        @if ($content['reflection'] !== '')
            <p>{{ $content['reflection'] }}</p>
        @else
            {{-- Blank lines so the teacher can write a reflection by hand after the lesson --}}
            <div class="rph-lines"></div><div class="rph-lines"></div><div class="rph-lines"></div>
        @endif
    </div>
</div>
