<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'report', options: ['collate' => 'utf8mb4_0900_ai_ci'])]
#[ORM\UniqueConstraint(name: 'domain', columns: ['domain', 'reportid'])]
class Report
{
    #[ORM\Column(name: 'serial', type: Types::INTEGER, nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $serial;

    #[ORM\Column(name: 'mindate', type: Types::DATETIME_MUTABLE, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTime $minDate;

    #[ORM\Column(name: 'maxdate', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $maxDate = null;

    #[ORM\Column(name: 'domain', type: Types::STRING, length: 255, nullable: false)]
    private string $domain;

    #[ORM\Column(name: 'org', type: Types::STRING, length: 255, nullable: false)]
    private string $org;

    #[ORM\Column(name: 'reportid', type: Types::STRING, length: 255, nullable: false)]
    private string $reportId;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'extra_contact_info', type: Types::STRING, length: 255, nullable: true)]
    private ?string $extraContactInfo = null;

    #[ORM\Column(name: 'policy_adkim', type: Types::STRING, length: 20, nullable: true)]
    private ?string $policyAdkim = null;

    #[ORM\Column(name: 'policy_aspf', type: Types::STRING, length: 20, nullable: true)]
    private ?string $policyAspf = null;

    #[ORM\Column(name: 'policy_p', type: Types::STRING, length: 20, nullable: true)]
    private ?string $policyP = null;

    #[ORM\Column(name: 'policy_sp', type: Types::STRING, length: 20, nullable: true)]
    private ?string $policySp = null;

    #[ORM\Column(name: 'policy_pct', type: Types::SMALLINT, nullable: true, options: ['unsigned' => true])]
    private ?int $policyPct = null;

    #[ORM\Column(name: 'raw_xml', type: Types::TEXT, length: 16777215, nullable: true)]
    private ?string $rawXml = null;

    /**
     * @var Collection<string, RptRecord>
     */
    #[ORM\OneToMany(targetEntity: RptRecord::class, mappedBy: 'report', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $rptRecords;

    public function __construct()
    {
        $this->rptRecords = new ArrayCollection();
    }

    public function getSerial(): int
    {
        return $this->serial;
    }

    public function setSerial(int $serial): void
    {
        $this->serial = $serial;
    }

    public function getMinDate(): DateTime
    {
        return $this->minDate;
    }

    public function setMinDate(DateTime $minDate): void
    {
        $this->minDate = $minDate;
    }

    public function getMaxDate(): ?DateTime
    {
        return $this->maxDate;
    }

    public function setMaxDate(?DateTime $maxDate): void
    {
        $this->maxDate = $maxDate;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    public function getOrg(): string
    {
        return $this->org;
    }

    public function setOrg(string $org): void
    {
        $this->org = $org;
    }

    public function getReportId(): string
    {
        return $this->reportId;
    }

    public function setReportId(string $reportId): void
    {
        $this->reportId = $reportId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getExtraContactInfo(): ?string
    {
        return $this->extraContactInfo;
    }

    public function setExtraContactInfo(?string $extraContactInfo): void
    {
        $this->extraContactInfo = $extraContactInfo;
    }

    public function getPolicyAdkim(): ?string
    {
        return $this->policyAdkim;
    }

    public function setPolicyAdkim(?string $policyAdkim): void
    {
        $this->policyAdkim = $policyAdkim;
    }

    public function getPolicyAspf(): ?string
    {
        return $this->policyAspf;
    }

    public function setPolicyAspf(?string $policyAspf): void
    {
        $this->policyAspf = $policyAspf;
    }

    public function getPolicyP(): ?string
    {
        return $this->policyP;
    }

    public function setPolicyP(?string $policyP): void
    {
        $this->policyP = $policyP;
    }

    public function getPolicySp(): ?string
    {
        return $this->policySp;
    }

    public function setPolicySp(?string $policySp): void
    {
        $this->policySp = $policySp;
    }

    public function getPolicyPct(): ?int
    {
        return $this->policyPct;
    }

    public function setPolicyPct(?int $policyPct): void
    {
        $this->policyPct = $policyPct;
    }

    public function getRawXml(): ?string
    {
        return $this->rawXml;
    }

    public function setRawXml(?string $rawXml): void
    {
        $this->rawXml = $rawXml;
    }

    public function addRptRecord(RptRecord $rptRecord): void
    {
        $this->rptRecords->add($rptRecord);
        $rptRecord->setReport($this);
    }

    /**
     * @return Collection<string, RptRecord>
     */
    public function getRptRecords(): Collection
    {
        return $this->rptRecords;
    }

    /**
     * @param Collection<string, RptRecord> $rptRecords
     */
    public function setRptRecords(Collection $rptRecords): void
    {
        $this->rptRecords = $rptRecords;
    }
}
