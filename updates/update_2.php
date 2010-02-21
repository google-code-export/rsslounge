<?PHP
    ob_start();
?>
ALTER TABLE `items` ADD `rating` FLOAT NOT NULL ,
ADD `rated` ENUM( 'up', 'down' ) NULL;

CREATE TABLE IF NOT EXISTS `b8wordlist` (
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `count` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `b8wordlist` (`token`, `count`) VALUES
('bayes*dbversion', '2'),
('bayes*texts.ham', '1'),
('another', '1 0 100208'),
('bayes*texts.spam', '1'),
('bayes', '0 1 100208'),
('learn', '1 1 100208');
<?PHP
    $sql = ob_get_contents();
    ob_end_clean();
    
    // rename tables
    if(strlen(trim($_POST['prefix']))>0) {
        $sql = str_replace('CREATE TABLE IF NOT EXISTS `', 'CREATE TABLE IF NOT EXISTS `' . trim(Zend_Registry::get('config')->resources->db->prefix), $sql);
        $sql = str_replace('INSERT INTO `', 'INSERT INTO `' . trim(Zend_Registry::get('config')->resources->db->prefix), $sql);
    }
    
    try {
        $config = array(
            'host'     => Zend_Registry::get('config')->resources->db->params->host,
            'username' => Zend_Registry::get('config')->resources->db->params->username,
            'password' => Zend_Registry::get('config')->resources->db->params->password,
            'dbname'   => trim(Zend_Registry::get('config')->resources->db->params->dbname),
            'driver_options'  => array( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true )
        );
        $db = Zend_Db::factory('Pdo_Mysql', $config);
        $db->getConnection();
    } catch (Exception $e) {
        Zend_Registry::get('logger')->log('patch error: could not connect to database', Zend_Log::ERR);
        die("patch error: could not connect to database");
    }
    
    $db->exec($sql);
    