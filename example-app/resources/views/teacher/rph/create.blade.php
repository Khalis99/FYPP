<x-layouts.app title="RPH Generator">
    <x-page-header title="AI RPH Generator" description="Fill in your lesson details. A draft RPH will be prepared for you to review and edit.">
        <x-slot:actions>
            <a href="{{ route('teacher.rph.index') }}" class="btn btn-secondary"><x-icon name="document" /> My RPH</a>
        </x-slot:actions>
    </x-page-header>

    <x-ai-notice class="mb-6" />

    @if (empty($subjectOptions))
        <div class="card"><x-empty-state icon="classes" title="No classes assigned" message="You need to be assigned to a class before you can prepare an RPH. Please contact the administrator." /></div>
    @else
        <form method="POST" action="{{ route('teacher.rph.generate') }}" class="space-y-6">
            @csrf

            <x-card title="1. Lesson Details">
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.select name="language" label="RPH language" :options="$languageOptions" :value="$inputs['language'] ?? 'ms'" required
                                   hint="The language of the generated content." />
                    @include('teacher.rph._header-fields', ['values' => $inputs])
                </div>
            </x-card>

            <x-card title="2. Learning Goals">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-form.textarea name="learning_objectives" label="Learning objectives" :value="$inputs['learning_objectives'] ?? null" rows="4" required
                                     placeholder="e.g. Students can explain the basic process of photosynthesis." hint="One objective per line." />
                    <x-form.textarea name="learning_standards" label="Learning standards" :value="$inputs['learning_standards'] ?? null" rows="4"
                                     placeholder="e.g. 2.1.1 Explain the process of photosynthesis" hint="From the DSKP. One per line." />
                </div>
            </x-card>

            <x-card title="3. Teaching Approach">
                <div class="grid gap-5 lg:grid-cols-2">
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
                    <x-form.textarea name="additional_instructions" label="Additional instructions" :value="$inputs['additional_instructions'] ?? null" rows="3"
                                     placeholder="e.g. Include a group activity. Students are weaker in English." />
                </div>
            </x-card>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn btn-primary px-6 py-3 text-base" data-loading-text="Generating your RPH… please wait">
                    <x-icon name="sparkles" /> Generate RPH
                </button>
            </div>
        </form>
    @endif
</x-layouts.app>
