{{-- Editable RPH (draft review and edit). Expects $document, $subjectOptions, $classOptions, $sections --}}
@php($form = \App\Services\Rph\RphContent::toForm($document->content ?? []))

<x-card title="Lesson Details">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="sm:col-span-2 lg:col-span-3">
            <x-form.input name="title" label="RPH title" :value="$document->title" required />
        </div>
        @include('teacher.rph._header-fields', ['values' => $document->only(['subject_id', 'school_class_id', 'topic', 'theme', 'unit', 'week', 'student_count', 'duration_minutes']) + [
            'lesson_date' => $document->lesson_date?->format('Y-m-d'),
            'start_time' => $document->start_time ? substr($document->start_time, 0, 5) : null,
        ]])
    </div>
</x-card>

<x-card title="RPH Content">
    <p class="mb-5 flex items-start gap-2 rounded-md bg-slate-50 p-3 text-[15px] text-slate-600">
        <x-icon name="info" class="mt-0.5" />
        For lists, write one item per line. Learning objectives complete the sentence "Pada akhir pengajaran dan pembelajaran murid dapat:".
    </p>
    <div class="grid gap-5 lg:grid-cols-2">
        @foreach ($sections as $key => [$label, $isList, $malayLabel])
            <div @class(['lg:col-span-2' => in_array($key, ['teaching_activities', 'learning_objectives', 'reflection'])])>
                <x-form.textarea :name="'content['.$key.']'" :id="'content_'.$key" :label="app()->getLocale() === 'ms' ? $malayLabel : __($label).' ('.$malayLabel.')'" :value="$form[$key]"
                                 :rows="$isList ? 5 : 3" :required="$key === 'learning_objectives'"
                                 :hint="$key === 'reflection' ? __('Usually completed after the lesson. Leave empty to print the fill-in reflection lines.') : null" />
            </div>
        @endforeach
    </div>
</x-card>
