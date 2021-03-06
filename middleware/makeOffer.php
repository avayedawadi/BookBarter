<?php
// Allow Cross Origin Requests (other ips can request data)
header("Access-Control-Allow-Origin: *");

// Load the JWT library
require_once __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;

$jwt = htmlspecialchars_decode($_GET["jwt"]);
$usr = htmlspecialchars_decode($_GET["otherUsername"]);
$isbn = htmlspecialchars_decode($_GET["isbn"]);
$replaceVal = htmlspecialchars_decode($_GET["replaceisbn"]);

$credentials = json_decode(include "verifyJWT.php");
if($credentials == "This key has been tampered with or is out of date." || $credentials == "Please Provide a JSON Web Token.") {
    echo $credentials;
    return $credentials;
}

$client = new MongoDB\Client('mongodb+srv://dbrunner:AWsAcctcHfb1g8FG@cluster0-vixlf.mongodb.net/hackathon?retryWrites=true&w=majority');
$collection = $client->hackathon->traderoom;
$newCollec = $client->hackathon->userdata;

$title="";
$author="";

$document = $newCollec->findOne(["username" => $credentials->username]);
foreach($document->books as $book) {
    if($book->ISBN == $replaceVal) {
        $title = $book->title;
        $author = $book->author;
    break;
    }
}

$collection->updateOne(["seller" => $usr, "isbn" => $isbn], ['$push' => 
    ['queue' => [
        "time" => time(),
        "username" => $credentials->username,
        "replacementISBN" => $replaceVal,
        "email" => $credentials->email,
        "author" => $author,
        "title" => $title
    ]
]]);

echo "Request added sucessfully";
?>
