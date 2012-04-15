<?php
/* Draftable Test cases generated on: 2012-04-13 19:19:39 : 1334344779*/
App::uses('DraftableBehavior', 'Drafts.Model/Behavior');


if (!class_exists('Article')) {
	class Article extends CakeTestModel {
	/**
	 *
	 */
		public $callbackData = array();

	/**
	 *
	 */
		public $actsAs = array(
			'Drafts.Draftable' => array(
				'triggerField' => 'rename_draft',
				));
	/**
	 *
	 */
		public $useTable = 'articles';

	/**
	 *
	 */
		public $name = 'Article';
	}
}


/**
 * DraftableBehavior Test Case
 *
 */
class DraftableBehaviorTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Drafts.draft',
		'plugin.Drafts.article',
		);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Draftable = new DraftableBehavior();
		$this->Model = Classregistry::init('Article');
		$this->Draft = Classregistry::init('Drafts.Draft');
	}
	

/**
 * Test behavior instance
 *
 * @return void
 */
	public function testBehaviorInstance() {
		$this->assertTrue(is_a($this->Model->Behaviors->Draftable, 'DraftableBehavior'));
	}
	
	
/**
 * Test this behaviors interception of saving related models
 */
	public function testSaving() {
		// test normal article save without a triggerField set
		$data['Article'] = array(
			'title' => 'Test Name',
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			);
		$result = $this->Model->save($data);		
		$this->assertTrue(!empty($result['Article']['id'])); // should be the same array as $data but with an id value
		unset($result);
		
		
		$data['Article']['rename_draft'] = 1; // save a draft, with a non default draft field name
		$result = $this->Model->save($data);
		$this->assertTrue(empty($result['Article']['id'])); // test that the save didn't go through, becasue draft was set
		unset($result);
		
		
		$data['Article'] = array(
			'id' => '4f88970e-b438-4b01-8740-1a14124e0d46',
			'title' => 'New Test Name',
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			);
		$data['Article']['rename_draft'] = 1; // save a draft, with a non default draft field name
		$result = $this->Model->save($data);
		$this->assertEqual('First Article', $result['Article']['title']); // test that the title is equal to the fixture data that has the same id as the sent data, meaning that the record was not updated to new value, and instead was kept the same while the incoming data was sent to the drafts table
		unset($result);
	}
	
	
	public function testRevising() {
		
		$save = $this->Model->saveRevision('Article', '4f889729-c2fc-4c8a-ba36-1a14124e0d46', '2012-04-01 20:24:03');
		$find = $this->Model->find('first', array('conditions' => array('Article.id' => '4f889729-c2fc-4c8a-ba36-1a14124e0d46')));
		$this->assertEqual('Older Version of Second Article', $find['Article']['title']); // test that the article has been updated to an older version
		
		$data['Article']['id'] = '4f889729-c2fc-4c8a-ba36-1a14124e0d46';
		$data['Article']['rename_draft'] = 'revise'; // save a draft, with a non default draft field name
		$data['Article']['revise_to_date'] = '2012-04-01 20:24:03';
		$data['Article']['title'] = 'Do Not Save This Version Anywhere';
		$result = $this->Model->save($data);
		$this->assertEqual('Older Version of Second Article', $result['Article']['title']);
	}
	
	
	public function testDeleting() {
		
		$delete = $this->Model->delete('4f889729-c2fc-4c8a-ba36-1a14124e0d46');
		$result = $this->Draft->find('all', array('conditions' => array('Draft.model' => 'Article', 'Draft.foreign_key' => '4f889729-c2fc-4c8a-ba36-1a14124e0d46')));		
		$this->assertTrue(empty($result[0])); // test that all drafts with that id are gone
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Draftable);
		unset($this->Model);
		ClassRegistry::flush();

		parent::tearDown();
	}

}
