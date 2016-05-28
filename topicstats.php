<?php
include_once 'layout/header.php';
include_once 'functions/statsfunctions.php';
$m = new MongoClient();
$db = $m->ramble;
$coll = $db->messagesData;
$cursor = $coll->find();

$statsFunctions = new statsfunctions();
//SQL: $result = DB::getInstance()->query('SELECT topics.id, messages.text, messages.author_id, votes.upvote_or_downvote FROM topics JOIN messages ON topics.id = messages.topic_id LEFT JOIN votes ON votes.message_id = messages.id');
//Declaring arrays to be used in the dataTable
$averageWordsArray = array();
$averageLixNumberArray = array();
$messageCountPerTitleIdArray = array();
$mostFrequentWordArray = array();
$highestRatedMessageArray = array();
$LowestRatedMessageArray = array();

//Looping through each topic
foreach ($cursor as $doc) {
    $mostFrequentWord = array();
    $amountOfLongWords = 0;
    $averageWordsString = str_word_count($doc["text"]);
    $totalAmountOfWordsInSentence = str_word_count($doc["text"],1);
    foreach ($totalAmountOfWordsInSentence as $word) {
        if (strlen($word)> 6) {
            $amountOfLongWords++;
        }
    }
    $averageLixNumber = $statsFunctions->getLix($averageWordsString, $doc["text"], $amountOfLongWords);
    if (array_key_exists($doc["id"], $averageWordsArray)) {
        $messageCountPerTitleIdArray[$doc["id"]]++;
        $averageWordsArray[$doc["id"]] = (($averageWordsArray[$doc["id"]]*($messageCountPerTitleIdArray[$doc["id"]]-1))+$averageWordsString)/$messageCountPerTitleIdArray[$doc["id"]];
        $averageLixNumberArray[$doc["id"]] = (($averageLixNumberArray[$doc["id"]]*($messageCountPerTitleIdArray[$doc["id"]]-1))+$averageLixNumber)/$messageCountPerTitleIdArray[$doc["id"]];
        $mostFrequentWord = array_count_values($totalAmountOfWordsInSentence);
        arsort($mostFrequentWord);
        $mostFrequentWordArray[$doc["id"]] = key($mostFrequentWord);
        if ($doc["upvote_or_downvote"] == 1) {
            $highestRatedMessageArray[$doc["id"]]++;
        } else if ($doc["upvote_or_downvote"] == -1) {
            $lowestRatedMessageArray[$doc["id"]]++;
        }
    } else {
        $mostFrequentWord = array_count_values($totalAmountOfWordsInSentence);
        arsort($mostFrequentWord);
        $mostFrequentWordArray[$doc["id"]] = key($mostFrequentWord);
        $averageWordsArray[$doc["id"]] = $averageWordsString;
        $messageCountPerTitleIdArray[$doc["id"]] = 1;
        $averageLixNumberArray[$doc["id"]] = $averageLixNumber;
        if ($doc["upvote_or_downvote"] == 1) {
            $highestRatedMessageArray[$doc["id"]] = 1;
        } else if ($doc["upvote_or_downvote"] == -1) {
            $lowestRatedMessageArray[$doc["id"]] = 1;
        } else {
            $highestRatedMessageArray[$doc["id"]] = 0;
            $lowestRatedMessageArray[$doc["id"]] = 0;
        }
    }
}


//Selecting data for the dataTable
$coll = $db->topicData;
$cursor = $coll->find();
//SQL: $result = DB::getInstance()->query('SELECT topics.id, authors.author_name, topics.createdAt, topics.title FROM authors JOIN topics ON topics.author_id = authors.id');
?>

    <h3><b>Topics Information Table</b></h3></br>

    <table id="datatable" class="stripe hover">
        <thead>
        <tr>
            <th>Created At</th>
            <th>Author</th>
            <th>Topic Title</th>
            <th>Average Word Count</th>
            <th>Average Lix Number</th>
            <th>Most Frequent Word</th>
            <th>Total upvotes in topic</th>
            <th>Total downvotes in topic</th>
        </tr>
        </thead>
        <tbody>
        <?php
foreach($cursor as $doc){
    ?>
    <tr>
        <td><?php echo $doc["createdAt"] ?> </td>
        <td><?php echo $doc["author_name"]?></td>
        <td><?php echo "<a href='#'>".$doc["title"]."</a>" ?></td>
        <td>
        <?php
        if (array_key_exists($doc["id"], $averageWordsArray)) {
            echo ($statsFunctions->roundTo2Decimals($averageWordsArray[$doc["id"]]));
        } else {
            echo "No messages";
        }
        ?>
        </td>
        <td>
            <?php
            if (array_key_exists($doc["id"], $averageLixNumberArray)) {
                echo ($statsFunctions->roundTo2Decimals($averageLixNumberArray[$doc["id"]]));
            } else {
                echo "No messages";
            }
            ?>
        </td>
        <td>
            <?php
            if (array_key_exists($doc["id"], $mostFrequentWordArray)) {
                echo $mostFrequentWordArray[$doc["id"]];
            } else {
                echo "No messages";
            }
            ?>
        </td>
        <td>
            <?php
            if (array_key_exists($doc["id"], $highestRatedMessageArray)) {
                echo $highestRatedMessageArray[$doc["id"]];
            } else {
                echo 0;
            }
            ?>
        </td>
        <td>
            <?php
            if (array_key_exists($doc["id"], $lowestRatedMessageArray)) {
                echo $lowestRatedMessageArray[$doc["id"]];
            } else {
                echo 0;
            }
            ?>
        </td>
    </tr>
<?php
}
?>
        </tbody>
    </table>
    <h3><b>Amount of topics on dates</b></h3></br>

 <canvas id="myChart" width="400" height="400"></canvas>
<script>
var ctx = document.getElementById("myChart");
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
        <?php
//Selecting data for the bar chart
$coll = $db->voteData;
$cursor = $coll->find();
// SQL: $result = DB::getInstance()->query("SELECT topics.createdAt, COUNT(*) as amount FROM topics GROUP BY topics.createdAt");
foreach($cursor as $doc) {
    $createdAt = $doc["createdAt"];
    echo "'$createdAt', ";
}
?>

             ],
        datasets: [{
            label: '# of Topics',
            data: [
            <?php
$coll = $db->voteData;
$cursor = $coll->find();
foreach($cursor as $doc) {
    $amountOnDate = $doc["amount"];
    echo "'$amountOnDate', ";
}
?>



            ]
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
</script>

<?php
include_once 'layout/footer.php'
?>