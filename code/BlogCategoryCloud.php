<?php
/**
 * Shows a tag cloud of {@link BlogCategory}.
 */
class BlogCategoryCloud extends ViewableData {

	/**
	 * @var Int Limit to specific {@link BlogHolder}
	 */
	protected $holderId = null;

	/**
	 * @var string Property of {@link BlogCategory} to sort by.
	 */
	protected $sort = 'Title';

	/**
	 * @var int
	 */
	protected $limit = null;

	/**
	 * @var String
	 */
	protected $limitSortBy = array('Frequency' => 'DESC');

	/**
	 * @return ArrayList of {@link BlogCategoryCloud_Category}
	 */
	public function Categories() {
		$result = new ArrayList();

		$cats = BlogCategory::get();
		$entries = BlogEntry::get();
		
		if($this->holderId) {
			$cats = $cats->filter('ParentID', $this->holderId);
			$entries = $entries->filter('ParentID', $this->holderId);
		}

		$totalEntryCount = $entries->count();

		// TODO Not possible in a single query due to SS3 ORM
		$aggregateQuery = clone($cats->dataQuery()->query());
		$aggregateQuery->addLeftJoin(
			'BlogEntry_BlogCategories', 
			'"BlogEntry_BlogCategories"."BlogCategoryID" = "BlogCategory"."ID"'
		);
		$aggregateQuery->addLeftJoin(
			'BlogEntry', 
			'"BlogEntry_BlogCategories"."BlogEntryID" = "BlogEntry"."ID"'
		);
		$aggregateQuery->setSelect(array('"BlogCategory"."ID"'));
		$aggregateQuery->selectField(
			'COUNT("BlogEntry"."ID")', 
			'BlogEntryCount'
		);
		$aggregateQuery->setGroupBy(array('"BlogCategory"."ID"'));
		$aggregateResults = array();
		$maxEntryCount = 0;
		foreach($aggregateQuery->execute() as $v) {
			$aggregateResults[$v['ID']] = $v;
			if($v['BlogEntryCount'] > $maxEntryCount) $maxEntryCount = $v['BlogEntryCount'];
		}

		foreach($cats as $cat) {
			$result->push(BlogCategoryCloud_Category::create(
				$cat,
				$aggregateResults[$cat->ID]['BlogEntryCount'],
				$totalEntryCount,
				$maxEntryCount
			));
		}

		// Sort in-memory since it might be related to dynamic values like frequency
		// TODO Convert frequency calc to subselect and do sorting in SQL
		if($this->limit) {
			$result = $result->sort($this->limitSortBy);
			$result = $result->limit($this->limit);
		} 
		$result = $result->sort($this->sort);

		return $result;
	}

	public function setHolderId($id) {
		$this->holderId = $id;
		return $this;
	}

	public function getHolderId() {
		return $this->holderId;
	}

	public function setSort($sort) {
		$this->sort = $sort;
		return $this;
	}

	public function getSort() {
		return $this->sort;
	}

	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function forTemplate($field = null) {
		return $this->renderWith(get_class($this));
	}

}

class BlogCategoryCloud_Category extends ViewableData {

	protected $entryCount;

	protected $totalCount;

	protected $maxEntryCount;

	protected $classLevels = 10;

	public function __construct($category, $entryCount, $totalCount, $maxEntryCount) {
		parent::__construct();

		$this->failover = $category;
		$this->totalCount = $totalCount;
		$this->entryCount = $entryCount;
		$this->maxEntryCount = $maxEntryCount;
	}

	public function getTotalCount() {
		return $this->totalCount;
	}

	public function getCategory() {
		return $this->failover;
	}

	public function getEntryCount() {
		return $this->entryCount;
	}

	public function getMaxEntryCount() {
		return $this->maxEntryCount;
	}

	/**
	 * Which percentage of entries relate to this category,
	 * in a value from 0 to 1.
	 * 
	 * @return Float
	 */
	public function getPopularity() {
		return $this->entryCount/$this->totalCount;
	}

	/**
	 * How frequent is this tag, compared to the most used one,
	 * in a value from 0 to 1.
	 * 
	 * @return Float
	 */
	public function getFrequency() {
		return $this->entryCount/$this->maxEntryCount;
	}

	/**
	 * @return String
	 */
	public function getHtmlClass() {
		return 'level' . $this->getLevel();
	}

	/**
	 * @return Int
	 */
	public function getLevel() {
		return ceil($this->getFrequency()*$this->classLevels);
	}

}