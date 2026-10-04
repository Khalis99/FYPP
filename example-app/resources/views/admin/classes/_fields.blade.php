{{-- Expects $schoolClass --}}
<x-form.input name="name" label="Class name" :value="$schoolClass->name" placeholder="e.g. 1 Amanah" required />
<x-form.select name="form_level" label="Form" :options="[1 => 'Form 1', 2 => 'Form 2', 3 => 'Form 3', 4 => 'Form 4', 5 => 'Form 5', 6 => 'Form 6']" :value="$schoolClass->form_level" placeholder="Choose" required />
<x-form.input name="academic_year" label="Academic year" :value="$schoolClass->academic_year" placeholder="e.g. 2026" inputmode="numeric" maxlength="4" required />
