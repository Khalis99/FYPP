{{-- Shared by create and edit. Expects $assignment, $subjectOptions, $classOptions --}}
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.input name="title" label="Assignment title" :value="$assignment->title" placeholder="e.g. Mathematics Exercise 3" required />
    </div>
    <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" :value="$assignment->subject_id" placeholder="Choose a subject" required />
    <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$assignment->school_class_id" placeholder="Choose a class" required />
    <x-form.input name="due_at" type="datetime-local" label="Due date and time" :value="$assignment->due_at?->format('Y-m-d\TH:i')" required />
    <div class="sm:col-span-2">
        <x-form.textarea name="description" label="Description" :value="$assignment->description" rows="3" />
    </div>
    <div class="sm:col-span-2">
        <x-form.textarea name="instructions" label="Instructions for students" :value="$assignment->instructions" rows="4" hint="For example: which pages to complete and how to hand in the work." />
    </div>
    <div class="sm:col-span-2">
        <x-form.file name="attachment" :label="$assignment->attachment_path ? __('Replace attachment (optional)') : __('Attachment (optional)')" :current="$assignment->attachment_name" />
    </div>
</div>
