<?php
include 'emotions.php';
require 'vendor/autoload.php';
use Aws\DynamoDb\DynamoDbClient;
date_default_timezone_set('UTC');
use phpseclib\Net\SFTP;


//Step 1: SFTP to rename old sentences-sunburst.json file to 'sentences-sunburst-mmddyy.json'

$sftp = new SFTP('evrmore.io.dream.website');
if (!$sftp->login('wp_vv8fh5', '3vrM0re2020')) {
    exit('Login Failed');
}

//create currentdate
$newfilename = '/home/wp_vv8fh5/evrmore.io/wp-content/themes/sentences-sunburst-';
$d1 = new DateTime("now");
$date = $d1->format('mdy');
$newfilename.= $date;
$newfilename.= '.json';
$sftp->rename('/home/wp_vv8fh5/evrmore.io/wp-content/themes/sentences-sunburst.json', $newfilename);


//initial declarations
$stringmaster = '';
$sentenceobjects = array();
$primary = $secondary = '';
$emotioncounts = array(
            'joy' => 0,
            'trust' => 0,
            'fear' => 0,
            'surprise' => 0,
            'sadness' => 0,
            'disgust' => 0,
            'anger' => 0,
            'anticipation' => 0
        );


//Sentence object class
class Sentence {
    public $content;
    public $totalwords;
    public $joycount;
    private $joywordsinsentence;
    public $trustcount;
    private $trustwordsinsentence;
    public $fearcount;
    private $fearwordsinsentence;
    public $surprisecount;
    private $surprisewordsinsentence;
    public $sadnesscount;
    private $sadnesswordsinsentence;
    public $disgustcount;
    private $disgustwordsinsentence;
    public $angercount;
    private $angerwordsinsentence;
    public $anticipationcount;
    private $anticipationwordsinsentence;
    public $movement;
    public $flare;
    public $label;
    public $size;
    public $color;
    public $emotionwords;
    public $emotion;
    function __construct($content) {
        $this->content = strtolower($content);
        $this->set_totalwords();
        $this->set_joycount();
        $this->set_trustcount();
        $this->set_fearcount();
        $this->set_surprisecount();
        $this->set_sadnesscount();
        $this->set_disgustcount();
        $this->set_angercount();
        $this->set_anticipationcount();
        $this->set_movement();
        $this->set_flareandlabelandcolor();
        $this->set_size();
    }
    //set functions
    function set_joycount() {
        $joy = 0;
        global $joywords;
        $joyinsentence = array();
        foreach($joywords as $joyword) {
            $result = preg_match_all('/\b'. $joyword .'\b/', $this->get_content());
            $joy += $result;
            if($result > 0) {
                array_push($joyinsentence, $joyword);
            }
        }
        $this->joycount = $joy;
        $this->joywordsinsentence = $joyinsentence;
    }
    function set_trustcount() {
        $trust = 0;
        global $trustwords;
        $trustinsentence = array();
        foreach($trustwords as $trustword) {
            $result = preg_match_all('/\b'. $trustword .'\b/', $this->get_content());
            $trust += $result;
            if($result > 0) {
                array_push($trustinsentence, $trustword);
            }
        }
        $this->trustcount = $trust;
        $this->trustwordsinsentence = $trustinsentence;
    }
    function set_fearcount() {
        $fear = 0;
        global $fearwords;
        $fearinsentence = array();
        foreach($fearwords as $fearword) {
            $result = preg_match_all('/\b'. $fearword .'\b/', $this->get_content());
            $fear += $result;
            if($result > 0) {
                array_push($fearinsentence, $fearword);
            }
        }
        $this->fearcount = $fear;
        $this->fearwordsinsentence = $fearinsentence;
    }
    function set_surprisecount() {
        $surprise = 0;
        global $surprisewords;
        $surpriseinsentence = array();
        foreach($surprisewords as $surpriseword) {
            $result = preg_match_all('/\b'. $surpriseword .'\b/', $this->get_content());
            $surprise += $result;
            if($result > 0) {
                array_push($surpriseinsentence, $surpriseword);
            }
        }
        $this->surprisecount = $surprise;
        $this->surprisewordsinsentence = $surpriseinsentence;
    }
    function set_sadnesscount() {
        $sadness = 0;
        global $sadnesswords;
        $sadnessinsentence = array();
        foreach($sadnesswords as $sadnessword) {
            $result = preg_match_all('/\b'. $sadnessword .'\b/', $this->get_content());
            $sadness += $result;
            if($result > 0) {
                array_push($sadnessinsentence, $sadnessword);
            }
        }
        $this->sadnesscount = $sadness;
        $this->sadnesswordsinsentence = $sadnessinsentence;
    }
    function set_disgustcount() {
        $disgust = 0;
        global $disgustwords;
        $disgustinsentence = array();
        foreach($disgustwords as $disgustword) {
            $result = preg_match_all('/\b'. $disgustword .'\b/', $this->get_content());
            $disgust += $result;
            if($result > 0) {
                array_push($disgustinsentence, $disgustword);
            }
        }
        $this->disgustcount = $disgust;
        $this->disgustwordsinsentence = $disgustinsentence;
    }
    function set_angercount() {
        $anger = 0;
        global $angerwords;
        $angerinsentence = array();
        foreach($angerwords as $angerword) {
            $result = preg_match_all('/\b'. $angerword .'\b/', $this->get_content());
            $anger += $result;
            if($result > 0) {
                array_push($angerinsentence, $angerword);
            }
        }
        $this->angercount = $anger;
        $this->angerwordsinsentence = $angerinsentence;
    }
    function set_anticipationcount() {
        $anticipation = 0;
        global $anticipationwords;
        $anticipationinsentence = array();
        foreach($anticipationwords as $anticipationword) {
            $result = preg_match_all('/\b'. $anticipationword .'\b/', $this->get_content());
            $anticipation += $result;
            if($result > 0) {
                array_push($anticipationinsentence, $anticipationword);
            }
        }
        $this->anticipationcount = $anticipation;
        $this->anticipationwordsinsentence = $anticipationinsentence;
    }

    function set_movement() {
        global $forwardwords;
        global $backwardwords;
        foreach($forwardwords as $forwardword) {
            $this->movement += substr_count($this->get_content(), $forwardword);
            $this->movement += 3 * (substr_count($this->get_content(), strtoupper($forwardword)));
            $this->movement += 3 * (substr_count($this->get_content(), $forwardword."!"));
        }
        foreach($backwardwords as $backwardword) {
            $this->movement -= substr_count($this->get_content(), $backwardword);
            $this->movement -= 3 * (substr_count($this->get_content(), strtoupper($backwardword)));
            $this->movement -= 3 * (substr_count($this->get_content(), $backwardword."!"));
        }
    }
    function set_totalwords() {
        $this->totalwords = str_word_count($this->get_content());
    }
    function set_size() {
        if($this->get_totalwords() == 0) {
            $this->size = 0;
        } else {
            if ((($this->get_joycount() + $this->get_trustcount() + $this->get_fearcount() + $this->get_surprisecount() + $this->get_sadnesscount() + $this->get_disgustcount() + $this->get_angercount() + $this->get_anticipationcount()) / $this->get_totalwords()) >= 1) {
                $this -> size = 1;
            } else {
                $this -> size = (($this->get_joycount() + $this->get_trustcount() + $this->get_fearcount() + $this->get_surprisecount() + $this->get_sadnesscount() + $this->get_disgustcount() + $this->get_angercount() + $this->get_anticipationcount()) / $this->get_totalwords());
            }
        }
    }
    function set_flareandlabelandcolor() {
        $words = array();
        $sentencewords = array();
        $indexes = array();
        $filewords = array();
        $filetoget = '';
        $thecolor = '';
        //set color to text file
        $color_array = array(
            'yellow' => $this->get_joycount(),
            'light green' => $this->get_trustcount(),
            'dark green' => $this->get_fearcount(),
            'light blue' => $this->get_surprisecount(),
            'dark blue' => $this->get_sadnesscount(),
            'pink' => $this->get_disgustcount(),
            'red' => $this->get_angercount(),
            'orange' => $this->get_anticipationcount()
        );
        $value = max($color_array);
        $thecolor = array_search($value, $color_array);
        $this -> color = $thecolor;
        //print_r($color_array);
        //var_dump($thecolor);
        if($thecolor == 'yellow'){
            $filetoget = 'joy.txt';
            $this->emotionwords = $this->get_joywordsinsentence();
            $this->emotion = 'joy';
        } elseif ($thecolor == 'light green'){
            $filetoget = 'trust.txt';
            $this->emotionwords = $this->get_trustwordsinsentence();
            $this->emotion = 'trust';
        } elseif ($thecolor == 'dark green'){
            $filetoget = 'fear.txt';
            $this->emotionwords = $this->get_fearwordsinsentence();
            $this->emotion = 'fear';
        } elseif ($thecolor == 'light blue'){
            $filetoget = 'surprise.txt';
            $this->emotionwords = $this->get_surprisewordsinsentence();
            $this->emotion = 'surprise';
        } elseif ($thecolor == 'dark blue'){
            $filetoget = 'sadness.txt';
            $this->emotionwords = $this->get_sadnesswordsinsentence();
            $this->emotion = 'sadness';
        } elseif ($thecolor == 'pink'){
            $filetoget = 'disgust.txt';
            $this->emotionwords = $this->get_disgustwordsinsentence();
            $this->emotion = 'disgust';
        } elseif ($thecolor == 'red'){
            $filetoget = 'anger.txt';
            $this->emotionwords = $this->get_angerwordsinsentence();
            $this->emotion = 'anger';
        } elseif ($thecolor == 'orange'){
            $filetoget = 'anticipation.txt';
            $this->emotionwords = $this->get_anticipationwordsinsentence();
            $this->emotion = 'anticipation';
        }
        //var_dump($filetoget);
        $filewords = file($filetoget);
        foreach($filewords as $line_num => $line) {
            $parts = preg_split('/\s+/', $line);
            //part supposed to split sentences
            $result = preg_match_all('/\b'. $parts[0] .'\b/', $this->get_content());
            if($result > 0) {
                $this -> label = $parts[0];
                $this -> flare = $parts[1];
                break 1;
            }
        }
    }
    // get functions
     function get_totalwords() {
        return $this->totalwords;
    }
    function get_content() {
        return $this->content;
    }
    function get_joycount() {
        return $this->joycount;
    }
    function get_trustcount() {
        return $this->trustcount;
    }
    function get_fearcount() {
        return $this->fearcount;
    }
    function get_surprisecount() {
        return $this->surprisecount;
    }
    function get_sadnesscount() {
        return $this->sadnesscount;
    }
    function get_disgustcount() {
        return $this->disgustcount;
    }
    function get_angercount() {
        return $this->angercount;
    }
    function get_anticipationcount() {
        return $this->anticipationcount;
    }
    function get_color() {
        return $this->color;
    }
    function get_size() {
        return $this->size;
    }
    function get_flare() {
        return $this->flare;
    }
    function get_label() {
        return $this->label;
    }
    function get_joywordsinsentence() {
        return $this->joywordsinsentence;
    }
    function get_trustwordsinsentence() {
        return $this->trustwordsinsentence;
    }
    function get_fearwordsinsentence() {
        return $this->fearwordsinsentence;
    }
    function get_surprisewordsinsentence() {
        return $this->surprisewordsinsentence;
    }
    function get_sadnesswordsinsentence() {
        return $this->sadnesswordsinsentence;
    }
    function get_disgustwordsinsentence() {
        return $this->disgustwordsinsentence;
    }
    function get_angerwordsinsentence() {
        return $this->angerwordsinsentence;
    }
    function get_anticipationwordsinsentence() {
        return $this->anticipationwordsinsentence;
    }
    function get_emotion() {
        return $this->emotion;
    }
    function get_emotionwords() {
        return $this->emotionwords;
    }
}


//Step 2: Scan DynamoDB refinedData and store all data combined into one big string $stringmaster
$client = DynamoDbClient::factory(array(
    'region'  => 'us-west-2',
    'version' => '2012-08-10'
));

$scan = $client->getIterator('Scan', array('TableName' => 'refinedData'));
foreach ($scan as $item) {
    //extract everything from item into string all
    //call stringall = remove(stringall)
    $stringall = '';
    $stringall.= $item["URL"]["S"];
    $stringall.= $item["body"]["S"];
    $stringall.= $item["description, comments"]["S"];
    $stringall.= $item["div"]["S"];
    $stringall.= $item["h1"]["S"];
    $stringall.= $item["h2"]["S"];
    $stringall.= $item["img"]["S"];
    $stringall.= $item["p"]["S"];
    $stringall.= $item["span"]["S"];
    $stringall.= $item["table"]["S"];
    $stringall.= $item["tbody"]["S"];
    $stringall.= $item["td"]["S"];
    $stringall.= $item["title, caption"]["S"];
    $stringmaster.=$stringall;
}



function remove($string) {
    $new = str_replace("\n","",$string);
    return $new;
}


//Step 3: Convert $stringmaster into Sentence Objects
$sentencesarray = preg_split('/(?<=[.?!;:])\s+/', $stringmaster, -1, PREG_SPLIT_NO_EMPTY);
foreach($sentencesarray as $sentence) {
    if($sentence !== '.'){
        $newsentence = new Sentence($sentence);
        if ($newsentence -> get_flare() !== null && $newsentence -> get_label() !== null) {
          $sentenceobjects[] = $newsentence; 
        }
    } 
}

//set emotion labels
$joylabels = array();
$trustlabels = array();
$fearlabels = array();
$surpriselabels = array();
$sadnesslabels = array();
$disgustlabels = array();
$angerlabels = array();
$anticipationlabels = array();
//for loop through sentenceobjects
foreach($sentenceobjects as $sentenceobject) {
    //if sentenceobject is joy
    if($sentenceobject -> get_emotion() == "joy"){
        $emotioncounts['joy'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'ffb7e4');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => 'ff6cc7');
        //add toaddarray to joylabels
        $joylabels[] = $toaddarray;
    }
    //if sentenceobject is trust
    if($sentenceobject -> get_emotion() == "trust"){
        $emotioncounts['trust'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'ffe39a');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => 'ffd567');
        //add toaddarray to trustlabels
        $trustlabels[] = $toaddarray;
    }
    //if sentenceobject is fear
    if($sentenceobject -> get_emotion() == "fear"){
        $emotioncounts['fear'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'd6b7ff');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => 'ae70ff');
        //add toaddarray to fearlabels
        $fearlabels[] = $toaddarray;
    }
    //if sentenceobject is surprise
    if($sentenceobject -> get_emotion() == "surprise"){
        $emotioncounts['surprise'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'beefff');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => '7de0ff');
        //add toaddarray to surpriselabels
        $surpriselabels[] = $toaddarray;
    }
    //if sentenceobject is sadness
    if($sentenceobject -> get_emotion() == "sadness"){
        $emotioncounts['sadness'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'b9bbff');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => '7f82ff');
        //add toaddarray to sadnesslabels
        $sadnesslabels[] = $toaddarray;
    }
    //if sentenceobject is disgust
    if($sentenceobject -> get_emotion() == "disgust"){
        $emotioncounts['disgust'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'ffdbb0');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => 'ffbf73');
        //add toaddarray to disgustlabels
        $disgustlabels[] = $toaddarray;
    }
    //if sentenceobject is anger
    if($sentenceobject -> get_emotion() == "anger"){
        $emotioncounts['anger'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'e4ff7c');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => 'f0ffb8');
        //add toaddarray to angerlabels
        $angerlabels[] = $toaddarray;
    }
    //if sentenceobject is anticipation
    if($sentenceobject -> get_emotion() == "anticipation"){
        $emotioncounts['anticipation'] += 1;
        $temparray = array();
        foreach($sentenceobject -> get_emotionwords() as $emotionword){
            $temparray1 = array("name" => "$emotionword", "size" => 1, "color" => 'f672ff');
            //add temparray1 to temparray
            $temparray[] = $temparray1;
        }
        $toaddarray = array("name" => $sentenceobject -> get_label(), "children" => $temparray, "color" => 'fbbbff');
        //add toaddarray to anticipationlabels
        $anticipationlabels[] = $toaddarray;
    }
}

//set most common emotion
$mostcommonemotion = '';
$value = max($emotioncounts);
$theemotion = array_search($value, $emotioncounts);
$mostcommonemotion = $theemotion;

//set most common color
$mostcommonemotioncount = 0;
$mostcommoncolor = '';
if($mostcommonemotion == 'joy') {
    $mostcommoncolor = 'ff00a8';
    $mostcommonemotioncount = $emotioncounts['joy'];
}else if($mostcommonemotion == 'trust'){
    $mostcommoncolor = 'ffc600';
    $mostcommonemotioncount = $emotioncounts['trust'];
}else if($mostcommonemotion == 'fear'){
    $mostcommoncolor = '8300ff';
    $mostcommonemotioncount = $emotioncounts['fear'];
}else if($mostcommonemotion == 'surprise'){
    $mostcommoncolor = '00c7ff';
    $mostcommonemotioncount = $emotioncounts['surprise'];
}else if($mostcommonemotion == 'sadness'){
    $mostcommoncolor = '1b00ff';
    $mostcommonemotioncount = $emotioncounts['sadness'];
}else if($mostcommonemotion == 'disgust'){
    $mostcommoncolor = 'ff9c00';
    $mostcommonemotioncount = $emotioncounts['disgust'];
}else if($mostcommonemotion == 'anger'){
    $mostcommoncolor = 'c1ea00';
    $mostcommonemotioncount = $emotioncounts['anger'];
}else if($mostcommonemotion == 'anticipation'){
    $mostcommoncolor = 'f000ff';
    $mostcommonemotioncount = $emotioncounts['anticipation'];
}

//set percentage of most common emotional category
$percentage = floor(($mostcommonemotioncount / count($sentenceobjects)) * 100);

//set emotion sentences arrays
$joysentences = array("name" => "joy", "children" => $joylabels, "color" => 'ff00a8');
$trustsentences = array("name" => "trust", "children" => $trustlabels, "color" => 'ffc600');
$fearsentences = array("name" => "fear", "children" => $fearlabels, "color" => '8300ff');
$surprisesentences = array("name" => "surprise", "children" => $surpriselabels, "color" => '00c7ff');
$sadnesssentences = array("name" => "sadness", "children" => $sadnesslabels, "color" => '1b00ff');
$disgustsentences = array("name" => "disgust", "children" => $disgustlabels, "color" => 'ff9c00');
$angersentences = array("name" => "anger", "children" => $angerlabels, "color" => 'c1ea00');
$anticipationsentences = array("name" => "anticipation", "children" => $anticipationlabels, "color" => 'f000ff');

$sunburstdata = array("name" => "emotional-categories", "color" => $mostcommoncolor, "percentage" => $percentage, "mostcommonemotioncount" => $mostcommonemotioncount, "children" => array($joysentences, $trustsentences, $fearsentences, $surprisesentences, 
$sadnesssentences, $disgustsentences, $angersentences, $anticipationsentences));



//Step 4: Delete sentences-sunburst.json file
unlink('sentences-sunburst.json');

//Step 5: Process Sentence Objects into sentences-sunburst.json file
$fp = fopen('sentences-sunburst.json', 'w');
fwrite($fp, json_encode($sunburstdata, JSON_PRETTY_PRINT));
fclose($fp);


//Step 6: SFTP new-sentences-sunburst.json file to WP File Manager
$sftp->put('/home/wp_vv8fh5/evrmore.io/wp-content/themes/sentences-sunburst.json', 'sentences-sunburst.json', SFTP::SOURCE_LOCAL_FILE);


//Step 7: Delete DynamoDB refinedData table
$client->deleteTable(array(
    'TableName' => 'refinedData'
));
    
$client->waitUntil('TableNotExists', array(
    'TableName' => 'refinedData'
));

//Step 8: Create DynamoDB refinedData table
$client->createTable(array(
        'TableName' => 'refinedData',
        'AttributeDefinitions' => array(
            array(
                'AttributeName' => 'URL',
                'AttributeType' => 'S'
            )
        ),
        'KeySchema' => array(
            array(
                'AttributeName' => 'URL',
                'KeyType'       => 'HASH'
            )
        ),
        'ProvisionedThroughput' => array(
            'ReadCapacityUnits'  => 500,
            'WriteCapacityUnits' => 30
        )
));
$client->waitUntil('TableExists', array(
        'TableName' => 'refinedData'
));


?>
