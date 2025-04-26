<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Component\System\Check\DatabaseChecker;
use App\Component\System\Check\ImapChecker;
use Communitales\Component\Log\LogAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:system:check', description: 'Checks if all systems can be reached.')]
class VerifyConfigCommand extends Command implements LoggerAwareInterface
{
    use LogAwareTrait;

    public function __construct(
        private DatabaseChecker $databaseChecker,
        private ImapChecker $imapChecker,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $options = $input->getOptions();

        $verbose = (bool)($options['verbose'] ?? false);
        $valid = true;

        /** @phpstan-ignore booleanAnd.rightAlwaysTrue */
        $valid = $this->databaseChecker->check($io, $verbose) && $valid;
        $valid = $this->imapChecker->check($io) && $valid;

        if ($valid) {
            $io->success('Configuration is valid.');
        } else {
            $io->warning('Some problem have been found.');
        }

        return Command::SUCCESS;
    }
}
