<?php
require_once 'functions/statsfunctions.php';

class FunctionsTest extends PHPUnit_Framework_TestCase {
    public $test;

    public function setUp() {
        $this->test = new statsfunctions();
    }

    //Calculating Lix Number of string
    public function testGetLix() {
        $averageLix = $this->test->getLix(5, "The Lix Formula was developed.", 2);
        $this->assertTrue($averageLix == 45);
    }

    //Rounding number to only 2 decimals
    public function testRoundTo2Decimals() {
        $roundedNumber = $this->test->roundTo2Decimals(23,4444);
        $this->assertTrue($roundedNumber == 23,44);
    }
}

