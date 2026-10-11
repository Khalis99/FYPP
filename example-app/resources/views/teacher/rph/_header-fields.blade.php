{{--
    RPH header fields matching the KPM template boxes (Mata Pelajaran, Kelas, Tajuk, Tema, Unit,
    Tarikh, Minggu, Masa, Bil. Murid). Shared by the generator form and the editor.
    Expects $values (array), $subjectOptions, $classOptions.
--}}
<x-form.select name="subject_id" label="Subject (Mata Pelajaran)" :options="$subjectOptions" :value="$values['subject_id'] ?? null" placeholder="Choose a subject" required />
<x-form.select name="school_class_id" label="Class (Kelas)" :options="$classOptions" :value="$values['school_class_id'] ?? null" placeholder="Choose a class" required />
<x-form.input name="topic" label="Topic (Tajuk)" :value="$values['topic'] ?? null" placeholder="e.g. 5.0 Tumbuhan" required />
<x-form.input name="theme" label="Theme (Tema)" :value="$values['theme'] ?? null" placeholder="e.g. Sains Hayat" />
<x-form.input name="unit" label="Unit" :value="$values['unit'] ?? null" placeholder="e.g. 5" />
<x-form.input name="lesson_date" type="date" label="Date (Tarikh)" :value="$values['lesson_date'] ?? now()->toDateString()" required />
<x-form.input name="week" type="number" label="Week (Minggu)" :value="$values['week'] ?? null" min="1" max="53" hint="School week number." />
<x-form.input name="start_time" type="time" label="Start time (Masa)" :value="$values['start_time'] ?? null" />
<x-form.input name="duration_minutes" type="number" label="Duration in minutes" :value="$values['duration_minutes'] ?? 60" min="10" max="300" required />
<x-form.input name="student_count" type="number" label="Number of students (Bil. Murid)" :value="$values['student_count'] ?? null" min="1" max="100" />
