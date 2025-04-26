<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<Report>
 */
class ReportRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct($manager, $manager->getClassMetadata(Report::class));
    }

    public function save(Report $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);

        if ($flush) {
            $em->flush();
        }
    }
}
