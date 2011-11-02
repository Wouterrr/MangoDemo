<?php

class Controller_MangoDemo extends Controller_Template {

	public $template = 'mangoDemo.html';

	public function action_index()
	{
		$this->template->content = View::factory('mango/intro.html');
	}

	public function action_demo0()
	{
		$this->template->content = View::factory('mango/columns.html');
	}

	public function action_demo1()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating empty account object
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		$content .= Debug::vars($account->as_array());

		// now we can use the ID to retrieve it from DB
		$account2 = Mango::factory('account', array(
			'_id' => $account->_id
		))->load();

		// this should be the same account
		$content .= Debug::vars($account2->as_array());

		// Clean up
		$account->delete();
	}

	public function action_demo2()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// simulate $_POST object
		$post = array(
			'email'      => 'user@domain.com',
			'role'       => 'manager',
			'account_id' => $account->_id
		);

		// create empty user object
		$user = Mango::factory('user');

		try
		{
			// validate data
			$post = $user->check($post);

			// create user
			$user
				->values($post)
				->create();

			// show user
			$content .= Debug::vars($user->as_array());

			// load user by email
			$user2 = Mango::factory('user',array(
				'email' => 'user@domain.com'
			))->load();

			// this should be the same
			$content.= Debug::vars($user2->as_array());

			// you can access the account from the user object
			$content .= Debug::vars($user->account->as_array());

			// and you can access the users from the account object
			$users = $account->users;

			$content .= Debug::vars('account',$account->name,'has',$users->count(),'users');
		}
		catch(Validation_Exception $e)
		{
			$content .= Debug::vars($e->array->errors());
		}

		// clean up (because account has_many users, the users will be removed too)
		$account->delete();
	}

	public function action_demo3()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		//echo Debug::vars($account->some_counter); exit;

		$content .= Debug::vars($account->as_array());

		// atomic update
		$account->name = 'name2';
		$account->some_counter->increment(5);
		$account->update();

		$content .= Debug::vars($account->as_array());

		// another update
		$account->some_counter->increment();
		$account->update();

		$content .= Debug::vars($account->as_array());

		$account->delete();
	}

	public function action_demo4()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// create user
		$user = Mango::factory('user',array(
			'role' => 'manager',
			'email' => 'user@domain.com',
			'account_id' => $account->_id
		))->create();

		// create blog
		$blog = Mango::factory('blog',array(
			'title' => 'my first blog',
			'text' => 'hello world',
			'time_written' => time(),
			'user_id' => $user->_id
		))->create();

		// add an embedded has many object
		$comment = Mango::factory('comment',array(
			'name'    => 'John Doe',
			'comment' => 'Hello to you to',
			'time'    => time()
		));

		// to add a comment to blog (atomic) you can choose:
		$blog->add($comment); // OR $blog->comments[] = $comment;

		// save blog 
		$blog->update(); 

		// remove comment
		$blog->remove($comment); // OR unset($blog->comments[0]);

		// save blog
		$blog->update();

		// add comment again
		$blog->comments[] = $comment;

		// add another comment
		$comment2 = Mango::factory('comment',array(
			'name'    => 'Jane Doe',
			'comment' => 'I like your style',
			'time'    => time()
		));

		// add a second comment
		$blog->comments[] = $comment2; // or $blog->add($comment);

		$blog->update();

		// This will show the comments stored IN the blog object
		$content .= Debug::vars($blog->as_array());

		// You can access the comments
		// $blog->comments->as_array() is also possible
		foreach($blog->comments as $comment)
		{
			$content .= Debug::vars($comment->as_array());
		}

		// Reload
		$blog2 = Mango::factory('blog', array(
			'_id' => $blog->_id
		))->load();

		$content .= Debug::vars($blog2->as_array());

		// Remove second comment
		unset($blog2->comments[1]);

		$blog2->update();

		$content .= Debug::vars($blog2->as_array());

		// Clean up
		$account->delete();
	}

	public function action_demo5()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// create user
		$user1 = Mango::factory('user',array(
			'role' => 'manager',
			'email' => 'user@domain.com',
			'account_id' => $account->_id
		))->create();

		$user2 = Mango::factory('user',array(
			'role' => 'manager',
			'email' => 'user2@domain.com',
			'account_id' => $account->_id
		))->create();

		$group1 = Mango::factory('group',array(
			'name' => 'Group1'
		))->create();

		$group2 = Mango::factory('group',array(
			'name' => 'Group2'
		))->create();

		// add HABTM relationship between users and groups
		$group1->add($user1);
		$group1->add($user2);
		$group2->add($user1);
		$group2->add($user2);

		//SAVE ALL OBJECTS
		$user1->update();
		$user2->update();
		$group1->update();
		$group2->update();

		$content .= Debug::vars('two relations',$user1->as_array(),$group1->as_array());

		// delete group 2 - this should remove references from both users
		$group2->delete();

		$user1->reload();
		$user2->reload();

		$content .= Debug::vars('only one relation',$user1->as_array(),$user2->as_array());

		// Clean up - this should delete account -> thereby both users
		// and thereby clean user refs from group1
		$account->delete();

		// The $group1 object will still exist, although it's related $user is removed
		// lets check if the relationship is gone too:
		$group1->reload();

		$content .= Debug::vars('no more relations', $group1->as_array());

		$group1->delete();
		$group2->delete();
	}

	public function action_demo6()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// this is atomic
		$account->categories[] = 'cat1';

		$account->update();

		// as is $account->categories->push('cat1');
		// this isn't (but is possible)
		// $account->categories = array('cat1');
		// $account->update();

		$content .= Debug::vars($account->as_array());

		// try to push the same value
		$account->categories[] = 'cat1'; 

		echo Debug::vars($account->as_array());

		$account->categories[] = 'cat2';
		$account->update();

		$content .= Debug::vars($account->as_array());

		// atomic pull
		$account->categories->pull('cat1');
		// OR
		// unset($account->categories[ $account->categories->find('cat1') ]);
		$account->update();

		$content .= Debug::vars($account->as_array());

		// Clean up
		$account->delete();
	}

	public function action_demo7()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		// An unsaved object
		// All actions should result in a save query without updates/modifiers (it is inserted into DB)
		$content .= '<h1>Unsaved objects</h1>';

		/* Counters */
		$content.='<h2>counters</h2>';
		
		$account = Mango::factory('account');
		$account->some_counter->increment();
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_counter->decrement();
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_counter = 5;
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		/* Sets */
		$content.='<h2>sets</h2>';

		$account = Mango::factory('account');
		$account->categories[] = 'cat1';
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->categories = array('cat1','cat2');
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		/* Arrays */
		$content.='<h2>arrays</h2>';

		$account = Mango::factory('account');
		$account->some_array[] = 'cat1';
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_array['key'] = 'cat1';
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_array = array('cat1','key'=>'bla');
		$content.=Debug::vars($account->as_array(),$account->changed(FALSE)) . '<hr>';

		// Saved objects
		// Here we want $modifiers
		$content .= '<h1>Saved objects</h1>';

		/* Counters */
		$content.='<h2>counters</h2>';
		
		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_counter->increment(); // this is atomic (uses $inc)
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_counter = 5; // this is NOT atomic (uses $set, not $inc)
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		/* Sets */
		$content.='<h2>sets</h2>';

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->categories[] = 'cat1'; // this is atomic (uses $push)
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->categories = array('cat1','cat2'); // this is not atomic - a full reset of the categories array
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		/* Arrays */
		$content.='<h2>arrays</h2>';

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_array[] = 'bla';
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_array['key'] = 'bla';
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_array = array('key' => 'bla', 'blo');
		$content.=Debug::vars($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();
	}

	public function action_demo8()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		// let's simulate a load from DB
		$account = Mango::factory('account')->values(array(
			'_id'=>1,
			'name'=>'test',
			'report' => array(
				'total' => 5,
				'blog1' => array(
					'views' => 4,
					'comments'=> 3
				)
			)
		));

		// atomic counters are easy:
		$account->report['total']->increment();
		$account->report['blog1']['views']->increment();

		//$content .= Debug::vars($account->changed(TRUE));
		
		// simulate changes were saved
		$account->saved();
		
		// !! the following isn't necessary anymore - you can implicitely create counters
		// from Mango_Arrays, even when they haven't been defined before !!

		//$account->report['blog2'] = array('views'=>0,'comments'=>0);

		// just create counters on the fly, no matter how deep and no matter
		// if the fields haven't been defined and/or set to counter
		$account->report['blog2']['views']->increment();

		// also update an existing counter
		$account->report['blog1']['views']->increment();

		$content .= Debug::vars($account->changed(TRUE));

		// simulate save
		$account->saved();

		// do some more counting
		$account->report['blog2']['views']->increment();

		// and now it will inc
		$content .= Debug::vars($account->changed(TRUE));
	}

	public function action_demo9()
	{
		// Note: extension support is useful if you have different classes, that inherit
		// from the same (base) class, but each have different additional columns.

		$this->template->bind('content',$content);
		$content = '';

		// Create a Spyker car object
		// We should have access to the Car_Model columns as well as the Spyker_Model columns
		$car = Mango::factory('spyker',array(
			'price' => 1000,
			'spyker_data' => 'hello'
		));

		// create
		$car->create();

		$content .= Debug::vars($car->as_array());

		// Now create another car
		$car = Mango::factory('ferrari');
		$car->price = 750;
		$car->ferrari_data = 'world';
		$car->create();

		$content .= Debug::vars($car->as_array());

		// Now we have 2 cars saved in the cars collection, one ferrari, one spyker
		// Let's check - note we use 'car' in the factory method, but we get a fully
		// extended ferrari/spyker_model in return
		$cars = Mango::factory('car')->load(FALSE);
		foreach($cars as $car)
		{
			$content .= Debug::vars($car->as_array());

			// clean up
			$car->delete();
		}
	}

	public function action_demo10()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		$blog = Mango::factory('blog');

		// Make some data invalid to see validation error

		$data = array(
			'title'        => 'Title',
			'text'         => 'texxt',
			'time_written' => time(),
			'user_id'      => 12,
			'comments'     => array(
				array(
					//'name'    => 'N1', // this title will now fail the min_length = 4 rule
					'comment' => 'c1',
					'time'    => time()
				),
				array(
					'name'    => 'N2asa',
					'comment' => 'c2',
					'time'    => time()
				)
			)
		);

		// Validating full document
		try
		{
			$data = $blog->check($data);

			echo Debug::vars('Full Validation success!',$data);
		}
		catch(Validation_Exception $e)
		{
			echo Debug::vars('Full Validation failed', $e->model . ' (' . $e->seq .')', $e->array->errors());
		}

		// Validating local document only
		try
		{
			$data = $blog->check($data, Mango::CHECK_LOCAL);

			echo Debug::vars('Local Validation success!',$data);
		}
		catch(Validation_Exception $e)
		{
			echo Debug::vars('Local Validation failed', $e->model . ' (' . $e->seq .')', $e->array->errors());
		}

		// Validating supplied fields only
		// will not fail on required fields because it won't check those,
		// it will fail however on the supplied and incorrect 'name' field of the first column
		$data = array(
			'title'        => 'Title',
			'comments'     => array(
				array(
					'name'    => 'N1', // this title will now fail the min_length = 4 rule
				),
				array(
					'name'    => 'N2asa',
				)
			)
		);

		try
		{
			$data = $blog->check($data, Mango::CHECK_ONLY);

			echo Debug::vars('Only Validation success!',$data);
		}
		catch(Validation_Exception $e)
		{
			echo Debug::vars('Only Validation failed', $e->model . ' (' . $e->seq .')', $e->array->errors());
		}
	}

	public function action_demo11()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		// create 2 different blog entries with different titles
		$blog1 = Mango::factory('blog', array(
			'title'        => 'title1',
			'text'         => 'text1',
			'time_written' => time()
		))->create();

		$blog2 = Mango::factory('blog', array(
			'title'        => 'title2',
			'text'         => 'text2',
			'time_written' => time()
		))->create();

		$db = $blog2->db();

		// use a multiple update to change title of all blogs at once
		$db->update('blogs', array(), array('$set'=> array('title'=>'title3')), array('multiple' => true));

		// check and delete
		foreach( Mango::factory('blog')->load(FALSE) as $blog)
		{
			echo Debug::vars($blog->as_array());
			$blog->delete();
		}
	}

	public function action_demo12()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		// Create blog
		$blog = Mango::factory('blog', array(
			'title'        => 'title1',
			'text'         => 'text1',
			'time_written' => time()
		))->create();

		// Unset a field
		unset($blog->title);

		echo Debug::vars('Unset title',$blog->changed(true));

		// Save
		$blog->update();

		// Reload blog
		$blog->reload();

		// Check if title is really missing
		echo Debug::vars('Title should be missing', $blog->as_array());

		// Add 2 comments
		$blog->add( Mango::factory('comment', array(
			'name'    => 'Name1',
			'comment' => 'Text1',
			'time'    => time()
		)));

		$blog->add( Mango::factory('comment', array(
			'name'    => 'Name2',
			'comment' => 'Text2',
			'time'    => time()
		)));

		// This should use pushAll
		echo Debug::vars('Adding comments using $pushAll', $blog->changed(TRUE));

		$blog->update();
		$blog->reload();

		$blog->comments[0]->comment = 'New Text';

		echo Debug::vars('Update using array indices', $blog->changed(TRUE));

		$blog->update();
		$blog->reload();

		echo Debug::vars('New text in first comment', $blog->as_array());

		// Clean up
		$blog->delete();
	}

	public function action_demo13()
	{
		$user = Mango::factory('user', array(
			'role'  => 'viewer',
			'email' => 'a@b.nl'
		))->create();

		$group = Mango::factory('group', array(
			'name' => 'wouter'
		))->create();

		$user->add($group,'circle');

		echo Debug::vars($user->changed(TRUE), $group->changed(TRUE));

		$user->update();
		$group->update();

		$group->reload();
		$user->reload();

		foreach ( $group->users as $user)
		{
			echo Debug::vars($user->as_array(FALSE));
		}

		foreach ( $user->circles as $circle)
		{
			echo Debug::vars($circle->as_array(FALSE));
		}

		$group->remove($user);

		echo Debug::vars($user->changed(TRUE), $group->changed(TRUE));

		$user->delete();
		$group->delete();
	}

}