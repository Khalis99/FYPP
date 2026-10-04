<x-layouts.app title="Subjects">
    <x-page-header title="Subjects" description="Subjects that can be assigned to classes." :back="route('admin.classes.index')" />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card :padding="false" class="lg:col-span-2">
            <x-table>
                <thead><tr><th>Subject</th><th>Code</th><th>Classes</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        <tr>
                            <td colspan="2">
                                <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="flex flex-wrap gap-2" id="subject-{{ $subject->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $subject->name }}" class="form-input max-w-60 py-1.5" aria-label="Subject name" required>
                                    <input type="text" name="code" value="{{ $subject->code }}" class="form-input max-w-28 py-1.5" aria-label="Subject code" required>
                                </form>
                            </td>
                            <td>{{ $subject->teaching_loads_count }}</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex gap-3">
                                    <button type="submit" form="subject-{{ $subject->id }}" class="link">Save</button>
                                    <x-delete-button :action="route('admin.subjects.destroy', $subject)" message="Delete this subject?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">No subjects yet.</td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>

        <x-card title="Add Subject">
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-4">
                @csrf
                <x-form.input name="name" label="Subject name" placeholder="e.g. Geography" required />
                <x-form.input name="code" label="Code" placeholder="e.g. GEO" required />
                <button type="submit" class="btn btn-primary w-full"><x-icon name="plus" /> Add Subject</button>
            </form>
        </x-card>
    </div>
</x-layouts.app>
