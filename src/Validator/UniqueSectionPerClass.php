<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class UniqueSectionPerClass extends Constraint
{
    public string $message;

    public function __construct(
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        $this->message = $message ?? 'This section name already exists in this class.';

        // ✅ Important: tell Symfony this is a class-level constraint
        parent::__construct([
            'message' => $this->message,
        ], $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
