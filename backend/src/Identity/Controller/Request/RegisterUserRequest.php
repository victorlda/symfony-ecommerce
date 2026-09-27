<?php

declare(strict_types=1);

namespace App\Identity\Controller\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 120)]
        public string $name = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
        public string $password = '',
    ) {
    }
}
