<?php
    class statsfunctions {
        public $lix;
        public $number;


        public function getLix($averageWordsString, $text, $amountOfLongWords) {
            $punctuations = preg_match_all('/[[:punct:]]/', $text);
            $totalWords = $averageWordsString;
            $this->lix = ($totalWords/$punctuations)+(($amountOfLongWords*100)/$totalWords);
            return $this->lix;
        }

        public function roundTo2Decimals($number) {
            $this->number = round($number,2);
            return $this->number;
        }
    }
?>


