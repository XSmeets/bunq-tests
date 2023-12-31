<?php
const ENABLE_SHOW_NAME = TRUE;
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="balance.css">
</head>
<body>
  <table>
    <tr>
      <th>
        Card ID
      </th>
      <th>
        Card type
      </th>
      <th>
        Card description
      </th>
<?php
      if (ENABLE_SHOW_NAME) {
        echo "<th>Name on card</th>";
      }
?>
      <th>
        Card country
      </th>
      <th>
        Card status
      </th>
      <th>
        Card number - last 4 digits
      </th>
      <th>
        Expiry date
      </th>
      <th>
        CVC
      </th>
    </tr>
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/CardC.php';

use bunq\tinker\BunqLib;
use bunq\tinker\SharedLib;
//$allOption = getopt('SANDBOX', SharedLib::ALL_OPTION_KEY);
$allOption = array("production" => "");
$environment = SharedLib::determineEnvironmentType($allOption);
$bunq = new BunqLib($environment);
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\Card;
use bunq\Model\Generated\Endpoint\CardGeneratedCvc2;
use bunq\Model\Generated\Endpoint\BunqResponseInt;
use bunq\Model\Generated\Endpoint\BunqResponseCardGeneratedCvc2;
use bunq\Model\Generated\Endpoint\CardC;
const CURRENCY_EUR = "EUR";
const POINTER_TYPE_IBAN = "IBAN";
const MESSAGE_MONETARY_ACCOUNT_NAME = 'Display name of monetary account: "%s"';
const INDEX_FIRST = 0;
$echoValue = 1;

//$userArray = User::listing()->getValue();
//$user = $userArray[0]->getUserPerson();

$cardList = Card::listing(array("count" => "200"));
if(false) {
echo "<pre>";
print_r($cardList);
echo '</pre>';
}


function cardTableEntry($card) {
  $cardStatus = $card->getStatus();
  //print_r($card);
/*
  if ($cardStatus == "DEACTIVATED") {
    Card::update(
        $card->getId(), // card ID
        null, // pinCode
        null, // activationCode
        'ACTIVE', // status
    );
  }
*/
  if (($cardStatus == "ACTIVE") || ($cardStatus == "DEACTIVATED") || ($cardStatus == "PIN TRIES EXCEEDED") || ($cardStatus == "FROZEN")) {
    $cardId = $card->getId();
    $cardType = $card->getType();
    $cardDescription = $card->getPrimaryAccountNumbers()[0]->getDescription();
    $cardExpiry = $card->getExpiryDate();
    $cardLimits = $card->getCardLimit();
    $cardCountry = $card->getCountry();
    $cardPAN = $card->getPrimaryAccountNumbers()[0]->getFourDigit();
    if ($cardStatus == "ACTIVE" || $cardStatus == "DEACTIVATED") {
      $cvcList = CardGeneratedCvc2::listing($cardId)->getValue();
      $cvcExists = false;
      foreach ($cvcList as $listedCvc) {
        if (($listedCvc->getStatus()) == ("AVAILABLE")) {
          $cardCvc = $listedCvc->getCvc2();
          $cvcExists = true;
          break;
        }
      }
      if ($cvcExists == false) {
        $cardCvcId = CardGeneratedCvc2::create($cardId);
        $cvcList = CardGeneratedCvc2::listing($cardId)->getValue();
        foreach ($cvcList as $listedCvc) {
          if (($listedCvc->getStatus()) == ("AVAILABLE")) {
            $cardCvc = $listedCvc->getCvc2();
            break;
          }
        }
      }
    }
    echo "<tr><td>" . $cardId . "</td><td>";
    echo $cardType . "</td><td>";
    echo $cardDescription . "</td><td>";
    if (ENABLE_SHOW_NAME) {
      echo $card->getNameOnCard() . "</td><td>";
    }
    echo $cardCountry . "</td><td>";
    echo $cardStatus . "</td><td>";
    echo $cardPAN . "</td><td>";
    echo $cardExpiry . "<td>";
    if ($cardStatus == "ACTIVE") {
      echo $cardCvc;
    } else {
      echo "-";
    }
    echo "</td></tr>";
  }
}

foreach ($cardList->getValue() as $card) {
  cardTableEntry($card);
}

//$cardList = Card::listing(array("count" => "200"));


foreach (CardC::listing()->getValue() as $card) {
  cardTableEntry($card);
}

// Save the API context to account for all the changes that might have occurred to it during the example execution
$bunq->updateContext();
?>
</table>
</body>
</html>
