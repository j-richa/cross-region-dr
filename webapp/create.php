<?php
require 'aws.phar';

use Aws\SecretsManager\SecretsManagerClient; 
use Aws\Exception\AwsException;

/**
 * Use an HTML form to create a new entry in the
 * users table.
 *
 */

if (isset($_POST['submit'])) {

// Create a Secrets Manager Client 
$client = new SecretsManagerClient([
    'version' => 'latest',
    'region' => 'us-east-1',
]);

$secretName = 'db-secret';

try {
    $result = $client->getSecretValue([
        'SecretId' => $secretName,
    ]);

} catch (AwsException $e) {
    $error = $e->getAwsErrorCode();
    throw $e;
}
// Decrypts secret using the associated KMS CMK.
// Depending on whether the secret is a string or binary, one of these fields will be populated.
if (isset($result['SecretString'])) {
    $secret = $result['SecretString'];
} else {
    $secret = base64_decode($result['SecretBinary']);
}

// Decode json
$jsonObj = json_decode($secret, true);

/**
 * Configuration for database connection
 *
 */

$host       = $jsonObj["host"];
$username   = $jsonObj["username"];
$password   = $jsonObj["password"];
$dbname     = "sample";
$dsn        = "mysql:host=$host;dbname=$dbname";
$options    = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
              );

/**
 * Escapes HTML for output
 *
 */

function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

    try  {
        $connection = new PDO($dsn, $username, $password, $options); 
        $new_order = array(
            "ordernumber"   => $_POST['ordernumber'],
            "customername"  => $_POST['customername'],
            "address"       => $_POST['address'],
            "item"      => $_POST['item'],
            "price"         => $_POST['price']
        );

        $sql = sprintf(
                "INSERT INTO %s (%s) values (%s)",
                "orders",
                implode(", ", array_keys($new_order)),
                ":" . implode(", :", array_keys($new_order))
        );

        $statement = $connection->prepare($sql);
        $statement->execute($new_order);
    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Book Store Management App</title>

        <style>
        label {
                margin-left: 5px 0;
        }

        table {
                margin-left: auto;
                margin-right: auto;
                border-collapse: collapse;
                border-spacing: 0;
        }

        td,
        th {
                padding: 5px;
                border-bottom: 1px solid #aaa;
        }

        img {
                display: block;
                margin-left: auto;
                margin-right: auto;
        }

        h1,h2,li,blockquote {
                text-align: center;
        }

        ul {
                display: table;
                margin: 0 auto;
        }

        a {
                display: block;
                margin-left: auto;
                margin-right: auto;
        }

        .overall {
                width: 20%;
                margin: auto;
        }

        .field {
                margin-bottom: 10px;
        }

        .field2 {
                width: 400px;
                text-align: center;
        }

        label {
                display: inline-block;
                width: 150px;
                text-align: right;
        }
        </style>

        <link rel="stylesheet" href="css/style.css">
</head>

<body>

        <h1>Book Store Management App</h1>

<?php if (isset($_POST['submit']) && $statement) { ?>
    <blockquote><?php echo $_POST['ordernumber']; ?> successfully added.</blockquote>
<?php } ?>

<h2>Add an order</h2>

<div class="overall">
<form method="post">
    <div class="field">
            <label for="ordernumber">Order Number</label>
            <input type="text" name="ordernumber" id="ordernumber">
    </div>
    <div class="field">
            <label for="customername">Customer Name</label>
            <input type="text" name="customername" id="customername">
    </div>
    <div class="field">
            <label for="address">Address</label>
            <input type="text" name="address" id="address">
    </div>
    <div class="field">
            <label for="BookName">Item</label>
            <input type="text" name="item" id="item">
    </div>
    <div class="field">
            <label for="price">Price</label>
            <input type="text" name="price" id="price">
    </div>
    <div class="field2">
            <input type="submit" name="submit" value="Submit">
    </div>
</form>
</div>

<br/>

<div class="overall">
        <div class="field2">
                <a href="index.php">Back to home</a>
        </div>
</div>
</body>

</html>