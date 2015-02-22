<?php
require_once("./site.class.php");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

//SETTINGS
$base_dir = '/var/www/wordpress';
$config_file = 'wp-config.php';

//Regex for Finding DB Credentials
$dbname_regex = "/define\('DB\_NAME', '(.*)'.*/";
$dbhost_regex = "/define\('DB\_HOST', '(.*)'.*/";
$dbuser_regex = "/define\('DB\_USER', '(.*)'.*/";
$dbpw_regex = "/define\('DB\_PASSWORD', '(.*)'.*/";
$table_prefix_regex  = '/\$table_prefix.*\'(.*)\';/';

$sites_list = array();

//Find all dirs with wordpress config files
if ($handle = opendir($base_dir)) {
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      $config_file_full_path = $base_dir . '/' . $entry . '/' . $config_file;
      if(file_exists($config_file_full_path)){

        $file_contents = file_get_contents($config_file_full_path);

        //Fetch DB Credentials from config file
        preg_match($dbname_regex,$file_contents,$dbname_matches);
        $dbname = $dbname_matches[1];
        preg_match($dbhost_regex,$file_contents,$dbhost_matches);
        $dbhost = $dbhost_matches[1];
        preg_match($dbuser_regex,$file_contents,$dbuser_matches);
        $dbuser = $dbuser_matches[1];
        preg_match($dbpw_regex,$file_contents,$dbpw_matches);
        $dbpw = $dbpw_matches[1];
        preg_match($table_prefix_regex,$file_contents,$table_prefix_matches);
        $table_prefix = $table_prefix_matches[1];

        //Create new site object to fetch update data
        $site = new site($dbhost,$dbuser,$dbpw,$dbname,$table_prefix);
        $site->name = $entry;

        //load the data from the database
        $site->get_update_data();

        $core = $site->get_core_updates();
        $plugins = $site->get_plugin_updates();

        //add it to the list of sites
        $sites_list[$entry] = array('core' => $core, 'plugins' => $plugins);

      }
    }
  }
  closedir($handle);
}
echo json_encode($sites_list);
?>
