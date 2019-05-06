<!DOCTYPE html>
<html>
    <head>
        <title>Analyze Image by Yoga</title>
        <!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> -->
        <script src="jquery-3.4.0.js"></script>
    </head>
    <body>
        
    <script type="text/javascript">
            function processImage() {
                // **********************************************
                // *** Update or verify the following values. ***
                // **********************************************
        
                // Replace <Subscription Key> with your valid subscription key.
                var subscriptionKey = "387c858c2b16412ca6cb09923ddf9a16";
        
                // You must use the same Azure region in your REST API method as you used to
                // get your subscription keys. For example, if you got your subscription keys
                // from the West US region, replace "westcentralus" in the URL
                // below with "westus".
                //
                // Free trial subscription keys are generated in the "westus" region.
                // If you use a free trial subscription key, you shouldn't need to change
                // this region.
                var uriBase =
                    "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
        
                // Request parameters.
                var params = {
                    "visualFeatures": "Categories,Description,Color",
                    "details": "",
                    "language": "en",
                };
        
                // Display the image.
                var sourceImageUrl = document.getElementById("inputImage").value;
                document.querySelector("#sourceImage").src = sourceImageUrl;
        
                // Make the REST API call.
                $.ajax({
                    url: uriBase + "?" + $.param(params),
        
                    // Request headers.
                    beforeSend: function(xhrObj){
                        xhrObj.setRequestHeader("Content-Type","application/json");
                        xhrObj.setRequestHeader(
                            "Ocp-Apim-Subscription-Key", subscriptionKey);
                    },
        
                    type: "POST",
        
                    // Request body.
                    data: '{"url": ' + '"' + sourceImageUrl + '"}',
                })
        
                .done(function(data) {
                    // Show formatted JSON on webpage.
                    $("#responseTextArea").val(JSON.stringify(data, null, 2));
                })
        
                .fail(function(jqXHR, textStatus, errorThrown) {
                    // Display error message.
                    var errorString = (errorThrown === "") ? "Error. " :
                        errorThrown + " (" + jqXHR.status + "): ";
                    errorString += (jqXHR.responseText === "") ? "" :
                        jQuery.parseJSON(jqXHR.responseText).message;
                    alert(errorString);
                });
            };
        </script>

    <?php
    require_once 'vendor/autoload.php';
    require_once "./random_string.php";

    use MicrosoftAzure\Storage\Blob\BlobRestProxy;
    use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
    use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
    use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
    use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

    $connectionString = "DefaultEndpointsProtocol=https;AccountName='azureyoga';AccountKey='bGVd7OMBukLbRCP+sxAdpWJgnewIWBwz4ICjn/ga6lLjMd2GpxWbjLVycNvCQ2y1AMJ3hFNT3DUMNn2QgNXFWw=='";
    $blobClient = BlobRestProxy::createBlobService($connectionString);
    $fileToUpload = "jokowi.jpg";
    if (!isset($_GET["Cleanup"])) {
        $createContainerOptions = new CreateContainerOptions();
        $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
        $createContainerOptions->addMetaData("key1", "value1");
        $createContainerOptions->addMetaData("key2", "value2");
        $containerName = "blockblobs".generateRandomString();

        try {
            $blobClient->createContainer($containerName, $createContainerOptions);

            $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
            fclose($myfile);

            echo "Uploading BlockBlob: ".PHP_EOL;
            echo $fileToUpload;
            echo "<br />";
            
            $content = fopen($fileToUpload, "r");

			//Mengunggah blob
            $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

            // List blobs.
            $listBlobsOptions = new ListBlobsOptions();
            $listBlobsOptions->setPrefix("jokowi");

            echo "These are the blobs present in the container: ";

            do{
                $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
                foreach ($result->getBlobs() as $blob)
                {
                    echo $blob->getName().": ".$blob->getUrl()."<br />";
                    ?>
                    <h1>Analyze image:</h1>
Enter the URL to an image, then click the <strong>Analyze image</strong> button.
        <br><br>
Image to analyze:
        <input type="text" name="inputImage" id="inputImage"
            value="<?php echo $blob->getUrl()?>" />
        <button onclick="processImage()">Analyze image</button>
        <br><br>
        <div id="wrapper" style="width:1020px; display:table;">
            <div id="jsonOutput" style="width:600px; display:table-cell;">
                Response:
                <br><br>
                <textarea id="responseTextArea" class="UIInput"
                        style="width:580px; height:400px;"></textarea>
            </div>
            <div id="imageDiv" style="width:420px; display:table-cell;">
                Source image:
                <br><br>
                <img id="sourceImage" width="400" />
            </div>
        </div>

        
                    <?php
                }
                $listBlobsOptions->setContinuationToken($result->getContinuationToken());
            } while($result->getContinuationToken());
            echo "<br />";

        }
        catch(ServiceException $e){
     
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        catch(InvalidArgumentTypeException $e){
        
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
    } 
    else 
    {

        try{
            // Delete container.
            echo "Deleting Container".PHP_EOL;
            echo $_GET["containerName"].PHP_EOL;
            echo "<br />";
            $blobClient->deleteContainer($_GET["containerName"]);
        }
        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
    }
    ?>

    </body>
    <form method="post" action="phpQS.php?Cleanup&containerName=<?php echo $containerName; ?>">
        <button type="submit">Press to clean up all resources</button>
    </form>
</html>