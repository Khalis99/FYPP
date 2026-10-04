<?php

namespace App\Services\Ai;

use RuntimeException;

/**
 * Thrown when an AI provider fails or returns content we cannot use.
 * The message is safe to show to the teacher.
 */
class AiGenerationException extends RuntimeException
{
    //
}
