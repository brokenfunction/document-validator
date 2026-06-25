<?php

declare(strict_types=1);

namespace DocumentValidation\Exceptions;

use InvalidArgumentException;

final class InvalidDocument extends InvalidArgumentException implements DocumentValidationException
{
}
