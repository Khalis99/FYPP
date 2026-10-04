{{-- Editable RPH (draft review and edit). Expects $document, $subjectOptions, $classOptions, $sections --}}
@php($form = \App\Services\Rph\RphContent::toForm($document->content ?? []))

<x-card title="Lesson Details">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="sm:col-span-2 lg:col-span-3">
            <x-form.input name="title" label="RPH title" :value="$document->title" required />
        </div>
        <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" :value="$document->subject_id" required />
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$document->school_class_id" required />
        <x-form.input name="topic" label="Topic" :value="$document->topic" required />
        <x-form.input name="lesson_date" type="date" label="Date" :value="$document->lesson_date?->format('Y-m-d')" required />
        <x-form.input name="duration_minutes" type="number" label="Duration (minutes)" :value="$document->duration_minutes" min="10" max="300" required />
        <x-form.input name="student_count" type="number" label="Number of students" :value="$document->student_count" min="1" max="100" />
    </div>
</x-card>

<x-card title="RPH Content">
    <p class="mb-5 flex items-start gap-2 rounded-md bg-slate-50 p-3 text-[15px] text-slate-600">
        <x-icon name="info" class="mt-0.5" />
        For lists, write one item per line.
    </p>
    <div class="grid gap-5 lg:grid-cols-2">
        @foreach ($sections as $key => [$label, $isList])
            <div @class(['lg:col-span-2' => in_array($key, ['teaching_activities', 'learning_objectives'])])>
                <x-form.textarea :name="'content['.$key.']'" :id="'content_'.$key" :label="$label" :value="$form[$key]"
                                 :rows="$isList ? 5 : 3" :required="$key === 'learning_objectives'"
                                 :hint="$key === 'reflection' ? 'Usually completed after the lesson.' : null" />
            </div>
        @endforeach
    </div>
</x-card>
