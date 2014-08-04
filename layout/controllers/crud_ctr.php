<?php

abstract class CRUD_wrapper {

	public $manager = null;

	public function __construct() {
		$full_name = get_class($this);
		if (!preg_match('#(.+)Controller$#', $full_name, $mc)) 
			throw new Exception("Class name must follow the {ORM_MODEL}Controller");

		$short_name = $mc[1];

		/* URLS */
		$this->single_url = $short_name . '/*';
		$this->multi_url = $short_name;

		/* Cut 's' */
		if (substr($short_name, -1) == 's')
			$short_name = substr($short_name, 0, strlen($short_name)-1);

		/* Uppercase first letter */
		$short_name = ucfirst($short_name);

		_debug_log("CRUD-wrapping `". $short_name."` class.");

		/* SET CLASS! */
		$this->class_name = $short_name;

		/* Register manager */
		$this->manager = new CRUD_Controller($short_name);
	}

	public function create($A) {
		return $this->manager->create_generic($A, null, $this->single_url);	
	}

	public function update($A) {
		return $this->manager->update_generic($A, null, $this->single_url);
	}

	public function delete($A) {
		return $this->manager->delete_generic($A, $this->multi_url);
	}

	public function order($A) {
		return $this->manager->order_generic_aux($A, $this->multi_url);
	}

	public function GET_($id = null) {
		if ($id != null) return $this->GET_one($id);
		return $this->GET_list();
	}

}

class CRUD_controller {

	public $class_name;

	/* CRUD settings */
	public $primary_id_field = 'id';

	/* REORDERing settings */
	public $order_coma_ids_field = 'order'; /* INPUT field with ids separated by comas */
	public $order_id_property = 'id'; /* Object property that has primary key id */
	public $order_property = 'priority'; /* Object property that is used for ordering */
	public $order_owner_id_field = 'item_id'; /* INPUT field with owner id */

	/* UPLOAD settings */
	public $upload_image_field_name = 'image';	 
	public $upload_owner_id_field = 'item_id'; /* INPUT field with owner id */

	public function __construct($class_name) {
		$this->class_name = $class_name;
	}

	function redirect($url = null, $item = null) {
		if ($url) {
			if ($item)
				$return_to = str_replace('*', $item->id(), $url);
			else
				$return_to = $url;			
		}
		else
			$return_to = get_return_to();

		go_to($return_to);
		return true;
	}

	function create_generic($A, $opts = array(), $return_to = null) {
		$class_name = $this->class_name;

		$item = new $class_name();
		fillObject($item, $A);
		if ($opts)
			foreach ($opts as $k=>$v)
				$item->$k = $v;
		$item->save();

		$this->redirect($return_to, $item);
	}

	function update_generic($A, $fields = null, $return_to = null) {
		$class = $this->class_name;
		$key = $this->primary_id_field;

		if (!isset($A[$key])) throw new Exception("No $key in $A");	
	
		if ($fields) foreach ($fields as $field)
			if (!isset($A[$field])) throw new Exception("Incomplete form, lacking `".$field."` field.");

		$object = ORM::Model($class, $A[$key]);
		if (!$object) throw new Exception("Object not found.");

		$errors = array();
		$input = null;
		if ($fields) {
			$B = array();
			foreach ($fields as $field)
				$B[$field] = $A[$field];
			$input = $B;
		} else {
			$input = $A;
		}
		fillObject($object, $input, $errors, true);

		/* Hack for DB.ORM */
		foreach ($input as $key=>$value) {
			if (preg_match('#(.+)_id$#', $key, $mc)) {
				$val = $mc[1];
				$object->$val = new stdClass();
				$object->$val->id = $value;
			}
		}

		$object->save($fields);

		if (_FORMAT('json'))
			return ajax_success( $object );

		$this->redirect($return_to, $object);
	}

	function delete_generic($A, $return_to = null) {
		$field = $this->primary_id_field;
		$class_name = $this->class_name;
		if (!isset($A[$field])) return false;	

		$object = ORM::Model($class_name, $A[$field]); 
		if (!$object) throw new Exception($class_name.' not found.');

		$object->delete();

		if (_FORMAT('json')) 
			return ajax_success(array(
				'action'	=> 'deleted',
				'type'	=> strtolower($class_name),
				'id'  	=> $A[$field]
			));

		$this->redirect($return_to);
	}

	function order_generic($A, $return_to, $filter = null, $order_reset = true) {
		$class_name = $this->class_name;
		$ids_field = $this->order_coma_ids_field;
		$property = $this->order_property;

		if (!isset($A[$ids_field]))
			throw new Exception("Undefined order.");

		$elements = ORM::Collection($class_name, $filter, 1);
		$new_order = preg_split('/,/', $A[$ids_field]);
		$new_order = array_filter($new_order);

		$elements->forceFilter($filter);
		$elements->order_using($property, $new_order, $order_reset);
		$elements->save(array($property));

		if (_FORMAT('json'))
			return ajax_success( $elements );

		$this->redirect($return_to);
	}

	function order_generic_aux($A, $backurl, $order_reset = 2) {
		$model_class = $this->class_name;
		$field = $this->order_property;
		$ids_field = $this->order_coma_ids_field;
		$id_prop = $this->order_id_property;

		if (!isset($A[$ids_field]))
			throw new Exception("Undefined order.");

		$ids = preg_split('/,/', $A[$ids_field]);
		$ids = array_filter($ids);

		if (!$ids)
			throw new Exception("Malformed order.");

		$q = new QRY();
		$q->WHERE($id_prop, 'IN', $ids);

		return $this->order_generic($A, $backurl, $q, $order_reset);  
	}

	private function get_owner($owner_property) {
		$model_class = $this->class_name;
		$map = @$model_class::$belongs_to[$owner_property];
		if (!$map) throw new Exception("Can't find belongs_to[ $holder_backref ] in $model_class.");

		return $map;
	}

	function order_generic_owned($A, $owner_property, $backurl, $order_reset = 2) {
		$model_class = $this->class_name;
		list($owner_class, $idref) = $this->get_owner($owner_property);
		$field = $this->order_property;
		$owner_field = $this->order_owner_id_field;

		if (!isset($A[$owner_field]))
			throw new Exception("Undefined ".$model_class.".");

		$url = str_replace('*', $A[$owner_field], $backurl);
		return $this->order_generic($A, $url,
			array($idref => $A[$owner_field]), $order_reset);
	}

	function upload_owned($A, $holder_backref, $vpath, $url) {
		$model_class = $this->class_name;
		$map = @$model_class::$belongs_to[$holder_backref];
		if (!$map) throw new Exception("Can't find belongs_to[ $holder_backref ] in $model_class.");

		$holder_class = $map[0];
		$id_name = $map[1];//$this->primary_id_field;
		//static $belongs_to = array(
		//'project' => array('Project', 'project_id', 'id'),
		//);
		$field = $this->upload_owner_id_field;

		if (!isset($A[$field]))
			throw new Exception("Undefined " . $holder_class . ".");

		$item = ORM::Model($holder_class, $A[$field], 1);
		if (!$item) throw new Exception("Unrecognized " . $holder_class . ".");

		$full_vpath = str_replace('*', $item->id(), $vpath);

		$image_name = $this->upload_image_field_name;

		if ($A['title']) $_FILES[$image_name]['title'] = $A['title']; // use title as fake imagefilename, for db
		else { //replace file extension

			$name = $_FILES[$image_name]['name'];
			$ext = File::file_extension($name);
			$name = substr($name, 0, strlen($name) - strlen($ext) - 1); 
			$_FILES[$image_name]['title'] = $name;
		}

		$picture = new $model_class();

		/* $picture->is_raunchy = 1; */

		$ok = $picture->upload($_FILES[$image_name], $full_vpath);
		if (is_object($ok)) $picture = $ok;

		if (!$ok) throw new Exception($picture->LAST_ERROR);

		$picture->$holder_backref = $item;
		/* $picture->is_raunchy = 1; */

		$size = $picture->imageSize();
		if (!$size) throw new Exception("Uploaded file is not an image.");

		//$picture->width = $size[0];
		//$picture->height = $size[1];

		$picture->priority = $item->pictures->count();

		$ok = $picture->save(array($id_name, 'priority'));
	//	$ok = $picture->save();

		if (_FORMAT('json') || isset($_REQUEST['json'])) 
			return ajax_success( $picture );

		$this->redirect($url, $item);
	}

	function quickupdate_generic($A, $property, $value, $reaction, $new_action) {
		$class_name = $this->class_name;
		$field = $this->primary_id_field;

		if (!isset($A[$field]))
			throw new Exception('Undefined ' . $class_name . '.');

		$id = $A[$field];
		$item = ORM::Model($class_name, $id);
		if (!$item)
			throw new Exception('Object ' . $class_name . ' not found.');

		$item->$property = $value;
		if (!$item->save(array($property)))
			throw new Exception('Unable to save ' . $class_name . '.');

		$HTMLprop = $property.'HTML';
		$HTMLpropFUNC = $HTMLprop . '_auto'; 
		$item->$HTMLprop = $item->$HTMLpropFUNC();

		if (_FORMAT('json'))
			return ajax_success(array(
				'action'	=> $reaction,
				'type'  	=> $class_name,
				'id'    	=> $id,
				'hint'  	=> $item->$HTMLprop,
				'reaction'	=> $new_action,
			));

		$this->redirect();
	}

}

?>