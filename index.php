<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
	<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Audit your Internet Site</title>
  <meta name="description" content="Audit Internet Site by Rivetta.fr"  >
	<meta name="keywords" content="Audit, Internet, Site, Rivetta, internet site, web"   >
	<meta name="author" content="Rivetta.fr">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" >

	<!-- FAVICON -->
	<link rel="icon" href="http://officinalab.fr/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="http://officinalab.fr/favicon.ico" type="image/x-icon">
  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/style.css" media="all">
  <!-- Responsive Stylesheets -->
	<!--<link rel="stylesheet" media="only screen and (max-width: 1024px) and (min-width: 769px)" href="css/desktop.css">
	<link rel="stylesheet" media="only screen and (max-width: 768px) and (min-width: 481px)" href="css/tablet.css">
	<link rel="stylesheet" media="only screen and (max-width: 480px)" href="css/smartphone.css"> -->

  <!-- JavaScript -->
	<script src="js/jquery-3.2.1.min.js"></script>
</head>
<body>
<form action="" method="post" name="auditform" id="auditform">
<input id="domain" name="domain" type="text" required placeholder="<?php echo 'Please insert your domain name.'; ?>">
<input id="page" name="page" type="text" placeholder="<?php echo 'If you want test a single page, intset it here.'; ?>" value="/">
<input id="nwords" name="nwords" type="number" step="1"  placeholder="<?php echo 'Please insert max keywords number to show.'; ?>">
<!-- <input name="lang" type="hidden" value="<?php // echo $_SESSION['langf']; ?>"> -->
<input id="btnsubmit" type="submit" value="<?php echo 'Start Audit'; ?>">
</form>
<div id="output"></div>
</body>
<script>
// SCRIPT FOR races page
$("#auditform").on('submit',function(e) { // hancor home
  e.preventDefault();
  $('#btnsubmit').fadeOut('normal', function() {
    $("#output").html('<img id="loadingwait" src="images/loading.gif" alt="Loading&hellip;" >');
  });
  var parameters = $("#auditform").serialize(); //recupera tutti i valori del form automaticamente
  $.ajax({
      type: "post",
      url: "audit.php",
      data: parameters,
      cache: false,
      success: function(htmldata){
        $('#btnsubmit').fadeIn('normal', function() {
          $("#output").html(htmldata);
        });
        $('html, body').animate({
          scrollTop: $("#output").offset().top// scroll on line in list edited
        }, 1000);
        return false;
      }
    });
});
// END SCRIPT FOR  races page
</script>
</html>
<?php


/*
$filename = 'doc/urllist.csv';
if(!empty($list)){
  $file = new filetext($filename,"w+");
  $file->writedata($list);
  unset($file);
} else {
  echo "<h2>Aucun fichier trouv&eacute;!</h2>";
}
*/
//saveDataCSV($list, $domain);

/*
$pathBase = ".";
$urlOrigin = "http://rivetta.fr";
$fileSitemap = "titlemeta.html";
$extensionsOk = array('php','asp','aspx','py','xhtml','phtml','php3');
$foldersOk = array();
$filesIgnores = array('404.php','403.php','500.php','footer.php');
array_push($filesIgnores,basename(__FILE__));
$crawler = fopen($fileSitemap,"w");
fputs($crawler,"<!DOCTYPE html>\n");
fputs($crawler,'<meta charset="UTF-8">'."\n");
fputs($crawler,"<head>\n");
fputs($crawler,'<style type="text/css">'. "\n");
fputs($crawler,".green{color:green;}\n");
fputs($crawler,'.red{color:red;}\n');
fputs($crawler,'</style>'."\n");
fputs($crawler,'</head>'."\n");
fputs($crawler,'<body>');
crawlFile($pathBase,$urlOrigin, $extensionsOk, $filesIgnores, $foldersOk);
fputs($crawler, "</body>\n");
fputs($crawler, "</html>");
*/
?>
