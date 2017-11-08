<?php
include_once('class/pdf.class.php');

if (isset($_POST['html']) && !empty($_POST['html'])){
  $domain = "";
  if(isset($_POST['domain']) && !empty($_POST['domain']) ){
    $domain = $_POST['domain'];
  }
  $html =$_POST['html'];
  $offset = strpos($html,'class="tablepdf"');
  $html = substr($html, 0, $offset) . str_replace('</span>','&nbsp;&nbsp;&#124;&nbsp;&nbsp;</span>',substr($html,$offset));
  $html=
  '<style>
  section{ width:100%; background-color: #FFFFFF;   }
  div{ width:100%;  border-bottom:1px solid #EEE;  background:#ddd;}
    div.linehead{  color:#000;   }
    div.linehead > div{ border-bottom:none;}
      span.line{
        color:#444;
      }
      span.line b.valueone{
        text-align:center;}
      span.line b.valuemore{
        color:green;
        text-align:center;}
  .green{ color:green;}
  .red { color:red;}
  h2{
    width:100%;
    background-color: blue;
    color:white;
    text-align: center;}
  </style>' . $html;
  $pdf = new pdf("documents/audit.pdf",$html,$domain);
  $showtype = 'I';
  if(isset($_GET['showtype']) && !empty($_GET['showtype']) ){
    $showtype = $_GET['showtype'];
  }
  $pdf->show($showtype);
  unset($pdf);
} ?>
