<?php 
$header = HeaderViewData::init();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />

        <title><?= $header->title() ?></title>
        <script type="text/javascript">
            var article_id = '<?php echo env::vars()->article_id;?>';
        </script>
        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <!--[if IE 6]><link rel="stylesheet" type="text/css" href="/css/ie6.css" /><![endif]-->
        <!--[if IE 7]><link rel="stylesheet" type="text/css" href="/css/ie7.css" /><![endif]-->
        <!--[if IE 8]><link rel="stylesheet" type="text/css" href="/css/ie8.css" /><![endif]-->
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/js/main.js"></script>
        <link rel="icon" href="/css/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="/css/favicon.ico" type="image/x-icon" />


        <?php
        foreach ($header->meta() as $name => $description) {
        ?>
            <meta name="<?= $name ?>" content="<?= $description ?>" />
        <?php
        }

        foreach ($header->styles() as $style) {
        ?>
            <link rel="stylesheet" type="text/css" media="<?= $style['media'] ?>" href="<?= $style['file'] ?>" />
        <?php
        }

        foreach ($header->styles_with_condition() as $style) {
        ?>
            <!--[<?= $style['condition'] ?>]>
    				<link rel="stylesheet" type="text/css" media="<?= $style['media'] ?>" href="<?= $style['file'] ?>" />
    				<![endif]-->
        <?php
        }

        foreach ($header->scripts() as $script) {
        ?>
            <script type="text/javascript" src="<?= $script ?>" ></script>
        <?php
        }
        ?>
        <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-5069809-23']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
    </head>
