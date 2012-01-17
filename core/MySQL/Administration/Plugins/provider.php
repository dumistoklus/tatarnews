<?php

class PluginsAdminProvider {
	
	public function get_all_plugins() {
		$sql = 'SELECT id, name, file_name, container, have_settings FROM '.PREFIX.'plugins WHERE visiblity = "1" ORDER BY name ASC';
		
		return get_bd_data($sql, __FILE__.':'.__CLASS__.':'.__LINE__);
	}
	
}

class PagePluginsManager {

    private $page;
    private $schema;
    private $plugins_map;
    private $pageIsset = true;

    function __construct($page) {

        $this->page = $page;

        if ($this->isPageName()) {
            $this->page = PageController::load($page);
        } else if ($this->isPageObject()) {
            $this->page = $page;
        }
        else
            $this->page = null;

        if($this->page === null || $this->page->id() == 0) $this->pageIsset = false;

        Module::load('Utils.Plugins.Map');
    }

    public function removeSchema() {

        if (!$this->pageIsset)
            return false;

        $sql = 'DELETE FROM `' . PREFIX . 'page_plugins` WHERE `page_id` = ' . $this->page->id();

        return affectedRowsQuery($sql);
    }

    public function addPluginsSchema($schema) {

        $this->schema = $schema;

        if (!$this->pageIsset)
            return false;

        $sql = 'INSERT INTO `' . PREFIX . 'page_plugins` (`page_id`, `plugin_id`, `order`, `side`) VALUES ';

        $sql_values = array();
        foreach ($schema as $side => $plugins) {

            if(!is_array($plugins)) return false;

            foreach ($plugins as $order => $plugin_id) {
                $sql_values[] = '("' . $this->page->id() . '","' . (int) $plugin_id . '","' . ((int) $order + 1) . '", "' . (int) $side . '")';
            }
        }

        $sql .= implode(',', $sql_values);

        $result = query($sql);

        if ($result)
            return true;

        return false;
    }

    private function get_map($side, $plugin_id) {
        if (!isset($this->plugins_map[$side])) {

            $this->plugins_map[$side] = array();
        }
    }

    private function isPageName() {
        if (is_string($this->page)) {
            return true;
        }
        return false;
    }

    private function isPageObject() {
        $class = get_class($this->page);

        if ($class == 'EditPage' || $class == 'Page' || $class == 'NewPage') {
            return true;
        }
        return false;
    }

}