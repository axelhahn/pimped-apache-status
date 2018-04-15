<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: ALL REQUESTS
 * 
 */


$oDatarenderer=new Datarenderer();
$content=$oDatarenderer->renderTable('requests_all');
