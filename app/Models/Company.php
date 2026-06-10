<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    public const DEFAULT_TAX_RATE = 10.00;

    public const DEFAULT_SERVICE_CHARGE_RATE = 10.00;

    protected $fillable = [
        'name',
        'default_tax_rate',
        'default_service_charge_rate',
        'uses_qr_code',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
            'default_service_charge_rate' => 'decimal:2',
            'uses_qr_code' => 'boolean',
        ];
    }

    public function getTaxRateAsPercentage(): float
    {
        return (float) $this->default_tax_rate / 100;
    }

    public function getServiceChargeRateAsPercentage(): float
    {
        return (float) $this->default_service_charge_rate / 100;
    }

    public static function active(): ?self
    {
        return static::query()->first();
    }

    public static function activeTaxRate(): float
    {
        $company = static::active();

        if ($company === null) {
            return self::DEFAULT_TAX_RATE / 100;
        }

        return $company->getTaxRateAsPercentage();
    }

    public static function activeServiceChargeRate(): float
    {
        $company = static::active();

        if ($company === null) {
            return self::DEFAULT_SERVICE_CHARGE_RATE / 100;
        }

        return $company->getServiceChargeRateAsPercentage();
    }

    public static function usesQrCode(): bool
    {
        $company = static::active();

        if ($company === null) {
            return true;
        }

        return $company->uses_qr_code;
    }
}
