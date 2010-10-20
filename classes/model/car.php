<?php

class Model_Car extends Mango {

	protected $_db = 'demo'; //don't use default db config

	// hardcode the collection name, so that all instances, including all extending
	// instances (like Spyker_Model) are stored in the collection 'cars'
	protected $_collection = 'cars';

	// !! With extending classes - specify fields and relations in this method !!
	protected function set_model_definition()
	{
		$this->_set_model_definition(array(
			'_fields' => array(
				'price'    => array('type'=>'int','min_value' => 0),
				'car_type' => array('type'=>'int')
			)
		));

		parent::set_model_definition();
	}

	public function create($safe = TRUE)
	{
		$this->car_type = $this->_car_type;

		return parent::create();
	}
}
