<?php
class BlogCategoryCloudTest extends SapphireTest {
	
	static $fixture_file = 'BlogCategoryCloudTest.yml';

	function testCategories() {
		$cat1 = $this->objFromFixture('BlogCategory', 'one');
		$cat2 = $this->objFromFixture('BlogCategory', 'two');
		$cat3 = $this->objFromFixture('BlogCategory', 'three');
		$cat4 = $this->objFromFixture('BlogCategory', 'four-holder2');

		$cloud = new BlogCategoryCloud();
		$cats = $cloud->Categories();
		$this->assertContains($cat1->ID, $cats->column('ID'));
		$this->assertContains($cat2->ID, $cats->column('ID'));
		$this->assertContains($cat3->ID, $cats->column('ID'));
		$this->assertContains($cat4->ID, $cats->column('ID'), 'Contains all holders');
	}
	
	function testIncludesAllHolders() {
		$holder1 = $this->objFromFixture('BlogHolder', 'holder1');
		$holder2 = $this->objFromFixture('BlogHolder', 'holder2');

		$cloud = new BlogCategoryCloud();
		$cats = $cloud->Categories();
		$this->assertContains($holder1->ID, $cats->column('ParentID'));
		$this->assertContains($holder2->ID, $cats->column('ParentID'));
	}

	function testCanFilterByHolder() {
		$holder1 = $this->objFromFixture('BlogHolder', 'holder1');
		$holder2 = $this->objFromFixture('BlogHolder', 'holder2');

		$cloud = new BlogCategoryCloud();
		$cloud->setHolderId($holder1->ID);
		$cats = $cloud->Categories();
		$this->assertContains($holder1->ID, $cats->column('ParentID'));
		$this->assertNotContains($holder2->ID, $cats->column('ParentID'));
	}

	function testEntryCount() {
		$cat1 = $this->objFromFixture('BlogCategory', 'one');
		$cat2 = $this->objFromFixture('BlogCategory', 'two');

		$cloud = new BlogCategoryCloud();
		$cats = $cloud->Categories();
		$cat1Wrapper = $cats->find('ID', $cat1->ID);
		$this->assertEquals(1, $cat1Wrapper->getEntryCount());
		$cat2Wrapper = $cats->find('ID', $cat2->ID);
		$this->assertEquals(2, $cat2Wrapper->getEntryCount());
	}

	function testFrequency() {
		$entryCount = BlogEntry::get()->count();
		$cat1 = $this->objFromFixture('BlogCategory', 'one');
		$cat2 = $this->objFromFixture('BlogCategory', 'two');

		$cloud = new BlogCategoryCloud();
		$cats = $cloud->Categories();
		$cat1Wrapper = $cats->find('ID', $cat1->ID);
		$this->assertEquals(
			1/2, // max entries per tag divided by current entries on this tag
			$cat1Wrapper->getFrequency()
		);
		$cat2Wrapper = $cats->find('ID', $cat2->ID);
		$this->assertEquals(
			2/2, // max entries per tag divided by current entries on this tag
			$cat2Wrapper->getFrequency()
		);
	}

	function testLevel() {
		$entryCount = BlogEntry::get()->count();
		$cat1 = $this->objFromFixture('BlogCategory', 'one');
		$cat2 = $this->objFromFixture('BlogCategory', 'two');

		$cloud = new BlogCategoryCloud();
		$cats = $cloud->Categories();
		$cat1Wrapper = $cats->find('ID', $cat1->ID);
		$this->assertEquals(
			5, // appears in 50% of max tag-count for a single entry, so level 5 of 10
			$cat1Wrapper->getLevel()
		);
		$cat2Wrapper = $cats->find('ID', $cat2->ID);
		$this->assertEquals(
			10, // has max tag-count for a single entry, so level 10 of 10
			$cat2Wrapper->getLevel()
		);
	}

	function testSortByTitle() {
		$cloud = new BlogCategoryCloud();
		$cloud->setSort('Weight');
		$cats = $cloud->Categories();
		$this->assertEquals(
			array('four-holder2', 'one', 'three', 'two'),
			$cats->column('Title')
		);

	}

}