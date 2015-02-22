<?php
/**
* site
* Class for pulling information from a wordpress site
*
* @author Matt Taniguchi
*/
class site {

  public $name;
  public $db_config;
  public $environment;
  protected $plugin_updates;
  protected $core_updates;

  function __construct($dbhost,$dbusername,$dbpassword,$dbname,$table_prefix) {
    $this->db_config = array('host' => $dbhost, 'username' => $dbusername, 'password' => $dbpassword, 'db_name' => $dbname, 'table_prefix' => $table_prefix);
  }

  //Validate config and return appropriate error message
  function validate_config() {

    $config = $this->db_config;

    //simple validation the db_config
    if(is_array($config)){
      foreach($config as $property => $value){
        if($value == ""){
          $config_errors = "All config values must be set";
        }
      }
    }
    else {
      $config_errors = "Config not loaded";
    }

    //log any errors
    if(isset($config_errors)){
      error_log($config_errors, 0);
    }
  }

  //pull update data from the db
  function get_update_data() {

    $this->validate_config();
    $db = $this->db_config;
    $mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['db_name']);

    //Check if multisite
    $multisite = False;
    $table_result = $mysqli->query("SHOW FULL TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
    while ($table = $table_result->fetch_array(MYSQLI_NUM)) {

      //If there is a wp_blogs table its probably a multisite
      if($table[0] == 'wp_sitemeta'){
        $multisite = True;
      }
    }

    if($multisite){
      $core_update_query = "SELECT meta_value FROM " . $db['table_prefix'] . "sitemeta WHERE meta_key = '_site_transient_update_core'";
      $plugin_update_query = "SELECT meta_value FROM " . $db['table_prefix'] . "sitemeta WHERE meta_key = '_site_transient_update_plugins'";
      $value_col = 'meta_value';
    } else {
      $core_update_query = "SELECT option_value FROM " . $db['table_prefix'] . "options WHERE option_name = '_site_transient_update_core'";
      $plugin_update_query = "SELECT option_value FROM " . $db['table_prefix'] . "options WHERE option_name = '_site_transient_update_plugins'";
      $value_col = 'option_value';
    }

    //check for updates to plugins
    if ($plugin_result = $mysqli->query($plugin_update_query)) {

      while ($row = $plugin_result->fetch_object()) {

        $option_value = $row->$value_col;

        //unserialize the data
        $values = unserialize($option_value);

        //save to site object
        $this->plugin_updates = $values->response;
      }

      $plugin_result->close();
    }

    //check for updates to wp core
    if ($core_result = $mysqli->query($core_update_query)) {

      while ($row = $core_result->fetch_object()) {
        $option_value = $row->$value_col;

        //unserialize the data
        $values = unserialize($option_value);
        $this->core_updates = $values;
      }

      $core_result->close();
    }

    return true;
  }

  //get a list of plugins and new versions
  function get_plugin_updates() {

    $plugin_list = array();
    foreach ($this->plugin_updates as $file => $plugin) {
      $plugin_list[$plugin->slug] = $plugin->new_version;
    }

    if ($plugin_list == array()) {
      return false;
    } else {
      return $plugin_list;
    }
  }

  //see if theres a core update
  function get_core_updates() {
    $current_version = $this->core_updates->version_checked;
    $action = $this->core_updates->updates[0]->response;
    $new_version = $this->core_updates->updates['0']->current;

    if (($action == 'upgrade')) {
      return array('action' => $action, 'current' => $current_version, 'new' => $new_version);
    }
    elseif ($action == 'latest'){
      return array('action' => $action, 'current' => $current_version);
    }
    else {
      return false;
    }
  }

  //check if there any any updates available for a site
  function update_check(){

    $core_updates = $this->get_core_updates();
    $plugin_updates = $this->get_plugin_updates();

    if(($core_updates['action'] == 'latest') && (!$plugin_updates)){
      return false;
    }
    else{
      return true;
    }
  }

}
?>
