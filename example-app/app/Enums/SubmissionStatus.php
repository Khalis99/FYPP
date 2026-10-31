<?php

namespace App\Enums;

/**
 * Submission status is derived (not stored): a student either has a submission
 * row before the due date, after the due date, or no row at all.
 */
enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case Late = 'late';
    case NotSubmitted = 'not_submitted';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => __('Submitted'),
            self::Late => __('Late'),
            self::NotSubmitted => __('Not Submitted'),
        };
    }

    /**
     * Colour name used by the <x-badge> component.
     */
    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'green',
            self::Late => 'amber',
            self::NotSubmitted => 'red',
        };
    }
}
