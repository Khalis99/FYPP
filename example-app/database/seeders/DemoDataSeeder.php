<?php

namespace Database\Seeders;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementStatus;
use App\Enums\MaterialType;
use App\Enums\UserRole;
use App\Enums\Weekday;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassTeacher;
use App\Models\RphDocument;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingMaterial;
use App\Models\Timetable;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use App\Notifications\SubmissionReceived;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Realistic demo data for presenting Pelita.
 *
 * Logins (password for all: "password"):
 *   admin@schoolhub.test  – Administrator
 *   aisyah@schoolhub.test – Teacher (Science)
 *   hakim@schoolhub.test  – Teacher (Mathematics)
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Pn. Rosnah Abdullah',
            'email' => 'admin@schoolhub.test',
            'staff_id' => '900001',
            'password' => 'password',
            'role' => UserRole::Admin,
            'phone' => '03-87654321',
        ]);

        $subjects = collect([
            ['name' => 'Mathematics', 'code' => 'MAT'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Bahasa Melayu', 'code' => 'BM'],
            ['name' => 'History', 'code' => 'SEJ'],
        ])->mapWithKeys(fn (array $subject) => [$subject['name'] => Subject::create($subject)]);

        $teachers = collect([
            ['key' => 'aisyah', 'name' => 'Nur Aisyah binti Rahman', 'email' => 'aisyah@schoolhub.test', 'staff_id' => '260001', 'department' => 'Science & Mathematics', 'subject' => 'Science'],
            ['key' => 'hakim', 'name' => 'Ahmad Hakim bin Ismail', 'email' => 'hakim@schoolhub.test', 'staff_id' => '260002', 'department' => 'Science & Mathematics', 'subject' => 'Mathematics'],
            ['key' => 'mariam', 'name' => 'Siti Mariam binti Yusof', 'email' => 'mariam@schoolhub.test', 'staff_id' => '260003', 'department' => 'Languages', 'subject' => 'English'],
            ['key' => 'faridah', 'name' => 'Faridah binti Hassan', 'email' => 'faridah@schoolhub.test', 'staff_id' => '260004', 'department' => 'Languages', 'subject' => 'Bahasa Melayu'],
        ])->mapWithKeys(function (array $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'staff_id' => $data['staff_id'],
                'password' => 'password',
                'role' => UserRole::Teacher,
                'phone' => '01'.random_int(2, 9).'-'.random_int(1000000, 9999999),
            ]);

            $teacher = $user->teacher()->create(['department' => $data['department']]);
            $teacher->setAttribute('main_subject', $data['subject']);

            return [$data['key'] => $teacher];
        });

        $classes = collect(['1 Amanah' => 1, '1 Bestari' => 1, '2 Amanah' => 2, '2 Bestari' => 2])
            ->map(fn (int $form, string $name) => SchoolClass::create(['name' => $name, 'form_level' => $form, 'academic_year' => '2026']));

        $this->seedStudents($classes);

        // Every teacher teaches their main subject to every class.
        foreach ($teachers as $teacher) {
            foreach ($classes as $class) {
                ClassTeacher::create([
                    'teacher_id' => $teacher->id,
                    'school_class_id' => $class->id,
                    'subject_id' => $subjects[$teacher->main_subject]->id,
                ]);
            }
        }

        $this->seedTimetable($teachers, $classes, $subjects);
        $this->seedMaterials($teachers, $classes, $subjects);
        $this->seedAssignments($teachers, $classes, $subjects);
        $announcements = $this->seedAnnouncements($admin);
        $this->seedRphDocuments($teachers, $classes, $subjects);

        // A few unread notifications so the bell icon has something to show.
        foreach ($teachers as $teacher) {
            $teacher->user->notify(new AnnouncementPublished($announcements->first()));
        }
        $teachers['aisyah']->user->notify(new SubmissionReceived(AssignmentSubmission::whereHas('assignment', fn ($q) => $q->where('teacher_id', $teachers['aisyah']->id))->latest('submitted_at')->first()));
    }

    /**
     * @param  Collection<string, SchoolClass>  $classes
     */
    private function seedStudents($classes): void
    {
        $names = [
            '1 Amanah' => ['Muhammad Aiman bin Zulkifli', 'Nurul Izzah binti Kamal', 'Tan Wei Ling', 'Arvind a/l Rajan', 'Siti Nur Aleesya binti Rosli', 'Danish Haikal bin Azman'],
            '1 Bestari' => ['Aina Sofea binti Hairul', 'Lim Jia Hui', 'Muhammad Irfan bin Shahrul', 'Kavitha a/p Muniandy', 'Nur Damia binti Fauzi', 'Adam Rayyan bin Hisham'],
            '2 Amanah' => ['Puteri Balqis binti Azhar', 'Wong Kar Mun', 'Ahmad Zikri bin Nordin', 'Nur Insyirah binti Salleh', 'Harith Iskandar bin Malik', 'Priya a/p Selvam'],
            '2 Bestari' => ['Amirul Hakimi bin Razak', 'Chong Mei Xin', 'Nur Qistina binti Ramli', 'Luqman Hakim bin Jamal', 'Sharvin a/l Ganesan', 'Hannah Sofia binti Idris'],
        ];

        $number = 1;

        foreach ($names as $className => $students) {
            foreach ($students as $name) {
                Student::create([
                    'school_class_id' => $classes[$className]->id,
                    'student_code' => sprintf('S2026%04d', $number++),
                    'name' => $name,
                    'email' => Str::slug(Str::before($name, ' bin'), '.').'@student.schoolhub.test',
                ]);
            }
        }
    }

    /**
     * Four periods a day. At each period every class has a different subject,
     * so no teacher, class or room is ever double-booked.
     */
    private function seedTimetable($teachers, $classes, $subjects): void
    {
        $periods = [['07:40', '08:40'], ['08:40', '09:40'], ['10:00', '11:00'], ['11:00', '12:00']];
        $teacherList = $teachers->values();
        $classList = $classes->values();
        $homeRooms = ['Room 101', 'Room 102', 'Room 201', 'Room 202'];

        foreach (Weekday::cases() as $day) {
            foreach ($periods as $periodIndex => [$start, $end]) {
                foreach ($classList as $classIndex => $class) {
                    $teacher = $teacherList[($periodIndex + $classIndex + $day->value) % $teacherList->count()];

                    Timetable::create([
                        'day' => $day,
                        'start_time' => $start,
                        'end_time' => $end,
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subjects[$teacher->main_subject]->id,
                        'school_class_id' => $class->id,
                        'room' => $teacher->main_subject === 'Science' ? 'Science Lab 1' : $homeRooms[$classIndex],
                    ]);
                }
            }
        }
    }

    private function seedMaterials($teachers, $classes, $subjects): void
    {
        $materials = [
            ['aisyah', 'Science', '1 Amanah', 'Chapter 2 Cell Structure Notes', 'Cell structure', MaterialType::Notes, 'Summary notes on plant and animal cells with labelled diagrams.', 12],
            ['aisyah', 'Science', '1 Amanah', 'Photosynthesis Slides', 'Photosynthesis', MaterialType::Slides, 'Lesson slides introducing the process of photosynthesis.', 8],
            ['aisyah', 'Science', '2 Amanah', 'States of Matter Worksheet', 'Matter', MaterialType::Worksheet, 'Practice questions on solids, liquids and gases.', 5],
            ['aisyah', 'Science', null, 'Science Lab Safety Guide', 'Laboratory safety', MaterialType::Reference, 'Lab rules and safety symbols for all classes.', 20],
            ['hakim', 'Mathematics', '1 Amanah', 'Chapter 3 Algebra Notes', 'Algebraic expressions', MaterialType::Notes, 'Notes on terms, coefficients and simplifying expressions.', 15],
            ['hakim', 'Mathematics', '1 Bestari', 'Fractions Worksheet', 'Fractions', MaterialType::Worksheet, 'Addition and subtraction of fractions.', 9],
            ['hakim', 'Mathematics', '2 Bestari', 'Linear Equations Slides', 'Linear equations', MaterialType::Slides, 'Solving linear equations in one variable.', 3],
        ];

        foreach ($materials as [$teacherKey, $subject, $className, $title, $topic, $type, $description, $daysAgo]) {
            $fileName = Str::slug($title).'.pdf';
            $path = 'materials/'.Str::random(20).'.pdf';
            Storage::disk('local')->put($path, $this->makePdf($title, $description));

            $material = TeachingMaterial::create([
                'teacher_id' => $teachers[$teacherKey]->id,
                'subject_id' => $subjects[$subject]->id,
                'school_class_id' => $className ? $classes[$className]->id : null,
                'title' => $title,
                'topic' => $topic,
                'type' => $type,
                'description' => $description,
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => Storage::disk('local')->size($path),
            ]);
            $material->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->save();
        }
    }

    /**
     * Assignments with a mix of upcoming / past due dates and on-time / late / missing work.
     */
    private function seedAssignments($teachers, $classes, $subjects): void
    {
        $assignments = [
            // teacher, subject, class, title, due in days, [on time, late] submissions
            ['aisyah', 'Science', '1 Amanah', 'Photosynthesis Worksheet', 3, [3, 0]],
            ['aisyah', 'Science', '1 Bestari', 'Cell Structure Diagram', -5, [4, 1]],
            ['aisyah', 'Science', '2 Amanah', 'Lab Report: States of Matter', 7, [1, 0]],
            ['aisyah', 'Science', '2 Bestari', 'Microorganisms Quiz Corrections', -10, [4, 2]],
            ['hakim', 'Mathematics', '1 Amanah', 'Mathematics Exercise 3: Algebra', 2, [4, 0]],
            ['hakim', 'Mathematics', '1 Bestari', 'Fractions Practice', -3, [3, 1]],
            ['hakim', 'Mathematics', '2 Bestari', 'Linear Equations Project', 10, [0, 0]],
        ];

        foreach ($assignments as [$teacherKey, $subject, $className, $title, $dueInDays, [$onTime, $late]]) {
            $dueAt = now()->addDays($dueInDays)->setTime(23, 59);

            $assignment = Assignment::create([
                'teacher_id' => $teachers[$teacherKey]->id,
                'subject_id' => $subjects[$subject]->id,
                'school_class_id' => $classes[$className]->id,
                'title' => $title,
                'description' => "Complete the {$title} based on what we learnt in class.",
                'instructions' => 'Write your name and class at the top. Hand in your work on paper in class or send a clear photo/PDF.',
                'due_at' => $dueAt,
            ]);
            $assignment->forceFill(['created_at' => $dueAt->copy()->subDays(7)])->save();

            $students = $classes[$className]->students()->orderBy('id')->get();

            foreach ($students->take($onTime + $late) as $index => $student) {
                $submittedAt = $index < $onTime
                    ? min($dueAt->copy()->subDays(random_int(1, 3))->setTime(random_int(8, 20), random_int(0, 59)), now()->subHour())
                    : $dueAt->copy()->addDays(random_int(1, 2))->setTime(random_int(8, 16), 0);

                $path = null;
                if ($index === 0) {
                    $path = 'submissions/'.Str::random(20).'.pdf';
                    Storage::disk('local')->put($path, $this->makePdf($title, 'Submitted by '.$student->name));
                }

                AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'submitted_at' => $submittedAt,
                    'file_path' => $path,
                    'file_name' => $path ? Str::slug($student->name.' '.$title).'.pdf' : null,
                ]);
            }
        }
    }

    /**
     * @return Collection<int, Announcement>
     */
    private function seedAnnouncements(User $admin)
    {
        $items = [
            ['Mesyuarat Staf Hari Jumaat Ini', "Semua guru diingatkan untuk menghadiri mesyuarat staf pada hari Jumaat ini jam 2:30 petang di Bilik Mesyuarat.\n\nAgenda:\n1. Persediaan peperiksaan akhir tahun\n2. Penghantaran data PBD\n3. Aktiviti kokurikulum", AnnouncementAudience::Teachers, 1],
            ['Tarikh Akhir Penghantaran Data PBD', 'Sila pastikan semua rekod Pentaksiran Bilik Darjah (PBD) bagi Penggal 2 dikemas kini sebelum 31 Oktober 2026. Hubungi Unit Pentaksiran jika anda memerlukan bantuan.', AnnouncementAudience::Teachers, 3],
            ['Hari Sukan Sekolah 2026', 'Hari Sukan akan diadakan pada 15 November 2026 di padang sekolah. Kelas akan tamat pada pukul 11:00 pagi pada hari sebelumnya untuk persediaan.', AnnouncementAudience::All, 6],
            ['Jadual Peperiksaan Akhir Tahun Diumumkan', 'Jadual peperiksaan akhir tahun bagi Tingkatan 1 dan Tingkatan 2 telah diumumkan. Sila maklumkan kepada murid anda dan ingatkan mereka untuk mula mengulang kaji.', AnnouncementAudience::All, 9],
        ];

        $announcements = collect($items)->map(fn (array $item) => Announcement::create([
            'user_id' => $admin->id,
            'title' => $item[0],
            'body' => $item[1],
            'audience' => $item[2],
            'status' => AnnouncementStatus::Published,
            'published_at' => now()->subDays($item[3]),
        ]));

        Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Persediaan Hari Bertemu Pelanggan',
            'body' => 'Draf: butiran mengenai Hari Bertemu Pelanggan akan dikongsi tidak lama lagi.',
            'audience' => AnnouncementAudience::Teachers,
            'status' => AnnouncementStatus::Draft,
        ]);

        return $announcements;
    }

    private function seedRphDocuments($teachers, $classes, $subjects): void
    {
        $documents = [
            ['aisyah', 'Science', '1 Amanah', 'Photosynthesis', 2, [
                'learning_objectives' => ['Students can state the raw materials and products of photosynthesis.', 'Students can explain the basic process of photosynthesis.'],
                'learning_standards' => ['2.2.1 Explain the process of photosynthesis'],
                'previous_knowledge' => 'Students know that green plants need sunlight and water to live.',
                'teaching_aids' => ['Textbook', 'Potted plant and leaf samples', 'Short video on photosynthesis', 'Worksheet'],
                'teaching_activities' => ['Set induction (5 min): Show a potted plant kept in the dark and one kept in sunlight; students compare.', 'Development (40 min): Explain the photosynthesis word equation, then groups complete a flow diagram.', 'Closure (15 min): Groups present; teacher summarises the key points.'],
                'teacher_activities' => ['Asks why the plant in the dark looks weak.', 'Explains raw materials, conditions and products.', 'Guides groups and checks their flow diagrams.', 'Summarises the lesson.'],
                'student_activities' => ['Compare the two plants and share observations.', 'Copy the word equation into their notes.', 'Complete the flow diagram in groups.', 'Present their group answer.'],
                'assessment' => 'Oral questions during the lesson and a 3-question exit ticket on the word equation.',
                'closure' => 'Recap the word equation and link to the next lesson on factors affecting photosynthesis.',
                'reflection' => '',
            ]],
            ['aisyah', 'Science', '1 Bestari', 'Cell Structure', -3, [
                'learning_objectives' => ['Students can label the main parts of plant and animal cells.', 'Students can compare plant and animal cells.'],
                'learning_standards' => ['2.1.2 Compare and contrast animal and plant cells'],
                'previous_knowledge' => 'Students know that living things are made of cells.',
                'teaching_aids' => ['Microscope', 'Onion and cheek cell slides', 'Cell diagram chart'],
                'teaching_activities' => ['Set induction (5 min): Show magnified images of cells.', 'Development (45 min): Students observe slides under the microscope and draw what they see.', 'Closure (10 min): Complete a comparison table as a class.'],
                'teacher_activities' => ['Demonstrates how to use the microscope safely.', 'Moves around to help each group.'],
                'student_activities' => ['Observe the slides and draw the cells.', 'Label the parts using the chart.'],
                'assessment' => 'Check the labelled drawings and the comparison table.',
                'closure' => 'Students state one similarity and one difference between plant and animal cells.',
                'reflection' => '24/30 students could label all parts correctly. Weaker students need more time with the microscope.',
            ]],
            ['aisyah', 'Science', '2 Amanah', 'States of Matter', 5, [
                'learning_objectives' => ['Students can describe the arrangement of particles in solids, liquids and gases.'],
                'learning_standards' => ['5.1.1 Explain the kinetic theory of matter'],
                'previous_knowledge' => 'Students can give examples of solids, liquids and gases.',
                'teaching_aids' => ['Marbles in a tray', 'Ice, water and kettle', 'Worksheet'],
                'teaching_activities' => ['Set induction (5 min): Melt an ice cube in front of the class.', 'Development (40 min): Model particle arrangement using marbles in a tray.', 'Closure (15 min): Worksheet and discussion.'],
                'teacher_activities' => ['Leads the ice demonstration.', 'Explains particle arrangement using the marble model.'],
                'student_activities' => ['Predict what happens to the ice.', 'Arrange marbles to show each state of matter.'],
                'assessment' => 'Worksheet questions 1 to 5.',
                'closure' => 'Students summarise the three states in a table.',
                'reflection' => '',
            ]],
            ['hakim', 'Mathematics', '1 Amanah', 'Algebraic Expressions', 1, [
                'learning_objectives' => ['Students can identify terms, coefficients and variables.', 'Students can simplify algebraic expressions by combining like terms.'],
                'learning_standards' => ['5.1.1 Use letters to represent quantities with unknown values'],
                'previous_knowledge' => 'Students can perform addition and subtraction of integers.',
                'teaching_aids' => ['Algebra tiles', 'Textbook Chapter 5', 'Whiteboard'],
                'teaching_activities' => ['Set induction (5 min): A "think of a number" puzzle.', 'Development (45 min): Introduce terms and like terms with algebra tiles, followed by guided practice.', 'Closure (10 min): Quick quiz on the whiteboard.'],
                'teacher_activities' => ['Presents the puzzle and explains how letters represent unknowns.', 'Demonstrates combining like terms with algebra tiles.'],
                'student_activities' => ['Solve the puzzle.', 'Use algebra tiles in pairs to simplify expressions.'],
                'assessment' => 'Exercise 5.1 questions 1–10.',
                'closure' => 'Students explain what "like terms" means in their own words.',
                'reflection' => '',
            ]],
        ];

        foreach ($documents as [$teacherKey, $subject, $className, $topic, $dayOffset, $content]) {
            RphDocument::create([
                'teacher_id' => $teachers[$teacherKey]->id,
                'subject_id' => $subjects[$subject]->id,
                'school_class_id' => $classes[$className]->id,
                'title' => "RPH: {$topic} ({$className})",
                'topic' => $topic,
                'theme' => $subject === 'Science' ? 'Sains Hayat' : 'Nombor dan Operasi',
                'unit' => $subject === 'Science' ? '2' : '5',
                'lesson_date' => $lessonDate = now()->addDays($dayOffset),
                'week' => $lessonDate->weekOfYear,
                'start_time' => '08:40',
                'duration_minutes' => 60,
                'student_count' => $classes[$className]->students()->count(),
                'content' => $content,
                'is_ai_generated' => true,
                'ai_provider' => 'demo',
            ]);
        }
    }

    /**
     * Build a small, valid one-page PDF so seeded files can be downloaded and opened.
     */
    private function makePdf(string $title, string $body): string
    {
        $escape = fn (string $text): string => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 20 Tf 60 770 Td ({$escape($title)}) Tj ET\n"
            ."BT /F1 12 Tf 60 740 Td ({$escape($body)}) Tj ET\n"
            .'BT /F1 10 Tf 60 60 Td (Pelita demo file) Tj ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
