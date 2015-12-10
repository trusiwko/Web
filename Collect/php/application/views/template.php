<!doctype html>
<html>
  <head>
    <title><?=$title?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="<?=Kohana::$base_url?>favicon.ico" type="image/x-icon">
    <link href="<?=Kohana::$base_url?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=Kohana::$base_url?>css/css.css" rel="stylesheet">
    <?php foreach($styles as $style): ?>
    <link href="<?=Kohana::$base_url?>css/<?=$style?>" rel="stylesheet" type="text/css">
    <?php endforeach; ?>
    <script type="text/javascript">
        var PATH = '<?=Kohana::$base_url?>';
    </script>
    <script src="http://code.jquery.com/jquery.js"></script>
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->    
  </head>
  <body>
  
    <div class="container">
	
        <?=$template?>

		<div class="footer" style="text-align:center">
		</div>
		<div class="footer">
			&copy; 2015 <a rel="nofollow" href="https://docs.google.com/a/usbo.info/forms/d/1TWTG5MQYFQDOVpPVm9bw-HKNFgevd0sP80RbnsJRQuY/viewform">Павел Гончаренко</a>
		</div>
    
    </div>
    <script src="<?=Kohana::$base_url?>js/bootstrap.min.js"></script>
		
  </body>
</html>