<?php
namespace Application\Adapter;

use Zend\Paginator\Adapter;
use Zend\Paginator\Adapter\AdapterInterface;
use \ModelCriteria;

class PropelPaginator implements AdapterInterface
{
	protected $object = null;
	protected $rows = null;
	
	/**
	 * Create a new paginator instance
	 * @param ModelCriteria $obj Query object to paginate
	 */
	public function __construct($object = null)
	{
		$this->object = $object;
	}
	
	/**
	 * Returns an array of items for a page.
	 *
	 * @param integer $offset Page offset
	 * @param integer $itemCountPerPage Number of items per page
	 * @return array
	 */
	public function getItems($offset, $itemCountPerPage)
	{
		return $this->object->offset($offset)
			->limit($itemCountPerPage)
			->find();
	}
	
	/**
	 * Returns the total number of rows in the result set.
	 *
	 * @return integer
	 */
	public function count()
	{
		if (is_int($this->rows))
			return $this->rows;
	
		$this->rows = $this->object->count();
	
		return $this->rows;
	}
	
	/**
	 * Set the Propel Model to paginate
	 * @param ModelCriteria $obj
	 */
	public function setModel($obj)
	{
		$this->object = $obj;
		return $this;
	}
}