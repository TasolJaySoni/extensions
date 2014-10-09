<?php
/**
 * @package     IJoomer.extensions
 * @subpackage  icms
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class categories
{

	private $db;

	function __construct()
	{
		$this->db  = JFactory::getDBO();
	}

	/**
	 * @uses    Category list
	 * @example the json string will be like, :
	 *    {
	 *        "extName":"icms",
	 *        "extView":"categories",
	 *        "extTask":"allCategories",
	 *        "taskData":{}
	 *    }
	 *
	 */
	public function category()
	{
		$id         = IJReq::getTaskData('id', 0, 'int');
		$categories = $this->getCategories($id);
		$articles   = ($id <= 0) ? array() : $this->getArticles($id);

		return $this->prepareObject($articles, $categories);
	}

	// to fetch parent/children categories
	private function getCategories($id)
	{
		JRequest::setVar('id', $id);
		include_once JPATH_SITE . '/libraries/joomla/application/categories.php';
		include_once JPATH_SITE . '/components/com_content/models/categories.php';

		if ($id == 0)
		{
			$ContentModelCategories = new ContentModelCategories;
			$categories             = $ContentModelCategories->getItems();
		}
		else
		{
			$ContentModelCategory = new ContentModelCategory;
			$categories           = $ContentModelCategory->getChildren();
		}

		return (json_decode(json_encode($categories)));
	}

	// to fetch articles
	private function getArticles($id)
	{
		JRequest::setVar('id', $id);
		include_once JPATH_SITE . '/libraries/joomla/application/categories.php';
		include_once JPATH_SITE . '/components/com_content/models/categories.php';

		if ($id == 0)
		{
			$articles = array();
		}
		else
		{
			$ContentModelCategory = new ContentModelCategory;
			$articles             = $ContentModelCategory->getItems();
			$articles             = json_decode(json_encode($articles));
		}

		return (json_decode(json_encode($articles)));
	}

	/**
	 * Function for prepare object with list of articles and categories
	 *
	 * @param Array $articles
	 * @param Array $categories
	 *
	 * @return Array
	 */
	private function prepareObject($articles, $categories)
	{
		$totalarticles   = count($articles);
		$totalcategories = count($categories);
		$articlepageno   = IJReq::getTaskData('pageNO', 1, 'int');

		if ($totalarticles <= 0 && $totalcategories <= 0)
		{
			$jsonarray['code'] = 204;

			return $jsonarray;
		}

		if ($totalarticles <= 0)
		{
			$articleArray['articles'] = array();
		}
		else
		{
			require_once JPATH_COMPONENT . '/extensions/icms/articles.php';
			$articlesObj  = new articles;
			$articleArray = $articlesObj->getArticleList($articles, $totalarticles, true);
		}

		if ($totalcategories <= 0 or $articlepageno > 1)
		{
			$categoryArray['categories'] = array();
		}
		else
		{
			require_once JPATH_SITE . '/components/com_content/models/category.php';
			$categoryObj   = new ContentModelCategory;
			$inc           = 0;
			$categoryArray = array();
			foreach ($categories as $value)
			{
				$subcategory      = $this->getCategories($value->id);
				$subcategorycount = count($subcategory);
				$ischild          = false;
				$ischild          = ($subcategorycount > 0 or $value->numitems > 0) ? true : $this->getChildCount($value->id);
				if ($ischild)
				{
					$categoryArray['categories'][$inc]['categoryid']  = $value->id;
					$categoryArray['categories'][$inc]['title']       = $value->title;
					$categoryArray['categories'][$inc]['description'] = strip_tags($value->description);

					$images = array();
					preg_match_all('/(src)=("[^"]*")/i', $value->description, $images);
					$imgpath = str_replace(array('src="', '"'), "", $images[0]);
					if (!empty($imgpath[0]))
					{
						$image_properties = parse_url($imgpath[0]);
						if (empty($image_properties['host']))
						{
							$imgpath[0] = JUri::base() . $imgpath[0];
						}
					}

					$categoryArray['categories'][$inc]['image']         = ($imgpath) ? $imgpath[0] : '';
					$categoryArray['categories'][$inc]['parent_id']     = $value->parent_id;
					$categoryArray['categories'][$inc]['hits']          = $value->hits;
					$categoryArray['categories'][$inc]['totalarticles'] = ($value->numitems) ? $value->numitems : 0;

					$query = 'SELECT count(id)
				  			  FROM #__categories
				  			  WHERE parent_id=' . $value->id . ' AND published = 1';
					$this->db->setQuery($query);
					$categoryArray['categories'][$inc]['totalcategories'] = $this->db->loadResult();
					$inc++;
				}
			}
			if (!$categoryArray)
			{
				$categoryArray['categories'] = array();
			}
		}
		if (!isset($categoryArray['categories']) && !isset($articleArray['articles']) or (empty($categoryArray) && empty($articleArray)))
		{
			$jsonarray['code'] = 204;

			return $jsonarray;
		}
		$jsonarray['code']       = 200;
		$jsonarray['total']      = $totalarticles;
		$jsonarray['pageLimit']  = ICMS_ARTICLE_LIMIT;
		$jsonarray['articles']   = $articleArray['articles'];
		$jsonarray['categories'] = $categoryArray['categories'];

		return $jsonarray;
	}

	// to fetch category child count.
	private function getChildCount($id)
	{
		$childcategory = $this->getCategories($id);
		foreach ($childcategory as $value)
		{
			if ($value->numitems > 0)
			{
				return true;
			}
			else
			{
				$this->getChildCount($value->id);
			}
		}

		return false;
	}
}