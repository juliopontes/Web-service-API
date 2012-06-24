<?php
/**
 * @package     WebService.Application
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * WebService GET content class
 *
 * @package     WebService.Application
 * @subpackage  Controller
 * @since       1.0
 */
class WebServiceControllerV1JsonBaseGet extends WebServiceControllerV1Base
{
	/**
	 * @var    string  The limit of the results
	 * @since  1.0
	 */
	protected $limit = 20;

	/**
	 * @var    string  The maximum number of results per page
	 * @since  1.0
	 */
	protected $maxResults = 100;

	/**
	 * @var    string  The offset of the results
	 * @since  1.0
	 */
	protected $offset = 0;

	/**
	 * @var    array  The fields of the results
	 * @since  1.0
	 */
	protected $fields;

	/**
	 * @var    string  The content id. It may be numeric id or '*' if all content is refeered
	 * @since  1.0
	 */
	protected $id = '*';

	/**
	 * @var    string  The order of the results
	 * @since  1.0
	 */
	protected $order = null;

	/**
	 * @var    string  The minimum created date of the results
	 * @since  1.0
	 */
	protected $since = '01-01-1970';

	/**
	 * @var    string  The maximum created date of the results
	 * @since  1.0
	 */
	protected $before = 'now';

	/**
	 * Get the content ID from the input. It may also return '*' refeering all the content
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getContentId()
	{
		// Get route from the input
		$route = $this->input->get->getString('@route');

		// Break route into more parts
		$routeParts = explode('/', $route);

		// Content is not refered by a number id
		if (count($routeParts) > 0 && (!is_numeric($routeParts[0]) || $routeParts[0] < 0) && !empty($routeParts[0]))
		{
			$this->app->errors->addError("301");
			return;
		}

		// All content is refered
		if ( count($routeParts) == 0 || strlen($routeParts[0]) === 0 )
		{
			return $this->id;
		}

		// Specific content id
		return $routeParts[0];
	}

	/**
	 * Get the offset from input or the default one
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getOffset()
	{
		$offset = $this->input->get->getString('offset');

		if (isset($offset))
		{
			if ( is_numeric($offset) && $offset >= 0)
			{
				return $offset;
			}

			$this->app->errors->addError("302");
			return;
		}
		else
		{
			return $this->offset;
		}
	}

	/**
	 * Get the limit from input or the default one
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getLimit()
	{
		$limit = $this->input->get->getString('limit');

		if (isset($limit))
		{
			$limit = min($this->maxResults, $limit);
			if (is_numeric($limit) && $limit > 0)
			{
				return $limit;
			}

			$this->app->errors->addError("303");
			return;
		}
		else
		{
			return $this->limit;
		}
	}

	/**
	 * Get the fields from input or the default one
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	protected function getFields()
	{

		$fields = $this->input->get->getString('fields');

		if (isset($fields))
		{
			$fields = preg_split('#[\s,]+#', $fields, null, PREG_SPLIT_NO_EMPTY);

			return $fields;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the order from input or the default one
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	protected function getOrder()
	{

		$order = $this->input->get->getString('order');

		if (isset($order))
		{
			$order = preg_split('#[\s,]+#', $order, null, PREG_SPLIT_NO_EMPTY);

			foreach ($order as $key => $field)
			{
				if (!array_key_exists($field, $this->fieldsMap))
				{
					$this->app->errors->addError("307");
					return;
				}
			}

			if ($order == false)
			{
				return null;
			}

			return $order;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the since date limitation from input or the default one
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getSince()
	{
		$since = $this->input->get->getString('since');

		if (isset($since))
		{
			$date = new JDate($since);
			if (!empty($since) && checkdate($date->__get('month'), $date->__get('day'), $date->__get('year')))
			{
				return $date->toSql();
			}

			$this->app->errors->addError("304");
			return;
		}
		else
		{
			$date = new JDate($this->since);
			return $date->toSql();
		}
	}

	/**
	 * Get the before date limitation from input or the default one
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getBefore()
	{

		$before = $this->input->get->getString('before');

		if (isset($before))
		{
			$date = new JDate($before);
			if (!empty($before) && checkdate($date->__get('month'), $date->__get('day'), $date->__get('year')))
			{
				return $date->toSql();
			}

			$this->app->errors->addError("305");
			return;
		}
		else
		{
			$date = new JDate($this->before);
			return $date->toSql();
		}
	}

	/**
	 * Init parameters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function init()
	{
		// Set the fields
		$this->readFields();

		// Content id
		$this->id = $this->getContentId();

		// Results offset
		$this->offset = $this->getOffset();

		// Results limits
		$this->limit = $this->getLimit();

		// Returned fields
		$this->fields = $this->getFields();

		// Map fields according to the application database
		if ($this->fields != null)
		{
			$this->fields = $this->mapFieldsIn($this->fields);
		}

		// Results order
		$this->order = $this->getOrder();

		if ($this->order != null)
		{
			$this->order = $this->mapFieldsIn($this->order);
		}

		// Since
		$this->since = $this->getSince();

		// Before
		$this->before = $this->getBefore();
	}

	/**
	 * Controller logic
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Init request
		$this->init();

		if ($this->app->errors->errorsExist() == true)
		{
			$this->app->setBody(json_encode($this->app->errors->getErrors()));
			$this->app->setHeader('status', $this->app->errors->getResponseCode(), true);
			return;
		}

		// Returned data
		$data = $this->getContent();

		// Format the results properly
		$this->parseData($data);
	}

	/**
	 * Get content by id or all content
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	protected function getContent()
	{
		// New content model
		$model = new WebServiceModelBase;

		// Get content state
		$modelState = $model->getState();

		// Set content type that we need
		$modelState->set('content.type', $this->type);
		$modelState->set('content.id', $this->id);

		// Set date limitations
		$modelState->set('filter.since', $this->since);
		$modelState->set('filter.before', $this->before);

		// A specific content is requested
		if (strcmp($this->id, '*') !== 0)
		{
			// Get the requested data
			$item = $model->getItem();

			print_r($item->dump());

			// No item found
			if ($item == false)
			{
				return false;
			}

			return $item;
		}
		// All content is requested
		else
		{
			// Set offset and results limit
			$modelState->set('list.offset', $this->offset);
			$modelState->set('list.limit', $this->limit);

			// Get content from Database
			$items = $model->getList();

			// No items found
			if ($items == false)
			{
				return false;
			}

			return $items;
		}
	}

	/**
	 * Parse the returned data from database
	 *
	 * @param   mixed  $data  A JContent object, an array of JContent or a boolean.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function parseData($data)
	{
		// There is no content for the request
		if ($data == false)
		{
			$data = new stdClass;
		}

		if (count($this->order) > 0)
		{
			usort($data, array($this, "orderData"));
		}

		$data = $this->pruneFields($data, $this->fields);

		// Output the results
		$this->app->setBody(json_encode($data));
	}
}