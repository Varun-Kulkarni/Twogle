<?php
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
  foreach ($categories as $category) {
      printf('Category Name: %s' . PHP_EOL, $category['name']);
      printf('Confidence: %s' . PHP_EOL, $category['confidence']);
      printf(PHP_EOL);
  }

}
// Validates the subscription key.
if (strlen($accessKey) == 32 && isset($_GET['query'])) {
    $term = $_GET['query'];

    echo "<div class = 'subtext'>";
    print "Searching the Web for: " . $term . "\n";
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
    foreach ($json["webPages"]["value"] as $hit) {       ;
        echo "<div class = 'link-container'>";
        echo "<div class = 'hyperlink'>";
        echo "<a href = \"".$hit["url"]."\">". $hit["name"]."</a>\n";
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
classify_text($_GET['query'], "hackathontwogle");
echo "<form action= 'resultsPage.php'>";
if($currPage > 1) {
    echo "<button name='prev'>Previous</button>";
}

echo "<button name='next'>Next</button><input type='hidden' value='".$_GET['query']."' name = 'query'></input><input type='hidden' value='".$currPage."' name = 'currPage'></input></form>";
echo "Showing Page: " . $currPage;
?>
</html>
