<x-layouts.app title="RPH Generator">
    <x-page-header title="AI RPH Generator" description="Tell us about your lesson in a few words. A draft RPH will be prepared for you to review and edit.">
        <x-slot:actions>
            <a href="{{ route('teacher.rph.index') }}" class="btn btn-secondary"><x-icon name="document" /> My RPH</a>
        </x-slot:actions>
    </x-page-header>

    <x-ai-notice class="mb-6" />

    @php
        $optionalFields = ['learning_objectives', 'learning_standards', 'previous_knowledge', 'teaching_aids', 'teaching_method', 'theme', 'unit', 'week', 'start_time', 'student_count'];
        $showOptions = collect($inputs)->only($optionalFields)->filter()->isNotEmpty() || $errors->hasAny($optionalFields);
    @endphp

    @if (empty($subjectOptions))
        <div class="card"><x-empty-state icon="classes" title="No classes assigned" message="You need to be assigned to a class before you can prepare an RPH. Please contact the administrator." /></div>
    @else
        <form method="POST" action="{{ route('teacher.rph.generate') }}" class="space-y-6">
            @csrf

            <x-card title="Your Lesson">
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.select name="subject_id" label="Subject (Mata Pelajaran)" :options="$subjectOptions" :value="$inputs['subject_id'] ?? null" placeholder="Choose a subject" required />
                    <x-form.select name="school_class_id" label="Class (Kelas)" :options="$classOptions" :value="$inputs['school_class_id'] ?? null" placeholder="Choose a class" required />
                    <x-form.select name="language" label="RPH language" :options="$languageOptions" :value="$inputs['language'] ?? 'ms'" required />
                    <x-form.input name="lesson_date" type="date" label="Date (Tarikh)" :value="$inputs['lesson_date'] ?? now()->toDateString()" required />
                    <x-form.input name="duration_minutes" type="number" label="Duration in minutes" :value="$inputs['duration_minutes'] ?? 60" min="10" max="300" required />
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-form.input name="topic" label="What will you teach? (Tajuk)" :value="$inputs['topic'] ?? null" required
                                      placeholder="e.g. Photosynthesis: how plants make food" hint="The topic is enough. The AI will suggest the objectives, activities and assessment." />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-form.textarea name="additional_instructions" label="Anything else the AI should know? (optional)" :value="$inputs['additional_instructions'] ?? null" rows="2"
                                         placeholder="e.g. Include a group activity. Students are weaker in English." />
                    </div>
                </div>
            </x-card>

            <details class="card" @if ($showOptions) open @endif>
                <summary class="cursor-pointer px-5 py-4 text-lg font-semibold text-slate-900">
                    More options <span class="ml-2 text-sm font-normal text-slate-500">(optional: add your own objectives, standards and more)</span>
                </summary>
                <div class="space-y-5 border-t border-slate-100 p-5">
                    <div class="grid gap-5 lg:grid-cols-2">
                        <x-form.textarea name="learning_objectives" label="Learning objectives" :value="$inputs['learning_objectives'] ?? null" rows="4"
                                         placeholder="e.g. Students can explain the basic process of photosynthesis." hint="Leave blank and the AI will suggest them. One objective per line." />
                        <x-form.textarea name="learning_standards" label="Learning standards" :value="$inputs['learning_standards'] ?? null" rows="4"
                                         placeholder="e.g. 2.1.1 Explain the process of photosynthesis" hint="From the DSKP. One per line." />
                        <x-form.textarea name="previous_knowledge" label="Previous knowledge" :value="$inputs['previous_knowledge'] ?? null" rows="3"
                                         placeholder="e.g. Students know that plants need sunlight and water." />
                        <x-form.textarea name="teaching_aids" label="Teaching aids / resources" :value="$inputs['teaching_aids'] ?? null" rows="3"
                                         placeholder="e.g. Textbook, leaf samples, video" hint="One per line." />
                        <div>
                            <x-form.input name="teaching_method" label="Teaching method" :value="$inputs['teaching_method'] ?? null" list="teaching-methods"
                                          placeholder="e.g. Inquiry-based learning" />
                            <datalist id="teaching-methods">
                                <option value="Inquiry-based learning">
                                <option value="Cooperative learning">
                                <option value="Project-based learning">
                                <option value="Direct instruction with guided practice">
                                <option value="Problem-based learning">
                                <option value="Experiential learning">
                            </datalist>
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                        <x-form.input name="theme" label="Theme (Tema)" :value="$inputs['theme'] ?? null" placeholder="e.g. Sains Hayat" />
                        <x-form.input name="unit" label="Unit" :value="$inputs['unit'] ?? null" placeholder="e.g. 5" />
                        <x-form.input name="week" type="number" label="Week (Minggu)" :value="$inputs['week'] ?? null" min="1" max="53" />
                        <x-form.input name="start_time" type="time" label="Start time (Masa)" :value="$inputs['start_time'] ?? null" />
                        <x-form.input name="student_count" type="number" label="Number of students (Bil. Murid)" :value="$inputs['student_count'] ?? null" min="1" max="100" />
                    </div>
                </div>
            </details>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn btn-primary px-6 py-3 text-base" data-loading-text="Generating your RPH… please wait">
                    <x-icon name="sparkles" /> Generate RPH
                </button>
            </div>
        </form>
    @endif
</x-layouts.app>
