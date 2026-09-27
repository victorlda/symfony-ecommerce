<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Entity\RefreshToken;
use App\Identity\Entity\User;
use App\Identity\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Uid\Uuid;

final readonly class RefreshTokenService
{
    private const TTL = 'P14D';

    public function __construct(
        private RefreshTokenRepository $tokens,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Inicia uma nova sessão (nova família de tokens).
     *
     * @return array{0: string, 1: \DateTimeImmutable}
     */
    public function issue(User $user): array
    {
        return $this->create($user, Uuid::v7());
    }

    /**
     * Troca um refresh token válido por um novo, invalidando o anterior.
     *
     * @return array{user: User, token: string, expiresAt: \DateTimeImmutable}
     */
    public function rotate(string $plainToken): array
    {
        $result = $this->entityManager->wrapInTransaction(function () use ($plainToken): ?array {
            $now = $this->clock->now();
            $current = $this->tokens->findOneByHashForUpdate(self::hash($plainToken));

            if (null === $current) {
                return null;
            }

            if ($current->isRevoked()) {
                // Um token já rotacionado foi reapresentado: sinal de que foi copiado.
                // Revogamos a sessão inteira, derrubando o atacante e o usuário.
                $this->tokens->revokeFamily($current->getFamilyId(), $now);

                return null;
            }

            if ($current->isExpiredAt($now)) {
                return null;
            }

            $current->revoke($now);
            [$token, $expiresAt] = $this->create($current->getUser(), $current->getFamilyId());

            return ['user' => $current->getUser(), 'token' => $token, 'expiresAt' => $expiresAt];
        });

        // A exceção é lançada fora da transação para que a revogação da família seja gravada.
        return $result ?? throw new InvalidRefreshToken();
    }

    /**
     * Encerra a sessão à qual o token pertence.
     */
    public function revoke(string $plainToken): void
    {
        $token = $this->tokens->findOneByHash(self::hash($plainToken));

        if (null !== $token) {
            $this->tokens->revokeFamily($token->getFamilyId(), $this->clock->now());
        }
    }

    /**
     * @return array{0: string, 1: \DateTimeImmutable}
     */
    private function create(User $user, Uuid $familyId): array
    {
        $plainToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $now = $this->clock->now();
        $expiresAt = $now->add(new \DateInterval(self::TTL));

        $this->tokens->save(new RefreshToken($user, self::hash($plainToken), $familyId, $now, $expiresAt));

        return [$plainToken, $expiresAt];
    }

    private static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
