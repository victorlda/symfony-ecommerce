<?php

declare(strict_types=1);

namespace App\Identity\Repository;

use App\Identity\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function findOneByHash(string $hash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $hash]);
    }

    /**
     * Busca o token travando a linha no banco até o fim da transação.
     */
    public function findOneByHashForUpdate(string $hash): ?RefreshToken
    {
        $token = $this->createQueryBuilder('t')
            ->andWhere('t.tokenHash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();

        return $token instanceof RefreshToken ? $token : null;
    }

    public function revokeFamily(Uuid $familyId, \DateTimeImmutable $now): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(RefreshToken::class, 't')
            ->set('t.revokedAt', ':now')
            ->where('t.familyId = :family')
            ->andWhere('t.revokedAt IS NULL')
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->setParameter('family', $familyId, UuidType::NAME)
            ->getQuery()
            ->execute();
    }
}
