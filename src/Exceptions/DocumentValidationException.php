<?php

declare(strict_types=1);

namespace DocumentValidation\Exceptions;

use Throwable;

/** Lets callers catch every exception this library throws as one type. */
interface DocumentValidationException extends Throwable
{
}
