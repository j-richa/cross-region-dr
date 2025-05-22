<?php

require 'aws.phar';

use Aws\SecretsManager\SecretsManagerClient; 
use Aws\Exception\AwsException;

/**
 * Function to query information based on 
 * a parameter: in this case, location.
 *
 */

if (isset($_POST['submit'])) {
    try  {

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

        $connection = new PDO($dsn, $username, $password, $options);

        $sql = "SELECT *
                        FROM orders";

        $statement = $connection->prepare($sql);

        $statement->execute();

        $result = $statement->fetchAll();
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
                display: block;
                margin: 5px 0;
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

        input {
                width: 10%;
                margin-left: 45%;
        }
        </style>
</head>

<body>

        <h1>Book Store App</h1>

        <ul>
                <li><a href="create.php"><strong>Create</strong></a> - add an order</li>
        </ul>
<?php
if (isset($_POST['submit'])) {
    if ($result && $statement->rowCount() > 0) { ?>
        <h2>Results</h2>

        <table>
            <thead>
                <tr>
                    <th>Serial #</th>
                    <th>Order Number</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Book Name</th>
                    <th>Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
        <?php foreach ($result as $row) { ?>
            <tr>
                <td><?php echo escape($row["id"]); ?></td>
                <td><?php echo escape($row["ordernumber"]); ?></td>
                <td><?php echo escape($row["customername"]); ?></td>
                <td><?php echo escape($row["address"]); ?></td>
                <td><?php echo escape($row["item"]); ?></td>
                <td><?php echo escape($row["price"]); ?></td>
                <td><?php echo escape($row["date"]); ?> </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } else { ?>
        <blockquote>No results found.</blockquote>
    <?php }
} ?>

<h2>Get existing orders</h2>

<form method="post">
    <input type="submit" name="submit" value="View Results">
</form>

</body>

</html>