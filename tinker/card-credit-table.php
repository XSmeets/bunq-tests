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
function load() {include_once __DIR__ . '/CardC.php';}
spl_autoload_register('load');

use bunq\tinker\BunqLib;
use bunq\tinker\SharedLib;
//$allOption = getopt('SANDBOX', SharedLib::ALL_OPTION_KEY);
$allOption = array("production" => "");
$environment = SharedLib::determineEnvironmentType($allOption);
$bunq = new BunqLib($environment);
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\CardC;
use bunq\Model\Generated\Endpoint\CardGeneratedCvc2;
use bunq\Model\Generated\Endpoint\BunqResponseInt;
use bunq\Model\Generated\Endpoint\BunqResponseCardGeneratedCvc2;
const CURRENCY_EUR = "EUR";
const POINTER_TYPE_IBAN = "IBAN";
const MESSAGE_MONETARY_ACCOUNT_NAME = 'Display name of monetary account: "%s"';
const INDEX_FIRST = 0;
const ENABLE_METAL = false;
$echoValue = 1;

//$userArray = User::listing()->getValue();
//$user = $userArray[0]->getUserPerson();

$cardList = CardC::listing(array("count" => "200"));
if(false) {
echo "<pre>";
print_r($cardList);
echo '</pre>';
}
foreach ($cardList->getValue() as $card) {
  $cardStatus = $card->getStatus();
  //print_r($card);

  if (($cardStatus == "ACTIVE") || ($cardStatus == "DEACTIVATED") || ($cardStatus == "PIN TRIES EXCEEDED") || ($cardStatus == "FROZEN")) {
    $cardId = $card->getId();
    $cardType = $card->getType();
    $cardDescription = $card->getPrimaryAccountNumbers()[0]->getDescription();
    $cardExpiry = $card->getExpiryDate();
    $cardLimits = $card->getCardLimit();
    $cardCountry = $card->getCountry();
    $cardPAN = $card->getPrimaryAccountNumbers()[0]->getFourDigit();
    if ($cardStatus == "DEACTIVATED" && $cardDescription == "MasterCard Metal" && ENABLE_METAL) {
        CardC::update(
            $cardId, /* card ID */
            null, /* pinCode */
            null, /* activationCode */
            'ACTIVE', /* status */
        );    
    }
    if ($cardStatus == "ACTIVE") {
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
// Save the API context to account for all the changes that might have occurred to it during the example execution
$bunq->updateContext();
?>
</table>
</body>
</html>
