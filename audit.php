<?php
include_once('class/proxy.class.php');
include_once('class/argo.class.php');
include_once('class/filetext.class.php');
include_once('class/pdf.class.php');
include_once('class/spider.class.php');
Set_time_limit(0);
$domain = $_POST["domain"];
//$startPage= '/';
$startPage= $_POST["page"];
$nwords = "";
if(isset($_POST["nwords"])){
  $nwords = $_POST["nwords"];
}
$queryString = true;
$level = 3;
$max = 10; // 0 == crawler all

$spider = new spider($domain, $startPage, $queryString);
$list = $spider->crawlRecursive( $level, $max );
$spider->printHTML();

//$url = sprintf("dwnloadpdf.php?domain=%s&page=%s&nwordss=%s&querystring=%s&level=%d&max=%d",$domain,$startPage,$nwords,$queryString,$level,$max);
?>
<!-- <div id="contentdwn"><a id="dwn" href="#" title="Donwnload pdf report">Download PDF</a></div>
<div id="outputpdf"></div> -->
<form id="calldwn" name="calldwn" action="dwnloadpdf.php?showtype=I" method="post" target="_blank">
  <input name="domain" type="hidden" value="<?php echo $domain; ?>" >
  <input name="html" type="hidden" value='<?php echo html_entity_decode($spider->getHTML()) ; ?> '>
  <input id="btnsubmit" type="submit" value="Download PDF">
</form>

<script>
// SCRIPT FOR races page
$("#dwn").on('click',function(e) { // hancor home
  e.preventDefault();
  var ajaxRequest;
  var html = '<?php echo html_entity_decode($spider->getHTML() , ENT_QUOTES | ENT_XML1, 'UTF-8');  ?>'; //recupera tutti i valori del form automaticamente
  var domain =  '<?php echo $domain; ?>';
  ajaxRequest=$.ajax({
        url: "dwnloadpdf.php?showtype=E",
        type: "post",
        data: {html: html, domain : domain},
        beforeSend: function() {
          $("#outputpdf").html('<img id="loadingwait" src="images/loading.gif" alt="Loading&hellip;" >');
        }
    });
    ajaxRequest.done(function (response, textStatus, jqXHR){
        // show successfully for submit message
    //  alert(response);


        $("#outputpdf").html('<embed width=100% type="application/pdf" src="data:application/pdf;base64,' + response + '"></embed>');
        $('html, body').animate({
          scrollTop: $("#outputpdf").offset().top// scroll on line in list edited
        }, 1000);

    // window.open("data:application/pdf;base64," + encodeURI(response));
        return false;
    });

    //On failure of request this function will be called
    ajaxRequest.fail(function (){
        // show error
        $("#outputpdf").html(response);
    });
});
// END SCRIPT FOR  races page
</script>
<?php
unset($spider);
?>
