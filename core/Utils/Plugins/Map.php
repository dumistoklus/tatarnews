<?php
/* 
 * Plugins Utils
 */

class ContainersMap {

    private $plugins_schema;


    /**
     *
     * @param <type> $plugins
     * @param <type> $containers
     *
     * 
     */
    function __construct($plugins) {

        if (is_array($plugins)) {

            $this->plugins_schema = $plugins;

            $p_schema = $this->convertPluginsSchema();

            if ($p_schema) {
                $this->plugins_schema = $p_schema;    
            }
        }
    }
    /**
     *
     * @param <type> $side
     * @param <type> $container
     * @param <type> $order
     * @return <type>
     *
     */

    public function getMap() {
        return $this->plugins_schema;
    }

    public function getOrder($side, $container, $order) {
        if(isset($this->plugins_schema[$side][$container][$order]))
            return $this->plugins_schema[$side][$container][$order] + 1;

        return 0;
    }


    private function convertPluginsSchema() {
        $new_schema = array();

        foreach ($this->plugins_schema as $side => $schema) {

            $new_schema[$side] = array();

            if(!is_array($schema)) continue;

            foreach ($schema as $order => $plugin) {
                if(is_numeric($plugin))
                    $new_schema[$side][$plugin][] = $order;

            }
        }

        return $new_schema;
    }
}

class PluginsManager
{
    private $page;
    private $plugins;
    private $plugins_total_count;

    private $plugins_info;


    private $schema;

    function __construct($page) {

        if(!DB_Provider::Instance()->is_loaded('Core')) DB_Provider::Instance()->loadProvider('Core');

        $this->page = PageController::load($page);

        $this->plugins = new Plugins($this->page);

        $plugins = $this->plugins->get_raw_plugins();

        $this->plugins_total_count = ($plugins !== false) ? sizeof($plugins) : 0;

        $this->plugins_info = $this->make_plugins_info($plugins);
        $this->schema = $this->to_schema_array($plugins);

    }

    public function schema() {
        return $this->schema;
    }

    public function count() {
        return $this->plugins_total_count;
    }

    public function plugin_count($name) {
        return (isset($this->plugins_info[$name])) ? $this->plugins_info[$name]['count'] : 0;
    }

    private function make_plugins_info($plugins) {
        $info = array();
      
        if(!is_array($plugins)) return $info;

        foreach ($plugins as $inf) {
            $info[$inf['name']] = (isset($info[$inf['name']]))
                        ? array('name' => $inf['plugin_id'], 'count' => $info[$inf['name']]['count']+1)
                        : array('name' => $inf['plugin_id'], 'count' => 1);
        }

        return $info;
    }

    private function to_schema_array($plugins) {
           
        $schema = array();
            
        if(!is_array($plugins)) return $schema;

        foreach($plugins as $plugin) {
           
            $schema[$plugin['side']][$plugin['order']] = array ('type' => $plugin['name'], 'id' => $plugin['plugin_id']) ;
        }
        return $schema;

    }

    private function is_plugins_equal($container, $plugin, $p_name) {

        if (isset($container[$p_name])) {
            if(isset ($container[$p_name][$plugin['side']])) {
                if (isset($container[$p_name][$plugin['side']][$plugin['order']])) {
                    return true;
                }
            }
        }

        return false;
    }
}

?>
