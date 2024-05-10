<?php

namespace LucasG\EuroVatChecker;

readonly class CompanyInfo
{
    public function __construct(
        public Country $countryCode,
        public string $vatNumber,
        public bool $valid,
        public string $name,
        public string $address
    ) {
    }
}
