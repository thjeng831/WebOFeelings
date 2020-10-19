<?php
/**
* @package EvrmoreCrawlerPlugin
 */

//$file = 'sample-app.log';
//$message0 = sprintf("Entered evrmorewebcrawler.php");
//file_put_contents($file, date('Y-m-d H:i:s') . $message0 . "\n", FILE_APPEND);
$start = "http://www.evrmorewebcrawlerstart.com.s3-website-us-west-2.amazonaws.com";
$already_crawled = array();
$maxCapacity = 20000;
$numberposts = 1;
$numbercomments = 50;
//This is the portion incorporating the Amazon DynamoDB 
require 'vendor/autoload.php';
use Aws\DynamoDb\DynamoDbClient;
date_default_timezone_set('UTC');
$client = DynamoDbClient::factory(array(
    'region'  => 'us-west-2',
    'version' => '2012-08-10'
));

//$message0 = sprintf("Starting task 1");
//file_put_contents($file, date('Y-m-d H:i:s') . $message0 . "\n", FILE_APPEND);

//Step 1: this creates webcrawlerdata table
//check if table does not exist
try {
    $exists = $client->describeTable(array(
        'TableName' => 'webcrawlerdata'
    ));
} catch(Exception $e) {
    //Table does not exist: Create table
    //echo "Resource not found:\n";
    $client->createTable(array(
        'TableName' => 'webcrawlerdata',
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
            'ReadCapacityUnits'  => 30,
            'WriteCapacityUnits' => 30
        )
    ));
    $client->waitUntil('TableExists', array(
        'TableName' => 'webcrawlerdata'
    ));
} catch (DynamoDbException $e) {
    //Some other error happened
    //echo "Error Happened:\n";
    echo $e->getMessage() . "\n";
}
//delete table
$client->deleteTable(array(
    'TableName' => 'webcrawlerdata'
));
    
$client->waitUntil('TableNotExists', array(
    'TableName' => 'webcrawlerdata'
));
//create table
$client->createTable(array(
        'TableName' => 'webcrawlerdata',
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
            'ReadCapacityUnits'  => 30,
            'WriteCapacityUnits' => 30
        )
));
$client->waitUntil('TableExists', array(
        'TableName' => 'webcrawlerdata'
));

//$message0 = sprintf("Finished Step 1");
//file_put_contents($file, date('Y-m-d H:i:s') . $message0 . "\n", FILE_APPEND);


//This is the portion incorporating the Instagram Library
use Phpfastcache\Helper\Psr16Adapter;
//$instagram = new \InstagramScraper\Instagram();

//need to login with account now
$instagram = \InstagramScraper\Instagram::withCredentials('thomasevrmore', 'dummyaccount', new Psr16Adapter('Files'));
$instagram->login();
$instagram->saveSession();

//this is the normal crawl function
function crawl($url) {
    global $already_crawled;
    global $instagram;
    global $numberposts;
    $options = array('http' => array('method' => "GET", 'headers' => "User-Agent: evrmorewebcrawler/1.0\n"));
    $context = stream_context_create($options);
    //creating user agent
    $doc = new DOMDocument();
    @$doc->loadHTML(@file_get_contents($url, false, $context));
    $linklist = $doc->getElementsByTagName("a");
    //gets all the links on the page
    $h1main = '';
    $h1tagsmain = $doc->getElementsByTagName('h1');
    foreach ($linklist as $link) {
        $l = $link->getAttribute("href");
        //sets each link to l
        $l = standardize_link($url, $l);
        //this deals for different types of links
        //checks for instagram and Facebook go here
        //case if link is instagram explore tag 
        if(strpos($l, 'https://www.instagram.com/explore/tags/')!==False){
            //echo('entered Instagram Explore Tag Case'."\n");
            $tag = substr($l, 39);
            $endtag = strpos($tag, '/');
            $tag = substr($tag, 0, $endtag);
            try {
                $medias = $instagram->getMediasByTag($tag, $numberposts);
            } catch(Exception $e) {
                //This means case tag does not exist or it has been hidden by Instagram.
                //file_put_contents($file, date('Y-m-d H:i:s') . $e->getMessage() . "\n", FILE_APPEND);
                continue;
            }
            foreach($medias as $postinfo) {
                $account = $postinfo->getOwner();
                //check if account is private
                if($account->isPrivate()){
                    exit();
                }
                $igurl = $postinfo->getLink();
                $igurl = standardize_link($url, $igurl);
                if (!(array_key_exists($igurl, $already_crawled))){
                    $already_crawled[$igurl] = null;
                    store_post_details($postinfo, $url);
                }
            }
        }
        //case if link is normal
        else{
            if (!(array_key_exists($l, $already_crawled))){
                $already_crawled[$l] = null;
                store_details($l);
            }
        }
    }
}
function store_details($url) {
    global $client;
    global $maxCapacity;
    $options = array('http' => array('method' => "GET", 'headers' => "User-Agent: evrmorewebcrawler/1.0\n"));
    $context = stream_context_create($options);
    $doc = new DOMDocument();
    @$doc->loadHTML(@file_get_contents($url, false, $context));
    //copied from crawl
    $title = $doc->getElementsByTagName("title");
    if ($title->length==0){
        $title = '0';
    }else{
        $title = $title->item(0)->nodeValue;
    }
    // Get the tags
    $description = '';
    $author = '';
	$h1 = '';
	$h2 = '';
	$div = '';
	$span = '';
	$table = '';
	$tbody = '';
	$td = '';
	$body = '';
	$img = '';
	$p = '';
	//Description and Keywords
	$metas = $doc->getElementsByTagName("meta");
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		if (strtolower($meta->getAttribute("name")) == "description")
			$description = $meta->getAttribute("content");
		if (strtolower($meta->getAttribute("name")) == "author")
			$author = $meta->getAttribute("content");	
	}		
	$h1tags = $doc->getElementsByTagName('h1');
	$h2tags = $doc->getElementsByTagName('h2');
	$divtags = $doc->getElementsByTagName('div');
	$spantags = $doc->getElementsByTagName('span');
	$tabletags = $doc->getElementsByTagName('table');
	$tbodytags = $doc->getElementsByTagName('tbody');
	$tdtags = $doc->getElementsByTagName('td');
	$bodytags = $doc->getElementsByTagName('body');
	$imgtags = $doc->getElementsByTagName('img');
	$ptags = $doc->getElementsByTagName('p');
	if ($h1tags->length==0){
        $h1 = '0';
    }else{
        foreach($h1tags as $h1tag){
            $h1.=$h1tag->nodeValue;
        }
    }
    if ($h2tags->length==0){
        $h2 = '0';
    }else{
        foreach($h2tags as $h2tag){
            $h2.=$h2tag->nodeValue;
        }
    }
    if ($divtags->length==0){
        $div = '0';
    }else{
        foreach($divtags as $divtag){
            $div.=$divtag->nodeValue;
        }
    }
    if ($spantags->length==0){
        $span = '0';
    }else{
        foreach($spantags as $spantag){
            $span.=$spantag->nodeValue;
        }
    }
    if ($tabletags->length==0){
        $table = '0';
    }else{
        foreach($tabletags as $tabletag){
            $table.=$tabletag->nodeValue;
        }
    }
    if ($tbodytags->length==0){
        $tbody = '0';
    }else{
        foreach($tbodytags as $tbodytag){
            $tbody.=$tbodytag->nodeValue;
        }
    }
    if ($tdtags->length==0){
        $td = '0';
    }else{
        foreach($tdtags as $tdtag){
            $td.=$tdtag->nodeValue;
        }
    }
    if ($bodytags->length==0){
        $body = '0';
    }else{
        foreach($bodytags as $bodytag){
            $body.=$bodytag->nodeValue;
        }
    }
    if ($imgtags->length==0){
        $img = '0';
    }else{
        foreach($imgtags as $imgtag){
            $img.=$imgtag->nodeValue;
        }
    }
    if ($ptags->length==0){
        $p = '0';
    }else{
        foreach($ptags as $ptag){
            $p.=$ptag->nodeValue;
        }
    }
	//check for 0
	if ($title == '' or strlen($title) > $maxCapacity) {
	    $title = '0';
	}
	if ($description == '' or strlen($description) > $maxCapacity) {
	    $description = '0';
	}
	if ($author == '' or strlen($author) > $maxCapacity) {
	    $author = '0';
	}
	if ($h1 == '' or strlen($h1) > $maxCapacity) {
	    $h1 = '0';
	}
	if ($h2 == '' or strlen($h2) > $maxCapacity) {
	    $h2 = '0';
	}	
	if ($div == '' or strlen($div) > $maxCapacity) {
	    $div = '0';
	}
	if ($span == '' or strlen($span) > $maxCapacity) {
	    $span = '0';
	}
	if ($table == '' or strlen($table) > $maxCapacity) {
	    $table = '0';
	}
	if ($tbody == '' or strlen($tbody) > $maxCapacity) {
	    $tbody = '0';
	}
	if ($td == '' or strlen($td) > $maxCapacity) {
	    $td = '0';
	}
	if ($body == '' or strlen($body) > $maxCapacity) {
	    $body = '0';
	}
	if ($img == '' or strlen($img) > $maxCapacity) {
	    $img = '0';
	}	
	if ($p == '' or strlen($p) > $maxCapacity) {
	    $p = '0';
	}	
	//inert into dynamoDB table
	$result = $client->putItem(array(
        'TableName' => 'webcrawlerdata',
        'Item' => array(
            'URL'      => array('S' => $url),
            'title, caption'    => array('S' => $title),
            'description, comments'   => array('S' => $description),
            'author, username' => array('S' => $author),
            'h1'       => array('S' => $h1),
            'h2'       => array('S' => $h2),
            'div'       => array('S' => $div),
            'span'       => array('S' => $span),
            'table'       => array('S' => $table),
            'tbody'       => array('S' => $tbody),
            'td'       => array('S' => $td),
            'body'       => array('S' => $body),
            'img'       => array('S' => $img),
            'p'       => array('S' => $p)
        )
    ));
}
function store_post_details($postinfo, $url) {
    global $instagram;
    global $client;
    global $maxCapacity;
    global $numbercomments;
    $account = $postinfo->getOwner();
    $igurl = $postinfo->getLink();
    $igurl = standardize_link($url, $igurl);
    //echo($igurl."\n");
    $caption = $postinfo->getCaption();
    if ($caption == ''){
        $caption = '0';
    }
    //echo($caption."\n");
    //getting comments
    $mediaid = $postinfo->getId();
    $comments = $instagram->getMediaCommentsById($mediaid, $numbercomments);
    //check if there are any comments
    if(empty($comments)) {
        $commentstotal = '0';
    }
    $commentstotal = '';
    foreach($comments as $comment){
        if(is_null($comment)) {
            exit();
        }else{
            $commentstotal.= $comment->getText();
        }
    }
    if ($commentstotal == ''){
        $commentstotal = '0';
    }
    //echo($commentstotal."\n");
    //$userid = $account->getId();
    //$account1 = $instagram->getAccountById($userid);
    //$username = $account1->getUsername();
    $username = $account->getUsername();
    if ($username == ''){
        $username = '0';
    }
    //echo($username."\n");
    $h1 = '0';
    $h2 = '0';
    $div = '0';
    $span = '0';
    $table = '0';
    $tbody = '0';
    $td = '0';
    $body = '0';
    $img = '0';
    $p = '0';
    //inert into dynamoDB table
	$result = $client->putItem(array(
    'TableName' => 'webcrawlerdata',
    'Item' => array(
        'URL'      => array('S' => $igurl),
        'title, caption'    => array('S' => $caption),
        'description, comments'   => array('S' => $commentstotal),
        'author, username' => array('S' => $username),
        'h1'       => array('S' => $h1),
        'h2'       => array('S' => $h2),
        'div'       => array('S' => $div),
        'span'       => array('S' => $span),
        'table'       => array('S' => $table),
        'tbody'       => array('S' => $tbody),
        'td'       => array('S' => $td),
        'body'       => array('S' => $body),
        'img'       => array('S' => $img),
        'p'       => array('S' => $p)
    )
    ));
    
}
function standardize_link($url, $l) {
    if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
        $l = parse_url($url->get_content())["scheme"]."://".parse_url($url->get_content())["host"].$l;
    } else if (substr($l, 0, 2) == "//") {
        $l = parse_url($url->get_content())["scheme"].":".$l;
    } else if (substr($l, 0, 2) == "./") {
        $l = parse_url($url->get_content())["scheme"]."://".parse_url($url->get_content())["host"].dirname(parse_url($url->get_content())["path"]).substr($l, 1);
    } else if (substr($l, 0, 1) == "#") {
        $l = parse_url($url->get_content())["scheme"]."://".parse_url($url->get_content())["host"].parse_url($url->get_content())["path"].$l;
    } else if (substr($l, 0, 3) == "../") {
        $l = parse_url($url->get_content())["scheme"]."://".parse_url($url->get_content())["host"]."/".$l;
    //} else if (substr($l, 0, 11) == "javascript:") {
    //    continue;
    } else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
        $l = parse_url($url->get_content())["scheme"]."://".parse_url($url->get_content())["host"]."/".$l;
    }
    return $l;
}
//Step 2: start of crawler;
crawl($start);

//$message0 = sprintf("Finished Step 2");
//file_put_contents($file, date('Y-m-d H:i:s') . $message0 . "\n", FILE_APPEND);


//Step 3: Transfer data from webcrawlerdata to refinedData
$scan = $client->getIterator('Scan', array('TableName' => 'webcrawlerdata'));
foreach ($scan as $item) {
    $result1 = $client->putItem(array(
        'TableName' => 'refinedData',
        'Item' => $item
        ));
}

//$message0 = sprintf("Finished Step 3");
//file_put_contents($file, date('Y-m-d H:i:s') . $message0 . "\n", FILE_APPEND);

//Step 4: delete webcrawler table completely
$client->deleteTable(array(
    'TableName' => 'webcrawlerdata'
));

$client->waitUntil('TableNotExists', array(
    'TableName' => 'webcrawlerdata'
));

//$message0 = sprintf("Finished Step 4");
//file_put_contents($file, date('Y-m-d H:i:s') . $message0 . "\n", FILE_APPEND);


?>

