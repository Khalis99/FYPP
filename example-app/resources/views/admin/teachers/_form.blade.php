{{-- Expects $teacher and $user --}}
<div class="grid gap-5 sm:grid-cols-2">
    @include('admin.users._account-fields')
    <x-form.input name="department" label="Department" :value="$teacher->department" placeholder="e.g. Science & Mathematics" />
</div>
