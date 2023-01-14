<?php

// Instructions: Create a file called config.php and put $username and $password into it.

// Load the username and password from the config file
include 'config.php';

// Check the username and password
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != $username || $_SERVER['PHP_AUTH_PW'] != $password) {

    // Return an "access denied" message
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Image uploader."');
    exit('Access denied');
}

// Access granted
?>

<style>
  img {
    display: block;
    margin: 5em auto;
    max-width: 100%;
  }

  #pasted {
    text-align: center;
  }

  button, input {
    display: none;
  }
</style>

<div id="pasted">
  <img id="image" src='data:image/svg+xml,<svg width="200" height="200" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 1472q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm256 0q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm128-224v320q0 40-28 68t-68 28h-1472q-40 0-68-28t-28-68v-320q0-40 28-68t68-28h427q21 56 70.5 92t110.5 36h256q61 0 110.5-36t70.5-92h427q40 0 68 28t28 68zm-325-648q-17 40-59 40h-256v448q0 26-19 45t-45 19h-256q-26 0-45-19t-19-45v-448h-256q-42 0-59-40-17-39 14-69l448-448q18-19 45-19t45 19l448 448q31 30 14 69z"/></svg>'/>
  <p>Paste image to upload.</p>
  <form method="POST">
    <input type="hidden" id="dataURL" name="dataURL">
    <input id="filename" name="filename" placeholder="Filename without extension..."/>
    <button id="upload" type="submit">Upload</button>
  </form>
</div>

<script>
  function handlePaste(e) {
    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
    console.log(JSON.stringify(items)); // will give you the mime types
    for (index in items) {
      var item = items[index];
      if (item.kind === 'file') {
        var blob = item.getAsFile();
        var reader = new FileReader();
        reader.onload = function(event){
          image.src = event.target.result;
          dataURL.value = image.src;
          upload.style.display = "unset";
          filename.style.display = "unset";
        };
        reader.readAsDataURL(blob);
      }
    }
  }

  // Add a paste event listener to the body element
  document.body.addEventListener('paste', handlePaste);
</script>

<?php
// Get the Data URL from the POST request (if any)
$data_url = $_POST['dataURL'];
$filename = $_POST['filename'];

if ($data_url) {
  // Get the MIME type and the data from the Data URL
  list($mime_type, $data) = explode(';', $data_url);
  $data = base64_decode(substr($data, strpos($data, ',') + 1));

  // Generate a file name for the image based on the MIME type
  $file_extension = 'jpg';
  switch ($mime_type) {
    case 'data:image/png':
      $file_extension = 'png';
      break;
    case 'data:image/gif':
      $file_extension = 'gif';
      break;
  }
  if (empty($filename)) {
    $filename = uniqid();
  }
  $file_name = $filename . '.' . $file_extension;

  // Set the path to the image directory
  $image_dir = './';

  // Save the image to the image directory
  file_put_contents($image_dir . $file_name, $data);
  chmod($image_dir . $file_name, 0644);
}
?>

<?php
// Set the path to the images directory
$image_dir = './';

// Get all files in the images directory
$files = glob($image_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

// Sort the files by modified time, most recent first
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Get the 10 most recent images
$recent_images = array_slice($files, 0, 10);

// Display the images
foreach ($recent_images as $image) {
    echo '<img src="' . $image . '">';
}
?>
