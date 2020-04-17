#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use bunq\tinker\BunqLib;
use bunq\tinker\SharedLib;
use bunq\Model\Generated\Endpoint\CardDebit;
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\UserPerson;

$allOption = getopt('', SharedLib::ALL_OPTION_KEY);
$environment = SharedLib::determineEnvironmentType($allOption);

SharedLib::printHeader();

$bunq = new BunqLib($environment);
$userArray = User::listing()->getValue();
$user = $userArray[0]->getUserPerson();
//print_r($user);
//echo $user->getDisplayName();

$card = CardDebit::create("",$user->getDisplayName(),"MAESTRO_MOBILE_NFC");
$card = CardDebit::create("",$user->getDisplayName(),"MASTERCARD_MOBILE_NFC");

// Save the API context to account for all the changes that might have occurred to it during the example execution
$bunq->updateContext();
