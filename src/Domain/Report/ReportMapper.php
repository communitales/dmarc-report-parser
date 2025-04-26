<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Domain\Report;

use App\Domain\Report\Exception\ReportInvalidDataException;
use App\Entity\Enum\RecordDispositionEnum;
use App\Entity\Enum\RecordDkimAlignEnum;
use App\Entity\Enum\RecordDkimResultEnum;
use App\Entity\Enum\RecordSpfAlignEnum;
use App\Entity\Enum\RecordSpfResultEnum;
use App\Entity\Report;
use App\Entity\RptRecord;
use DateTime;
use SimpleXMLElement;

use function implode;
use function simplexml_load_string;
use function unpack;

/**
 * Class ReportMapper
 */
class ReportMapper
{
    /**
     * @throws ReportInvalidDataException
     */
    public function map(string $xmlString, Report $report): void
    {
        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            throw new ReportInvalidDataException('Invalid xml string');
        }

        $reportId = (string)$xml->{'report_metadata'}->{'report_id'};

        $minDate = (new Datetime())->setTimestamp((int)$xml->{'report_metadata'}->{'date_range'}->{'begin'});
        $maxDate = (new Datetime())->setTimestamp((int)$xml->{'report_metadata'}->{'date_range'}->{'end'});
        $org = (string)$xml->{'report_metadata'}->{'org_name'};
        $email = (string)$xml->{'report_metadata'}->{'email'};
        $extra = (string)$xml->{'report_metadata'}->{'extra_contact_info'};

        if ((string)$xml->{'policy_published'} === 'HASH') {
            $domain = (string)$xml->{'policy_published'}->{'domain'};
            $policyAdkim = (string)$xml->{'policy_published'}->{'adkim'};
            $policyAspf = (string)$xml->{'policy_published'}->{'aspf'};
            $policyP = (string)$xml->{'policy_published'}->{'p'};
            $policySp = (string)$xml->{'policy_published'}->{'sp'};
            $policyPct = (string)$xml->{'policy_published'}->{'pct'};
        } else {
            $domain = (string)$xml->{'policy_published'}[0]->{'domain'};
            $policyAdkim = (string)$xml->{'policy_published'}[0]->{'adkim'};
            $policyAspf = (string)$xml->{'policy_published'}[0]->{'aspf'};
            $policyP = (string)$xml->{'policy_published'}[0]->{'p'};
            $policySp = (string)$xml->{'policy_published'}[0]->{'sp'};
            $policyPct = (string)$xml->{'policy_published'}[0]->{'pct'};
        }

        $report->setReportId($reportId);
        $report->setMinDate($minDate);
        $report->setMaxDate($maxDate);
        $report->setOrg($org);
        $report->setEmail($this->stringOrNull($email));
        $report->setExtraContactInfo($this->stringOrNull($extra));
        $report->setDomain($domain);
        $report->setPolicyAdkim($this->stringOrNull($policyAdkim));
        $report->setPolicyAspf($this->stringOrNull($policyAspf));
        $report->setPolicyP($this->stringOrNull($policyP));
        $report->setPolicySp($this->stringOrNull($policySp));
        $report->setPolicyPct($this->intOrNull($policyPct));
        $report->setRawXml($xmlString);

        foreach ($xml->{'record'} as $recordXml) {
            $rptRecord = new RptRecord();
            $this->mapRecord($recordXml, $rptRecord);

            $report->addRptRecord($rptRecord);
        }
    }

    /**
     * @throws ReportInvalidDataException
     */
    private function mapRecord(SimpleXMLElement $xml, RptRecord $rptRecord): void
    {
        $row = $xml->{'row'};

        $rowCount = (int)$row->{'count'};
        $disposition = (string)$row->{'policy_evaluated'}->{'disposition'};
        # some reports don't have dkim/spf, "unknown" is default for these
        $dkimAlign = (string)$row->{'policy_evaluated'}->{'dkim'};
        $spfAlign = (string)$row->{'policy_evaluated'}->{'spf'};

        $identifierHeaderFrom = $xml->{'identifiers'}->{'header_from'};

        $dkimDomain = [];
        $dkimResult = [];
        foreach ($xml->{'auth_results'}->{'dkim'} as $dkimXml) {
            $dkimDomain[] = (string)$dkimXml->{'domain'};
            $dkimResult[] = (string)$dkimXml->{'result'};
        }

        $spfDomain = [];
        $spfResult = [];
        foreach ($xml->{'auth_results'}->{'spf'} as $spfXml) {
            $spfDomain[] = (string)$spfXml->{'domain'};
            $spfResult[] = (string)$spfXml->{'result'};
        }

        $reason = [];
        foreach ($row->{'policy_evaluated'}->{'reason'} as $reasonXml) {
            $reason[] = (string)$reasonXml->{'type'};
        }

        $ip = (string)$row->{'source_ip'};
        $ipv4 = null;
        $ipv6 = null;
        if ($nip = inet_pton($ip)) {
            if (strlen($nip) === 4) {
                $ipUnpacked = unpack('N', $nip);
                if ($ipUnpacked === false) {
                    throw new ReportInvalidDataException('Invalid IP format after unpack inet_pton:'.$ip);
                }

                $ipv4 = (int)$ipUnpacked[1]; // 32bit Unsigned Integer
            } elseif (strlen($nip) === 16) {
                $ipv6 = $nip; // BINARY(16)
            } else {
                throw new ReportInvalidDataException('Invalid IP format after inet_pton:'.$ip);
            }
        } else {
            throw new ReportInvalidDataException('Invalid IP address:'.$ip);
        }

        $disposition = $this->stringOrNull($disposition);
        $dkimResult = $this->stringOrNull(implode('/', $dkimResult));
        $spfResult = $this->stringOrNull(implode('/', $spfResult));

        $rptRecord->setIpv4($ipv4);
        $rptRecord->setIpv6($ipv6);
        $rptRecord->setRowCount($rowCount);
        $rptRecord->setDisposition($disposition !== null ? RecordDispositionEnum::from($disposition) : null);
        $rptRecord->setReason($this->stringOrNull(implode('/', $reason)));
        $rptRecord->setDkimDomain($this->stringOrNull(implode('/', $dkimDomain)));
        $rptRecord->setDkimResult($dkimResult !== null ? RecordDkimResultEnum::from($dkimResult) : null);
        $rptRecord->setSpfDomain($this->stringOrNull(implode('/', $spfDomain)));
        $rptRecord->setSpfResult($spfResult !== null ? RecordSpfResultEnum::from($spfResult) : null);
        $rptRecord->setSpfAlign(RecordSpfAlignEnum::from($spfAlign));
        $rptRecord->setDkimAlign(RecordDkimAlignEnum::from($dkimAlign));
        $rptRecord->setIdentifierHeaderFrom($this->stringOrNull($identifierHeaderFrom));
    }

    private function stringOrNull(?string $string): ?string
    {
        if ($string === '' || $string === null) {
            return null;
        }

        return $string;
    }

    private function intOrNull(?string $string): ?int
    {
        if ($string === '' || $string === null) {
            return null;
        }

        return (int)$string;
    }
}
