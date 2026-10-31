{{-- Shared by create and edit. Expects $material, $subjectOptions, $classOptions, $typeOptions --}}
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.input name="title" label="Title" :value="$material->title" placeholder="e.g. Chapter 3 Algebra Notes" required />
    </div>
    <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" :value="$material->subject_id" placeholder="Choose a subject" required />
    <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$material->school_class_id" placeholder="All my classes" hint="Leave as 'All my classes' if the material is for every class." />
    <x-form.input name="topic" label="Topic" :value="$material->topic" placeholder="e.g. Linear equations" />
    <x-form.select name="type" label="Material type" :options="$typeOptions" :value="$material->type" placeholder="Choose a type" required />
    <div class="sm:col-span-2">
        <x-form.textarea name="description" label="Description" :value="$material->description" rows="3" />
    </div>
    <div class="sm:col-span-2">
        <x-form.file name="file" :label="$material->exists ? __('Replace file (optional)') : __('File')" :required="! $material->exists" :current="$material->file_name" />
    </div>
</div>
