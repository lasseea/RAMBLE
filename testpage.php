<?php
$m = new MongoClient();
$db = $m->ramble;
echo "Connected to database ramble</br>";

$coll = $db->authors;

$cursor = $coll->find();

foreach ($cursor as $doc) {
    echo ($doc["author_name"]);
}

echo "Documents found";