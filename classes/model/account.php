<?php
class Model_Account extends Mango {

	protected $_fields = array(
		'name'         => array(
			'type'     => 'string',
			'required' => TRUE,
			'min_length' => 3,
			'max_length' => 127,
			'rules' => array(
				array('alpha_numeric')
			)
		),
		// the attributes below might seem strange - they are :-)
		// but in the demos I needed a counter, a set, an array and an
		// multidimensional array of counters
		'some_counter' => array('type'=>'counter'),
		'categories'   => array('type'=>'set'),
		'some_array'   => array('type'=>'array'),
		'report'       => array('type'=>'array','type_hint'=>'counter'),
		'local_data'   => array('type'=>'string','local'=>TRUE)
	);

	protected $_relations = array(
		'users' => array('type'=>'has_many')
	);

	protected $_db = 'demo'; //don't use default db config

}