<?php
const SHOW_INACTIVE_ACCOUNTS = FALSE;
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
        Account ID
      </th>
      <th>
        Description
      </th>
      <th>
        Owner name
      </th>
      <th>
        IBAN
      </th>
      <th>
        Balance
      </th>
    </tr>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use bunq\tinker\BunqLib;
use bunq\tinker\SharedLib;
$allOption = array("production" => "");
$environment = SharedLib::determineEnvironmentType($allOption);
$bunq = new BunqLib($environment);
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\BunqResponseInt;
use bunq\Model\Generated\Endpoint\MonetaryAccount;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\MonetaryAccountJoint;
use bunq\Model\Generated\Endpoint\MonetaryAccountLight;
use bunq\Model\Generated\Endpoint\MonetaryAccountSavings;
use bunq\Model\Generated\Object\Pointer;

foreach (MonetaryAccount::listing()->getValue() as $account) {
  accountTableEntry($account->getReferencedObject());
}

function accountTableEntry($account) {
  if (SHOW_INACTIVE_ACCOUNTS || $account->getStatus() == "ACTIVE") {
    echo "<tr><td>"; // new table row
    echo $account->getId() . "</td><td>";
    echo $account->getDescription() . "</td><td>";
    if ($account instanceof MonetaryAccountBank) {
      echo $account->getDisplayName();
    } else if ($account instanceof MonetaryAccountJoint || $account instanceof MonetaryAccountSavings) {
      if (! is_null($account->getAllCoOwner())) {
        foreach ($account->getAllCoOwner() as $coOwner) {
          echo $coOwner->getAlias()->getDisplayName();
          echo "<br>";
        }
      } else {
        // default to user's Legal Name if no Co-Owners are found
        echo User::get()->getValue()->getReferencedObject()->getLegalName();
      }
    }
    echo "</td><td>";
    foreach ($account->getAlias() as $pointer) {
      if ($pointer->getType() == "IBAN") {
        echo $pointer->getValue();
        break; // there should only be 1 IBAN, so this should not be necessary
      }
    }
    
    // always insert end of cell separately, to prevent problems if no IBAN is found
    echo "</td><td>";
    if ($account->getBalance()->getCurrency() == "EUR") {
      echo "€ ";
    }
    echo $account->getBalance()->getValue();
    echo "</td></tr>";
  }
}

// Save the API context to account for all the changes that might have occurred to it during the example execution
$bunq->updateContext();
?>
</table>
</body>
</html>
