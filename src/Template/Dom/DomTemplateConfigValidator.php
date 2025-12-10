<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Dom;

use Laminas\Validator\AbstractValidator;

final class TemplateConfigValidator extends AbstractValidator
{
    private const string TEMPLATE_CONFIG_NOT_FOUND = "templateConfigNotFound";
    private array $messages = [
        self::TEMPLATE_CONFIG_NOT_FOUND => "Template %value% not found in config",
    ];

    public function __construct(private array $config)
    {
    }

    public function isValid(mixed $value): bool
    {
        if (! \is_string($value)) {
            $this->setValue(\get_debug_type($value));
            return FALSE;
        }

        if (! isset($this->config[$value])) {
            $this->setValue($value);
            $this->error(self::TEMPLATE_CONFIG_NOT_FOUND);
            return FALSE;
        }

        return TRUE;
    }
}
