<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Domain\Config\ConfigFactory;
use App\Domain\Config\Model\ImportConfig;
use App\Domain\Report\Exception\AbstractReportException;
use App\Domain\Report\Exception\ReportInvalidDataException;
use App\Domain\Report\Exception\ReportSkipAttachmentException;
use App\Domain\Report\Exception\ReportSkippedException;
use App\Domain\Report\ReportMapper;
use App\Entity\Report;
use App\Repository\ReportRepository;
use Communitales\Component\Log\LogAwareTrait;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\MimeTypes;
use Throwable;
use Webklex\PHPIMAP\Attachment;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\MethodNotFoundException;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use ZipArchive;

use function gzopen;
use function sprintf;

#[AsCommand(name: 'import:imap', description: 'Import reports from IMAP.')]
class ImportReportsFromImapCommand extends Command implements LoggerAwareInterface
{
    use LogAwareTrait;

    public function __construct(
        private readonly ConfigFactory $configFactory,
        private readonly ClientManager $imapClient,
        private readonly ReportMapper $reportMapper,
        private readonly ReportRepository $reportRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section($this->getDescription());

        try {
            $this->import($io);
        } catch (Throwable $throwable) {
            $this->logException($throwable);
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws Throwable
     */
    private function import(SymfonyStyle $io): void
    {
        $config = $this->configFactory->createConfig();
        $client = $this->imapClient->account('default');

        $client->connect();

        $folder = $client->getFolder($config->imapReadFolder);
        if (!$folder instanceof Folder) {
            $io->error('Could not read from IMAP folder: '.$config->imapReadFolder);

            return;
        }

        $messagesCount = $folder->messages()->all()->count();
        for ($messageNum = 1; $messageNum <= $messagesCount; $messageNum++) {

            $messages = $folder->messages()->all()->limit(1)->fetchOrderAsc()->get();
            foreach ($messages as $message) {
                try {
                    $this->processMessage($io, $config, $message, $messageNum, $messagesCount);
                } catch (AbstractReportException $reportException) {
                    $io->comment($reportException->getMessage());
                }
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function processMessage(SymfonyStyle $io, ImportConfig $config, Message $message, int $messageNum, int $messageCount): void
    {
        $uid = $message->get('uid');
        $io->writeln(
            sprintf(
                'Processing message %d of %d: Message ID %s - %s - %s',
                $messageNum,
                $messageCount,
                $uid,
                $message->getSubject(),
                $message->getDate()->toDate()->format('d.m.Y H:i:s')
            )
        );

        $attachments = $message->getAttachments();
        foreach ($attachments as $attachment) {
            try {
                $xmlString = $this->getXmlFromAttachment($attachment);
                $this->createOrUpdateReport($xmlString);
            } catch (ReportSkippedException $reportException) {
                $message->move($config->imapMoveFolder);
                throw $reportException;
            } catch (ReportSkipAttachmentException|ReportInvalidDataException) {
            }
        }

        $message->move($config->imapMoveFolder);
    }

    /**
     * @throws ReportSkippedException
     * @throws ReportInvalidDataException
     */
    private function createOrUpdateReport(string $xmlString): void
    {
        $report = new Report();

        $this->reportMapper->map($xmlString, $report);

        $domain = $report->getDomain();
        $reportId = $report->getReportId();

        $existingReport = $this->reportRepository->findOneBy(['domain' => $domain, 'reportId' => $reportId]);
        if ($existingReport !== null) {
            throw new ReportSkippedException(sprintf('%s %s is already imported. Skipping.', $domain, $reportId));
        }

        $this->reportRepository->save($report);
    }

    /**
     * @throws ReportInvalidDataException
     * @throws ReportSkipAttachmentException
     * @throws MethodNotFoundException
     */
    private function getXmlFromAttachment(Attachment $attachment): string
    {
        $filename = tempnam(sys_get_temp_dir(), 'imap');

        $handle = fopen($filename, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open temp file: '.$filename);
        }

        fwrite($handle, $attachment->getContent());
        fclose($handle);

        $mimeTypes = new MimeTypes();
        $mimeType = $mimeTypes->guessMimeType($filename);

        if ($mimeType === 'application/zip') {
            $xml = $this->getXmlFromZipFile($filename);
        } elseif ($mimeType === 'application/gzip') {
            $xml = $this->getXmlFromGzFile($filename);
        } else {
            throw new ReportSkipAttachmentException('Unsupported mime type: '.$mimeType.$filename);
        }

        unlink($filename);

        return $xml;
    }

    /**
     * @throws ReportInvalidDataException
     */
    private function getXmlFromZipFile(string $filename): string
    {
        $zip = new ZipArchive();

        if ($zip->open($filename) === true) {
            $xmlContent = $zip->getFromIndex(0);
            $zip->close();
        } else {
            throw new RuntimeException('Could not open ZIP file.');
        }

        if ($xmlContent === false) {
            throw new ReportInvalidDataException('No XML file found in ZIP.');
        }

        return $xmlContent;
    }

    private function getXmlFromGzFile(string $filename): string
    {
        $gz = gzopen($filename, 'rb');

        if ($gz === false) {
            throw new RuntimeException('Could not open GZIP file.');
        }

        $xmlContent = '';
        while (!gzeof($gz)) {
            $xmlContent .= gzread($gz, 102400);
        }

        gzclose($gz);

        return $xmlContent;
    }
}
