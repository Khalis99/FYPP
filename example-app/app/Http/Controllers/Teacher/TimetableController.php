<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function __invoke(Request $request): View
    {
        $entries = $this->currentTeacher($request)->timetables()
            ->with(['subject', 'schoolClass'])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($entry) => $entry->day->value);

        return view('teacher.timetable', [
            'days' => Weekday::cases(),
            'entries' => $entries,
            'today' => Weekday::fromDate(now()),
        ]);
    }
}
