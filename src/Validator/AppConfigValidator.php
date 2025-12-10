<?php

declare(strict_types=1);

namespace Marshal\Application\Validator;

use Laminas\Validator\AbstractValidator;

final class AppConfigValidator extends AbstractValidator
{
    public function __construct(private array $config)
    {
    }

    public function isValid(mixed $value): bool
    {
        return TRUE;
    }
}
