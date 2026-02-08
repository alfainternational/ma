<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use InvalidArgumentException;

class Company
{
    private const VALID_STATUSES = ['active', 'inactive', 'pending', 'suspended'];

    private string  $id;
    private string  $userId;
    private string  $name;
    private string  $industry;
    private ?string $legalName;
    private ?string $taxNumber;
    private ?int    $foundedYear;
    private ?string $employeeCountRange;
    private ?string $annualRevenueRange;
    private ?string $locationCountry;
    private ?string $locationCity;
    private ?string $websiteUrl;
    private ?string $phone;
    private ?string $logoUrl;
    private string  $status;
    private string  $createdAt;
    private string  $updatedAt;

    public function __construct(
        string  $id,
        string  $userId,
        string  $name,
        string  $industry,
        ?string $legalName = null,
        ?string $taxNumber = null,
        ?int    $foundedYear = null,
        ?string $employeeCountRange = null,
        ?string $annualRevenueRange = null,
        ?string $locationCountry = null,
        ?string $locationCity = null,
        ?string $websiteUrl = null,
        ?string $phone = null,
        ?string $logoUrl = null,
        string  $status = 'active',
        ?string $createdAt = null,
        ?string $updatedAt = null,
    ) {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }

        $this->id                 = $id;
        $this->userId             = $userId;
        $this->name               = $name;
        $this->industry           = $industry;
        $this->legalName          = $legalName;
        $this->taxNumber          = $taxNumber;
        $this->foundedYear        = $foundedYear;
        $this->employeeCountRange = $employeeCountRange;
        $this->annualRevenueRange = $annualRevenueRange;
        $this->locationCountry    = $locationCountry;
        $this->locationCity       = $locationCity;
        $this->websiteUrl         = $websiteUrl;
        $this->phone              = $phone;
        $this->logoUrl            = $logoUrl;
        $this->status             = $status;
        $this->createdAt          = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt          = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): string                    { return $this->id; }
    public function getUserId(): string                { return $this->userId; }
    public function getName(): string                  { return $this->name; }
    public function getIndustry(): string              { return $this->industry; }
    public function getLegalName(): ?string             { return $this->legalName; }
    public function getTaxNumber(): ?string             { return $this->taxNumber; }
    public function getFoundedYear(): ?int              { return $this->foundedYear; }
    public function getEmployeeCountRange(): ?string    { return $this->employeeCountRange; }
    public function getAnnualRevenueRange(): ?string    { return $this->annualRevenueRange; }
    public function getLocationCountry(): ?string       { return $this->locationCountry; }
    public function getLocationCity(): ?string           { return $this->locationCity; }
    public function getWebsiteUrl(): ?string            { return $this->websiteUrl; }
    public function getPhone(): ?string                { return $this->phone; }
    public function getLogoUrl(): ?string               { return $this->logoUrl; }
    public function getStatus(): string                { return $this->status; }
    public function getCreatedAt(): string             { return $this->createdAt; }
    public function getUpdatedAt(): string             { return $this->updatedAt; }

    public function setName(string $name): void                          { $this->name = $name; $this->touch(); }
    public function setIndustry(string $industry): void                  { $this->industry = $industry; $this->touch(); }
    public function setLegalName(?string $legalName): void               { $this->legalName = $legalName; $this->touch(); }
    public function setTaxNumber(?string $taxNumber): void               { $this->taxNumber = $taxNumber; $this->touch(); }
    public function setFoundedYear(?int $foundedYear): void              { $this->foundedYear = $foundedYear; $this->touch(); }
    public function setEmployeeCountRange(?string $range): void          { $this->employeeCountRange = $range; $this->touch(); }
    public function setAnnualRevenueRange(?string $range): void          { $this->annualRevenueRange = $range; $this->touch(); }
    public function setLocationCountry(?string $country): void           { $this->locationCountry = $country; $this->touch(); }
    public function setLocationCity(?string $city): void                  { $this->locationCity = $city; $this->touch(); }
    public function setWebsiteUrl(?string $url): void                    { $this->websiteUrl = $url; $this->touch(); }
    public function setPhone(?string $phone): void                       { $this->phone = $phone; $this->touch(); }
    public function setLogoUrl(?string $url): void                       { $this->logoUrl = $url; $this->touch(); }
    public function setStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }
        $this->status = $status;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'user_id'              => $this->userId,
            'name'                 => $this->name,
            'industry'             => $this->industry,
            'legal_name'           => $this->legalName,
            'tax_number'           => $this->taxNumber,
            'founded_year'         => $this->foundedYear,
            'employee_count_range' => $this->employeeCountRange,
            'annual_revenue_range' => $this->annualRevenueRange,
            'location_country'     => $this->locationCountry,
            'location_city'        => $this->locationCity,
            'website_url'          => $this->websiteUrl,
            'phone'                => $this->phone,
            'logo_url'             => $this->logoUrl,
            'status'               => $this->status,
            'created_at'           => $this->createdAt,
            'updated_at'           => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:                 $data['id'],
            userId:             $data['user_id'],
            name:               $data['name'],
            industry:           $data['industry'],
            legalName:          $data['legal_name'] ?? null,
            taxNumber:          $data['tax_number'] ?? null,
            foundedYear:        isset($data['founded_year']) ? (int) $data['founded_year'] : null,
            employeeCountRange: $data['employee_count_range'] ?? null,
            annualRevenueRange: $data['annual_revenue_range'] ?? null,
            locationCountry:    $data['location_country'] ?? null,
            locationCity:       $data['location_city'] ?? null,
            websiteUrl:         $data['website_url'] ?? null,
            phone:              $data['phone'] ?? null,
            logoUrl:            $data['logo_url'] ?? null,
            status:             $data['status'] ?? 'active',
            createdAt:          $data['created_at'] ?? null,
            updatedAt:          $data['updated_at'] ?? null,
        );
    }
}
