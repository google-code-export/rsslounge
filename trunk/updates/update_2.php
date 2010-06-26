<?PHP
    // this is an example update file for the database
    ob_start();
?>
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
<?PHP
    $sql = ob_get_contents();
    ob_end_clean();
    
    $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
    $db->exec($sql);
    
    // purge cache
    $dir = Zend_Registry::get('config')->cache->path;
    foreach(glob($dir.'*.*') as $file)
        unlink($file);