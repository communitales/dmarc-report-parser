<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Component\System\Check;

use Communitales\Component\Log\LogAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function is_array;
use function sprintf;

use const PHP_EOL;

/**
 * Class DatabaseChecker
 */
class DatabaseChecker implements LoggerAwareInterface
{
    use LogAwareTrait;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function check(SymfonyStyle $io, bool $verbose): bool
    {
        $io->section('Check database connection');

        $connection = $this->entityManager->getConnection();
        $valid = $this->checkConnection($io, $connection, $verbose);
        if ($verbose) {
            $io->writeln(PHP_EOL.PHP_EOL);
        }

        return $valid;
    }

    private function checkConnection(SymfonyStyle $io, Connection $connection, bool $verbose): bool
    {
        try {
            $nativeConnection = $connection->getNativeConnection();
        } catch (Throwable $throwable) {
            $io->writeln('❌ Error');
            $io->comment($throwable->getMessage());

            return false;
        }

        if ($verbose && $nativeConnection instanceof PDO) {
            $clientVersion = $nativeConnection->getAttribute(PDO::ATTR_CLIENT_VERSION);
            if (is_array($clientVersion)) {
                foreach ($clientVersion as $key => $value) {
                    $io->comment(sprintf('%s: %s', $key, (string)$value));
                }
            } else {
                $io->comment(sprintf('ClientVersion: %s', (string)$clientVersion));
            }
        }

        try {
            $connected = $connection->isConnected();
            if (!$connected) {
                try {
                    $connection->executeQuery('SHOW TABLES');
                    $connected = true;
                } catch (Throwable) {
                    $connected = false;
                }
            }

            if ($verbose) {
                $io->comment(sprintf('Connection: %s', $connected ? 'Success' : '❌ Error'));
            } else {
                $io->writeln($connected ? '✅ <fg=green;bg=default>OK</>' : '❌ <fg=red;bg=default>Error</>');
            }

            if ($connected === false) {
                return false;
            }
        } catch (Throwable $throwable) {
            $this->logException($throwable);
            if ($verbose) {
                $io->comment('Verbindung: ❌ Fehler');
            } else {
                $io->writeln('❌ <fg=red;bg=default>Fehler</>');
            }

            return false;
        }

        if ($verbose) {
            $io->writeln('✅ OK');
        }

        return true;
    }
}
