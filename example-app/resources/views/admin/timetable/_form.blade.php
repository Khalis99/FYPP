{{-- Expects $timetable and option arrays --}}
<div class="grid gap-5 sm:grid-cols-3">
    <x-form.select name="day" label="Day" :options="$dayOptions" :value="$timetable->day" required />
    <x-form.input name="start_time" type="time" label="Start time" :value="$timetable->start_time ? substr($timetable->start_time, 0, 5) : '08:00'" required />
    <x-form.input name="end_time" type="time" label="End time" :value="$timetable->end_time ? substr($timetable->end_time, 0, 5) : '09:00'" required />
    <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$timetable->school_class_id" placeholder="Choose a class" required />
    <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" :value="$timetable->subject_id" placeholder="Choose a subject" required />
    <x-form.select name="teacher_id" label="Teacher" :options="$teacherOptions" :value="$timetable->teacher_id" placeholder="Choose a teacher" required />
    <x-form.input name="room" label="Room" :value="$timetable->room" placeholder="e.g. Room 201" />
</div>
<p class="mt-4 flex items-start gap-2 text-sm text-slate-600">
    <x-icon name="info" class="mt-0.5 size-4" />
    The teacher must already be assigned to the subject in that class. Clashes for the same teacher, class or room are blocked.
</p>
