<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
<meta charset="UTF-8" />
<title><?php echo $pageTitle;?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $appBase;?>views/css/kickstart.css" media="all" />                  
<link rel="stylesheet" type="text/css" href="<?php echo $appBase;?>views/css/site.css" media="all" />                       
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $appBase;?>views/js/kickstart.js"></script>
</head>
<body>
<div class="container" id="header"><?php echo $this->requestAction($this->app,'common','head');?></div>
<div class="container" id="content"><?php echo $contents_for_layout;?></div>
<div class="container" id="footer"><?php echo $this->requestAction($this->app,'common','foot');?></div>
</body></html>