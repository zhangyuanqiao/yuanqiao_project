<?php
require_once ('EventStub.php');
require_once ('TracedParameter.php'); 

$p1=new TracedParameter("type",1);
$p2=new TracedParameter("scID","11:11:11:11:11:11"); 
$p3=new TracedParameter("sta","11:11:11:11:11:11");

$envent_tub=new EventStub();
$envent_tub->addTracedParameter($p1); 
$envent_tub->addTracedParameter($p2); 
$envent_tub->addTracedParameter($p3);

echo   $envent_tub->createEvent();

?>