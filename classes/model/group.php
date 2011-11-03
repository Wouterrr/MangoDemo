<?php

class Model_Group extends Mango {

	protected $_fields = array(
	 	'name'       => array('type'=>'string','required'=>TRUE,'min_length'=>4,'max_length'=>127,'rules'=>array(array('alpha_numeric')))
	);

	protected $_relations = array(
		'users'      => array('type'=>'has_and_belongs_to_many','related_relation'=> 'circles')
	);

	protected $_db = 'demo'; //don't use default db config
}