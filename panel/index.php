<?php
session_start();
header('Content-type:text/html; Charset=utf-8');
header('Access-Control-Allow-Origin:*');
error_reporting (E_ALL ^ E_NOTICE);
 /*** error reporting on ***/
 //error_reporting(E_ALL);

 /*** define the site path ***/
 $site_path = realpath(dirname(dirname(__FILE__)));
 define ('__SITE_PATH', $site_path);

 /*** include the init.php file ***/
 include 'includes/init.php';

 /*** load the router ***/
 $registry->router = new router($registry);

 /*** set the controller path ***/
  $registry->router->setPath (__SITE_PATH . '/panel/controller');

 /*** load up the template ***/
 $registry->template = new template($registry);
 /*** set the template path ***/
 $registry->template->setPath (__SITE_PATH . '/panel/views');
  /*** load the controller ***/
 $registry->router->loader();
?>