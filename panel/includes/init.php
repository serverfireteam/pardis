<?php

 /*** include the controller class ***/
 include __SITE_PATH . '/application/' . 'controller_base.class.php';

 /*** include the registry class ***/
 include __SITE_PATH . '/application/' . 'registry.class.php';
 
 /*** include the query class ***/
 include __SITE_PATH . '/application/' . 'query.class.php';

 /*** include the router class ***/
 include __SITE_PATH . '/panel/application/' . 'router.class.php';

 /*** include the template class ***/
 include __SITE_PATH . '/application/' . 'template.class.php';
 
 /*** include the field class ***/
 include __SITE_PATH . '/panel/application/' . 'field.class.version.php';
 
 /*** include the img class ***/
 include __SITE_PATH . '/application/' . 'img.class.php';

 /* lang */
 include __SITE_PATH . '/application/' . 'lang_fn.php';
 include __SITE_PATH .'/lang/it.php';

 /*** auto load model classes ***/
    function __autoload($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = __SITE_PATH . '/model/' . $filename;

    if (file_exists($file) == false)
    {
        return false;
    }
  include ($file);
}

 /*** a new registry object ***/
 $registry = new registry;

 /*** create the database registry object ***/
 // $registry->db = db::getInstance();

?>
