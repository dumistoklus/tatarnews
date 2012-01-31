<?php

class PageProvider {
	private $page_id = false;
	private $page_data = array();
	private $unknown_page_data = array();
	
	function __construct($page = false) {
		
		$this->unknown_page_data['id'] = 0;
		
		$this->unknown_page_data['name'] =
		$this->unknown_page_data['title'] =
		$this->unknown_page_data['description'] =
		$this->unknown_page_data['content'] =
		$this->unknown_page_data['keywords'] =
		$this->unknown_page_data['page_isset'] = false;
		
		if(is_numeric($page)) {
			$this->page_data = $this->getPageById($page);
		}
		else if($this->isValidName($page)) {
			$this->page_data = $this->getPageByName($page);
		}
		else {
			$this->page_data = $this->unknown_page_data;
		}
	}
	
	public function getPageData() {
		return $this->page_data;
	}
	
	public function delete() {

		if($this->page_id > 0) {
			$sql = "DELETE FROM ".PREFIX."pages WHERE id=".(int)$this->page_id;

			$result =query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
			
			if($result) return true;
		}

		return false;
	}
	
	private function getPageById($id) {
		
		$sql = "SELECT * FROM ".PREFIX."pages WHERE id=".(int)$id." LIMIT 1";
			
		return $this->initPage($sql);
	}
	
	private function initPage($sql) {
		
		$page_result = get_bd_data($sql);
		
		if(!isset($page_result[0]['id']))
			return $this->unknown_page_data;

		$this->page_id = $page_result[0]['id']; 				
		$page_result[0]['page_isset'] = true;	
					
		return $page_result[0];

				
	}

    private function getPageByName($name) {
        if(!is_object($name))
        {
            if ($this->isValidName($name)) {
                $name = mysql_real_escape_string(strip_tags($name));

                $sql = "SELECT * FROM ".PREFIX."pages WHERE name='$name' LIMIT 1";

                return $this->initPage($sql);
            }
        }
        return $this->unknown_page_data;
    }
	
	public function createPage($name) {
            if(!is_object($name))
            {
            if ($this->isValidName($name)) {
                    $name = mysql_real_escape_string(addslashes(strip_tags($name)));

                    $sql = 'INSERT INTO '.PREFIX.'pages (name, title, keywords, description, content)
                                                                                    VALUES ("'.$name.'", "", "", "", "")';

                    $create_result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);

                    if($create_result) {
                            $this->page_id = mysql_insert_id();
                            return $this->page_id;
                    }
                }
            }
		return 0;
	}
	
	public function updatePageValue($value_name, $value) {
		
		if($this->page_id > 0) {
			$value = htmlspecialchars(trim($value), ENT_QUOTES);
				
			$sql = 'UPDATE '.PREFIX.'pages SET '.$value_name.'="'.mysql_real_escape_string(addslashes($value)).'" WHERE id='.$this->page_id.' LIMIT 1';
				
			$set_result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
			if($set_result) {
				return $value;
			}
		}
		return false;
	}

        public function isValidName($name) {

            if(preg_match('/^[A-z]+[\w\s\d]+$/', $name)) return true;
            else return false;
        }
}

class PluginsProvider {
	function __construct(){}
	
	public function getPagePlugins($id) {
		$sql = 'SELECT pp.order, pp.side, pl.file_name AS name, pp.plugin_id  FROM '.PREFIX.'page_plugins pp ';
		$sql .='LEFT JOIN '.PREFIX.'plugins pl ON pp.plugin_id = pl.id ';
		$sql .='WHERE pp.visible = "1" AND pp.page_id = '.(int)$id.' ORDER BY pp.side, pp.order';
		$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		
		$data_array = array();
		if($result) {
			while($data = mysql_fetch_assoc($result)){
				array_push($data_array, $data);
			}
		}
		
		return $data_array;
	}
}

class PageControllerProvider
{
    public function pages()
    {
        $sql = 'SELECT id, `name`, keywords, description, title FROM '.PREFIX.'pages';

        return get_bd_data($sql);
    }
}
