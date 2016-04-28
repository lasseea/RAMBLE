<?php
include_once 'layout/header.php';
$result = DB::getInstance()->query('SELECT topics.id, messages.text FROM topics JOIN messages ON topics.id = messages.topic_id');
$averageWordsArray = array();
$averageLixNumberArray = array();
$messageCountPerTitleIdArray = array();
$mostFrequentWordArray = array();

foreach ($result->results() as $topic) {
    $mostFrequentWord = array();
    $amountOfLongWords = 0;
    $averageWordsString = str_word_count($topic->text);
    $totalAmountOfWordsInSentence = str_word_count($topic->text,1);
    foreach ($totalAmountOfWordsInSentence as $word) {
        if (strlen($word)> 6) {
            $amountOfLongWords++;
        }
    }
    $averageLixNumber = ($averageWordsString/preg_match_all('/[[:punct:]]/', $topic->text))+(($amountOfLongWords*100)/count($totalAmountOfWordsInSentence));
    if (array_key_exists($topic->id, $averageWordsArray)) {
        $messageCountPerTitleIdArray[$topic->id]++;
        $averageWordsArray[$topic->id] = (($averageWordsArray[$topic->id]*($messageCountPerTitleIdArray[$topic->id]-1))+$averageWordsString)/$messageCountPerTitleIdArray[$topic->id];
        $averageLixNumberArray[$topic->id] = (($averageLixNumberArray[$topic->id]*($messageCountPerTitleIdArray[$topic->id]-1))+$averageLixNumber)/$messageCountPerTitleIdArray[$topic->id];
        $mostFrequentWord = array_count_values($totalAmountOfWordsInSentence);
        arsort($mostFrequentWord);
        $mostFrequentWordArray[$topic->id] = key($mostFrequentWord);
    } else {
        $mostFrequentWord = array_count_values($totalAmountOfWordsInSentence);
        arsort($mostFrequentWord);
        $mostFrequentWordArray[$topic->id] = key($mostFrequentWord);
        $averageWordsArray[$topic->id] = $averageWordsString;
        $messageCountPerTitleIdArray[$topic->id] = 1;
        $averageLixNumberArray[$topic->id] = $averageLixNumber;
    }
}



$result = DB::getInstance()->query('SELECT topics.id, authors.author_name, topics.createdAt, topics.title FROM authors JOIN topics ON topics.author_id = authors.id');
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
        </tr>
        </thead>
        <tbody>
        <?php
foreach($result->results() as $partResult){
    ?>
    <tr>
        <td><?php echo $partResult->createdAt ?> </td>
        <td><?php echo $partResult->author_name?></td>
        <td><?php echo "<a href='#'>".$partResult->title."</a>" ?></td>
        <td>
            <?php
            if (array_key_exists($partResult->id, $averageWordsArray)) {
                echo (round($averageWordsArray[$partResult->id],2));
            } else {
                echo "No messages";
            }
            ?>
        </td>
        <td>
            <?php
            if (array_key_exists($partResult->id, $averageLixNumberArray)) {
                echo (round($averageLixNumberArray[$partResult->id],2));
            } else {
                echo "No messages";
            }
            ?>
        </td>
        <td>
            <?php
            if (array_key_exists($partResult->id, $mostFrequentWordArray)) {
                echo $mostFrequentWordArray[$partResult->id];
            } else {
                echo "No messages";
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
$result = DB::getInstance()->query("SELECT topics.createdAt, COUNT(*) as amount FROM topics GROUP BY topics.createdAt");
foreach($result->results() as $partResult) {
    $createdAt = $partResult->createdAt;
    echo "'$createdAt', ";
}
?>

             ],
        datasets: [{
            label: '# of Topics',
            data: [
            <?php
foreach($result->results() as $partResult) {
    $amountOnDate = $partResult->amount;
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