<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Domain\Report;

use App\Domain\Report\Exception\ReportInvalidDataException;
use App\Domain\Report\ReportMapper;
use App\Entity\Enum\RecordDispositionEnum;
use App\Entity\Enum\RecordDkimAlignEnum;
use App\Entity\Enum\RecordDkimResultEnum;
use App\Entity\Enum\RecordSpfAlignEnum;
use App\Entity\Enum\RecordSpfResultEnum;
use App\Entity\Report;
use App\Entity\RptRecord;
use DateTime;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

/**
 * Class ReportMapperTest
 */
class ReportMapperTest extends TestCase
{
    /**
     * @throws ReportInvalidDataException
     */
    public function testCreateReportFromXmlWithOneRecord(): void
    {
        $xmlString = (string)file_get_contents(__DIR__.'/../../../Fixtures/Report/report_one_record.xml');

        $mapper = new ReportMapper();

        $report = new Report();
        $mapper->map($xmlString, $report);

        $expected = new Report();
        $expected->setReportId('8e65e066109d4eb0b0f71197b6e17722');
        $expected->setMinDate(new DateTime('2025-04-16T02:00:00'));
        $expected->setMaxDate(new DateTime('2025-04-17T02:00:00'));
        $expected->setOrg('Enterprise Outlook');
        $expected->setEmail('dmarcreport@microsoft.com');
        $expected->setExtraContactInfo(null);
        $expected->setDomain('example.com');
        $expected->setPolicyAdkim('r');
        $expected->setPolicyAspf('r');
        $expected->setPolicyP('quarantine');
        $expected->setPolicySp('none');
        $expected->setPolicyPct(100);
        $expected->setRawXml($xmlString);

        $rptRecord = new RptRecord();
        $rptRecord->setIpv4(2130706433);
        $rptRecord->setIpv6(null);
        $rptRecord->setRowCount(1);
        $rptRecord->setDisposition(RecordDispositionEnum::None);
        $rptRecord->setReason(null);
        $rptRecord->setDkimDomain('example.com');
        $rptRecord->setDkimResult(RecordDkimResultEnum::Pass);
        $rptRecord->setSpfDomain('example.com');
        $rptRecord->setSpfResult(RecordSpfResultEnum::Pass);
        $rptRecord->setSpfAlign(RecordSpfAlignEnum::Pass);
        $rptRecord->setDkimAlign(RecordDkimAlignEnum::Pass);
        $rptRecord->setIdentifierHeaderFrom('example.com');

        $expected->addRptRecord($rptRecord);

        self::assertEquals($expected, $report);
    }

    /**
     * @throws ReportInvalidDataException
     */
    public function testCreateReportFromXmlWithMultipleRecords(): void
    {
        $xmlString = (string)file_get_contents(__DIR__.'/../../../Fixtures/Report/report_multiple_records.xml');

        $mapper = new ReportMapper();

        $report = new Report();
        $mapper->map($xmlString, $report);

        $expected = new Report();
        $expected->setReportId('szn_example.com-2025-04-17');
        $expected->setMinDate(new DateTime('2025-04-17T00:00:00'));
        $expected->setMaxDate(new DateTime('2025-04-18T00:00:00'));
        $expected->setOrg('seznam.cz a.s.');
        $expected->setEmail('abuse@seznam.cz');
        $expected->setExtraContactInfo(null);
        $expected->setDomain('example.com');
        $expected->setPolicyAdkim('r');
        $expected->setPolicyAspf('r');
        $expected->setPolicyP('quarantine');
        $expected->setPolicySp(null);
        $expected->setPolicyPct(100);
        $expected->setRawXml($xmlString);

        $rptRecord = new RptRecord();
        $rptRecord->setIpv4(null);
        $rptRecord->setIpv6((string)hex2bin('2a010111f403c20c0000000000000001'));
        $rptRecord->setRowCount(1);
        $rptRecord->setDisposition(RecordDispositionEnum::None);
        $rptRecord->setReason(null);
        $rptRecord->setDkimDomain('example.com');
        $rptRecord->setDkimResult(RecordDkimResultEnum::Pass);
        $rptRecord->setSpfDomain('example.com');
        $rptRecord->setSpfResult(RecordSpfResultEnum::Pass);
        $rptRecord->setSpfAlign(RecordSpfAlignEnum::Pass);
        $rptRecord->setDkimAlign(RecordDkimAlignEnum::Pass);
        $rptRecord->setIdentifierHeaderFrom('example.com');

        $expected->addRptRecord($rptRecord);

        $rptRecord = new RptRecord();
        $rptRecord->setIpv4(null);
        $rptRecord->setIpv6((string)hex2bin('2a010111f403c20b0000000000000001'));
        $rptRecord->setRowCount(1);
        $rptRecord->setDisposition(RecordDispositionEnum::None);
        $rptRecord->setReason(null);
        $rptRecord->setDkimDomain('example.com');
        $rptRecord->setDkimResult(RecordDkimResultEnum::Pass);
        $rptRecord->setSpfDomain('example.com');
        $rptRecord->setSpfResult(RecordSpfResultEnum::Pass);
        $rptRecord->setSpfAlign(RecordSpfAlignEnum::Pass);
        $rptRecord->setDkimAlign(RecordDkimAlignEnum::Pass);
        $rptRecord->setIdentifierHeaderFrom('example.com');

        $expected->addRptRecord($rptRecord);

        $rptRecord = new RptRecord();
        $rptRecord->setIpv4(678139264);
        $rptRecord->setIpv6(null);
        $rptRecord->setRowCount(1);
        $rptRecord->setDisposition(RecordDispositionEnum::None);
        $rptRecord->setReason(null);
        $rptRecord->setDkimDomain('example.com');
        $rptRecord->setDkimResult(RecordDkimResultEnum::Pass);
        $rptRecord->setSpfDomain('example.com');
        $rptRecord->setSpfResult(RecordSpfResultEnum::Pass);
        $rptRecord->setSpfAlign(RecordSpfAlignEnum::Pass);
        $rptRecord->setDkimAlign(RecordDkimAlignEnum::Pass);
        $rptRecord->setIdentifierHeaderFrom('example.com');

        $expected->addRptRecord($rptRecord);

        self::assertEquals($expected, $report);
    }
}
