<?php

class  RightsEditorProvider {
	
	public function get_group_rights($group) {
		
		if($group == 0) return false;
		
		$result = query('SELECT * FROM '.PREFIX.'rights
						 LEFT JOIN '.PREFIX.'group_rights ON '.PREFIX.'group_rights.group_id = '.$group.'  
									AND '.PREFIX.'group_rights.right_id = '.PREFIX.'rights.id ');
		$rights = array();
		
		if($result) {
			while($data = mysql_fetch_assoc($result))
				$rights[] = $data;	
		}
		
		return $rights;
	}
	
	public function set_rights($group, $right_id, $enabled) {
		$sql = '';
		
		if($enabled === true) {
			$sql = 'INSERT INTO '.PREFIX.'group_rights VALUES ('.$group.','.$right_id.')';
		}
		else if ($enabled === false) {
			$sql = 'DELETE FROM '.PREFIX.'group_rights WHERE group_id='.$group.' AND right_id='.$right_id.' LIMIT 1';
		}
		
		$result = query($sql);
		
		if($result) return true;
		
		return false;
	}
}