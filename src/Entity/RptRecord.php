<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Enum\RecordDispositionEnum;
use App\Entity\Enum\RecordDkimAlignEnum;
use App\Entity\Enum\RecordDkimResultEnum;
use App\Entity\Enum\RecordSpfAlignEnum;
use App\Entity\Enum\RecordSpfResultEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rptrecord', options: ['collate' => 'utf8mb4_0900_ai_ci'])]
#[ORM\Index(name: 'serial', columns: ['serial', 'ip'])]
#[ORM\Index(name: 'serial6', columns: ['serial', 'ip6'])]
class RptRecord
{
    #[ORM\Column(name: 'id', type: Types::INTEGER, nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    /**
     * @phpstan-ignore property.onlyRead
     */
    private int $id;

    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'rptRecords')]
    #[ORM\JoinColumn(name: 'serial', referencedColumnName: 'serial', nullable: false, options: ['unsigned' => true])]
    private Report $report;

    #[ORM\Column(name: 'ip', type: Types::INTEGER, nullable: true, options: ['unsigned' => true])]
    private ?int $ipv4 = null;

    #[ORM\Column(name: 'ip6', type: Types::BINARY, length: 16, nullable: true, options: ['fixed' => true])]
    private ?string $ipv6 = null;

    #[ORM\Column(name: 'rcount', type: Types::INTEGER, nullable: false, options: ['unsigned' => true])]
    private int $rowCount;

    #[ORM\Column(name: 'disposition', type: Types::STRING, length: 10, nullable: true)]
    private ?string $disposition = null;

    #[ORM\Column(name: 'reason', type: Types::STRING, length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(name: 'dkimdomain', type: Types::STRING, length: 255, nullable: true)]
    private ?string $dkimDomain = null;

    #[ORM\Column(name: 'dkimresult', type: Types::STRING, length: 10, nullable: true)]
    private ?string $dkimResult = null;

    #[ORM\Column(name: 'spfdomain', type: Types::STRING, length: 255, nullable: true)]
    private ?string $spfDomain = null;

    #[ORM\Column(name: 'spfresult', type: Types::STRING, length: 10, nullable: true)]
    private ?string $spfResult = null;

    #[ORM\Column(name: 'spf_align', type: Types::STRING, length: 10, nullable: false)]
    private string $spfAlign;

    #[ORM\Column(name: 'dkim_align', type: Types::STRING, length: 10, nullable: false)]
    private string $dkimAlign;

    #[ORM\Column(name: 'identifier_hfrom', type: Types::STRING, length: 255, nullable: true)]
    private ?string $identifierHeaderFrom = null;

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    public function getIpv4(): ?int
    {
        return $this->ipv4;
    }

    public function setIpv4(?int $ipv4): void
    {
        $this->ipv4 = $ipv4;
    }

    public function getIpv6(): ?string
    {
        return $this->ipv6;
    }

    public function setIpv6(?string $ipv6): void
    {
        $this->ipv6 = $ipv6;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function setRowCount(int $rowCount): void
    {
        $this->rowCount = $rowCount;
    }

    public function getDisposition(): ?RecordDispositionEnum
    {
        if ($this->disposition === null) {
            return null;
        }

        return RecordDispositionEnum::from($this->disposition);
    }

    public function setDisposition(?RecordDispositionEnum $disposition): void
    {
        $this->disposition = $disposition?->value;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function getDkimDomain(): ?string
    {
        return $this->dkimDomain;
    }

    public function setDkimDomain(?string $dkimDomain): void
    {
        $this->dkimDomain = $dkimDomain;
    }

    public function getDkimResult(): ?RecordDkimResultEnum
    {
        if ($this->dkimResult === null) {
            return null;
        }

        return RecordDkimResultEnum::from($this->dkimResult);
    }

    public function setDkimResult(?RecordDkimResultEnum $dkimResult): void
    {
        $this->dkimResult = $dkimResult?->value;
    }

    public function getSpfDomain(): ?string
    {
        return $this->spfDomain;
    }

    public function setSpfDomain(?string $spfDomain): void
    {
        $this->spfDomain = $spfDomain;
    }

    public function getSpfResult(): ?RecordSpfResultEnum
    {
        if ($this->spfResult === null) {
            return null;
        }

        return RecordSpfResultEnum::from($this->spfResult);
    }

    public function setSpfResult(?RecordSpfResultEnum $spfResult): void
    {
        $this->spfResult = $spfResult?->value;
    }

    public function getSpfAlign(): RecordSpfAlignEnum
    {
        return RecordSpfAlignEnum::from($this->spfAlign);
    }

    public function setSpfAlign(RecordSpfAlignEnum $spfAlign): void
    {
        $this->spfAlign = $spfAlign->value;
    }

    public function getDkimAlign(): RecordDkimAlignEnum
    {
        return RecordDkimAlignEnum::from($this->dkimAlign);
    }

    public function setDkimAlign(RecordDkimAlignEnum $dkimAlign): void
    {
        $this->dkimAlign = $dkimAlign->value;
    }

    public function getIdentifierHeaderFrom(): ?string
    {
        return $this->identifierHeaderFrom;
    }

    public function setIdentifierHeaderFrom(?string $identifierHeaderFrom): void
    {
        $this->identifierHeaderFrom = $identifierHeaderFrom;
    }
}
