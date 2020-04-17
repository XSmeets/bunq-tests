#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use bunq\tinker\BunqLib;
use bunq\tinker\SharedLib;
use bunq\Model\Generated\Endpoint\Card;

$allOption = getopt('', SharedLib::ALL_OPTION_KEY);
$environment = SharedLib::determineEnvironmentType($allOption);
SharedLib::printHeader();

$bunq = new BunqLib($environment);

$cards = Card::listing();

print_r($cards);

// Save the API context to account for all the changes that might have occurred to it during the example execution
$bunq->updateContext();
