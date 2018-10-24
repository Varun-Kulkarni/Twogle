<?php
include('../keys/keys.php');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
atom://teletype/portal/17c60a14-3473-4eb6-8a83-3062679dfa48
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="style.css">
        <title></title>
    </head>
    <body>
        <div class="logo">
            <img src="resources\logo<?php echo rand(1,6);?>.png" alt="Logo">
        </div>
        <form class="search" action="resultsPageS.php" method="get">
            <input type="text" name="query" onchange=function()>
            <div class="submit">
                <button type="submit" name="button">
                    <img src="resources\search.png" alt="search">
                </button>
            </div>
        </form>
        <script type="text/javascript">
            $(function() {
                var params = {
                    // Request parameters
                    "q": "bill g",
                };

                $.ajax({
                    url: "https://api.cognitive.microsoft.com/bing/v7.0/suggestions/?" + $.param(params),
                    beforeSend: function(xhrObj){
                        // Request headers
                        xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key","{<? $azureKey ?>}");
                    },
                    type: "GET",
                    // Request body
                    data: "{body}",
                })
                .done(function(data) {
                    alert(JSON.stringify(data));
                })
                .fail(function() {
                    alert("error");
                });
            });
          </script>
    </body>
</html>
