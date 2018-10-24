<?php
include('Model2.php');
newModel();
session_start();
include('styleMethods.php');
include('../keys/keys.php');
include('style.css');
printHeader();
require __DIR__ . '/vendor/autoload.php';
$dir = realpath(__DIR__ . '/..');
putenv("GOOGLE_APPLICATION_CREDENTIALS=".str_replace("\\","//",$dir)."/keys/HackathonTwogle-92860d36f24b.json");
use Google\Cloud\Language\LanguageClient;
$accessKey = $azureKey;
$endpoint = 'https://api.cognitive.microsoft.com/bing/v7.0/search';

if(!isset($_GET['currPage'])) {
    $currPage = 1;
} else {
    $currPage = $_GET['currPage'];
}
if(isset($_GET['next'])) {
    $currPage = $currPage + 1;
} elseif(isset($_GET['prev']) && $currPage > 1) {
    $currPage = $currPage - 1;
}
function BingWebSearch ($url, $key, $query) {
    global $currPage;
    /* Prepare the HTTP request.
     * NOTE: Use the key 'http' even if you are making an HTTPS request.
     * See: http://php.net/manual/en/function

     </html>.stream-context-create.php.
     */
    $headers = "Ocp-Apim-Subscription-Key: $key\r\n";
    $options = array ('http' => array (
                          'header' => $headers,
                           'method' => 'GET'));

    // Perform the request and get a JSON response.
    $context = stream_context_create($options);
    $offset = "";
    if($currPage > 1) {
        $offset = "&offset=" . (($currPage-1)*20);
    }
    $result = file_get_contents($url . "?q=" . urlencode($query) . "&count=20".$offset, false, $context);

    // Extract Bing HTTP headers.
    $headers = array();
    foreach ($http_response_header as $k => $v) {
        $h = explode(":", $v, 2);
        if (isset($h[1]))
            if (preg_match("/^BingAPIs-/", $h[0]) || preg_match("/^X-MSEdge-/", $h[0]))
                $headers[trim($h[0])] = trim($h[1]);
    }

    return array($headers, $result);
}


function classify_text($text, $projectId) {
  //Make sure we have enough words (20+) to call classifyText
  while (str_word_count($text) < 20) {
      $text = $text . " " . $text;
  }
  // Create the Natural Language client

  $language = new LanguageClient([
      'projectId' => $projectId,
  ]);
  // Call the classifyText function
  $response = $language->classifyText($text);
  $categories = $response->categories();
  // Print out information about each category
  return $categories;

}
$categories = classify_text($_GET['query'], "hackathontwogle");
//var_dump($categories);
$matchingCategories = array();
foreach($categories as $category) {
    addNewCategory($category['name']);
    array_push($matchingCategories, $category['name']);
}
//var_dump($matchingCategories);
$modelArray = returnArray();
//echo "<br><br>";
//var_dump($modelArray);
$domains = array();
$_SESSION['category'] = $matchingCategories;

foreach($matchingCategories as $matchingCategory){
    array_push($domains, array_keys($modelArray[$matchingCategory], max($modelArray[$matchingCategory])));
}

//var_dump(returnArray());
//echo "<br><br>domains:";
//var_dump($domains);
//echo "<br><br>";
$domains2 = array();
$i = 0;
while ($i < count($domains)) {
    $j = 0;
    while ($j < count($domains[$i])){
        array_push($domains2, $domains[$i][$j]);
        $j++;
    }
    $i++;
}
echo "<br>";
//var_dump($domains2);

// Validates the subscription key.
if (strlen($accessKey) == 32 && isset($_GET['query'])) {
    $term = $_GET['query'];

    echo "<div class = 'search'>";
    print "Searching the Web for: <span class = 'entry'>" . $term . "</span>\n";
    echo "</div>";
    // Makes the request.
    list($headers, $json) = BingWebSearch($endpoint, $accessKey, $term);

    // Prints JSON encoded response.
    print "<p>\n";
    //echo json_encode(json_decode($json), JSON_PRETTY_PRINT);
    $json = json_decode($json, true);
    //var_dump($json);
    $addValueForNextPage = 0;
    if ($currPage > 1) {
        $addValueForNextPage = 20 * ($currPage - 1);
    }
    $ArrayOfHits = array(array());
    foreach ($json["webPages"]["value"] as $hit) {
        $ThisHit = array();
        $ThisHit['url'] = $hit['url'];
        $ThisHit['domain'] = isolateDomain($hit['url']);
        $ThisHit['name'] = $hit['name'];
        $ThisHit['snippet'] = $hit['snippet'];
        array_push($ArrayOfHits, $ThisHit);
        /*echo "<div class = 'link-container'>";
        echo "<div class = 'hyperlink'>";
        echo "<a href = \"".$hit["url"]."\">". $hit["name"]."</a>\n";
        echo "</div>";
        echo "<div class = 'subtext'>";
        echo "    ".$hit["snippet"] . "\n";
        echo "</div> </div>";*/
    }
    unset($ArrayOfHits[0]);
    $k = 1;
    $indecesOfPriorityHits = array();
    while($k <= count($ArrayOfHits)) {
        if (in_array($ArrayOfHits[$k]["domain"], $domains2)) {
            array_push($indecesOfPriorityHits, $k);
        }
        $k++;
    }
    $PriorityHits = array();
    foreach($indecesOfPriorityHits as $index) {
        array_push($PriorityHits, $ArrayOfHits[$index]);
        unset($ArrayOfHits[$index]);
    }
    $ArrayOfHits = array_merge($PriorityHits, $ArrayOfHits);
    foreach($ArrayOfHits as $hit) {
        echo "<div class = 'link-container'>";
        echo "<div class = 'hyperlink'>";
        echo "<a href = 'redirect.php?url=".$hit["url"]."'>". $hit["name"]."</a>\n";
        echo "</div>";
        echo "<div class = 'subtext'>";
        echo "    ".$hit["snippet"] . "\n";
        echo "</div> </div>";
    }
    print "</p>";

} else {

    print("Invalid Bing Search API subscription key!\n");
    print("Please paste yours into the source code.\n");

}
echo "<form action= 'resultsPageS.php'>";
if($currPage > 1) {
    echo "<button name='prev'>Previous</button>";
}

echo "<button name='next'>Next</button><input type='hidden' value='".$_GET['query']."' name = 'query'></input><input type='hidden' value='".$currPage."' name = 'currPage'></input></form>";
echo "Showing Page: " . $currPage;
?>
</html>
