<?php

require_once "vendor/autoload.php";

$checker = new LucasG\EuroVatChecker\EuroVatChecker();
$vatNumber = 'FR23808709794';

$companyInfo = $checker->getCompanyInfo($vatNumber);

echo "Is valid : " . ($checker->validate($vatNumber) ? 'TRUE' : 'FALSE') . "\n";
echo "Company Info : " . json_encode($companyInfo) . "\n";
