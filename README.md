# Euro VAT Checker

> [!IMPORTANT]
> This library does not support batch validation yet.

> [!WARNING]
> Work in progress. Do not use in production environments.


## Install via Composer

Coming, not available yet.

## Usage

```php
$checker = new \LucasG\EuroVatChecker\EuroVatChecker();

// Getting basic company information (see CompanyInfo.php)
$companyInfo = $checker->getCompanyInfo('FR23808709794');

// Validate VAT Number
if ($checker->validate('FR23808709794')) {
    // do stuff
}
```
