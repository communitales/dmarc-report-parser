<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Component\System\Check;

use Communitales\Component\Log\LogAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Folder;

use function sprintf;

/**
 * Class ImapChecker
 */
class ImapChecker implements LoggerAwareInterface
{
    use LogAwareTrait;

    public function __construct(private readonly ClientManager $imapClient, private readonly string $imapReadFolder, private readonly string $imapMoveFolder)
    {
    }

    public function getName(): string
    {
        return 'IMAP';
    }

    public function check(SymfonyStyle $io): bool
    {
        $io->section('Check IMAP connection');

        try {
            $client = $this->imapClient->account('default');
            $client->connect();
        } catch (Throwable $throwable) {
            $io->comment(sprintf('❌ Error while connecting: %s', $throwable->getMessage()));
            $this->logException($throwable);

            return false;
        }

        if (!$this->checkFolder($io, $client, $this->imapReadFolder)) {
            return false;
        }

        if (!$this->checkFolder($io, $client, $this->imapMoveFolder)) {
            return false;
        }

        $io->writeln('✅ OK');

        return true;
    }

    private function checkFolder(SymfonyStyle $io, Client $client, string $folderName): bool
    {
        try {
            $folder = $client->getFolder($folderName);
            if (!$folder instanceof Folder) {
                $io->comment(sprintf('❌ Error opening IMAP Folder: %s', $folderName));

                return false;
            }
        } catch (Throwable $throwable) {
            $io->comment(sprintf('❌ Error while connecting: %s', $throwable->getMessage()));
            $this->logException($throwable);

            return false;
        }

        return true;
    }
}
