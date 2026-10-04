{{-- Expects $student and $classOptions --}}
<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="name" label="Full name" :value="$student->name" required />
    <x-form.input name="student_code" label="Student ID" :value="$student->student_code" placeholder="e.g. S20260011" required />
    <x-form.input name="email" type="email" label="Email" :value="$student->email" />
    <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$student->school_class_id" placeholder="No class" />
    <x-form.select name="is_active" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$student->is_active ? '1' : '0'" required />
</div>
