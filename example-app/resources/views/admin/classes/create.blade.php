<x-layouts.app title="New Class">
    <x-page-header title="New Class" :back="route('admin.classes.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-6">
            @csrf
            <div class="grid gap-5 sm:grid-cols-3">
                @include('admin.classes._fields')
            </div>

            <fieldset class="rounded-md border border-slate-200 p-4">
                <legend class="px-1 text-[15px] font-semibold">Assign a subject teacher (optional)</legend>
                <p class="mb-4 text-sm text-slate-600">You can add more subjects and teachers after the class is created.</p>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" placeholder="None" />
                    <x-form.select name="teacher_id" label="Teacher" :options="$teacherOptions" placeholder="None" />
                </div>
            </fieldset>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Create Class</button>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
