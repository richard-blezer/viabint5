<?php
/*
* netvise Autocomplete Search
* #################################
* netvise - Sean Nicholas Dieterle
* Classen-Kappelmann-Straße 26
* 50931 Köln
* www.netvise.de
* info@netvise.de
*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db, $xtLink;

function buildLikeQuery($column, $keywords) {
	global $filter;

	$first = true;
	$keywords = explode(' ', $keywords);

	$query = '(';

	foreach($keywords as $keyword) {
		if(!$first) {
			$query .= ' AND ';
		} else {
			$first = false;
		}
		$escaped_keyword = $filter->_filter($keyword);
		$query .= $column . " LIKE '%" . $escaped_keyword . "%'";
	}

	$query .= ')';

	return $query;
}

//BEGIN:

$display_output = false;

$data = array();

$unescaped_keyword = $_GET['keywords'];
$escaped_keywords = $filter->_filter($unescaped_keyword);
$keywords = str_replace(' ', '%', $escaped_keywords);
$keywords = '%' . $keywords . '%';


//PRODUCTS:

if(NV_AUTOCOMPLETE_PRODUCTS_ACTIVE == 'true') {
	$sq_products = new getProductSQL_query();
	$sq_products->setPosition('getSearchData');
	$sq_products->setFilter('Language');
	$sq_products->setSQL_WHERE("AND (".buildLikeQuery('pd.products_keywords', $unescaped_keyword)." OR ".buildLikeQuery('pd.products_name', $unescaped_keyword)." ) ");
	$sq_products->setSQL_SORT("MATCH(pd.products_name) AGAINST ('".$escaped_keywords."') DESC, pd.products_name");
	$sq_products->setSQL_LIMIT(NV_AUTOCOMPLETE_MAX_PRODUCTS);
	$query = $sq_products->getSQL_query('p.products_id, pd.products_name, su.url_text');
	$record = $db->Execute($query);

	if ($record->RecordCount() > 0) {
		while(!$record->EOF){
			$link_array = array('page'=> 'product', 'type'=>'product',
				'name' => $record->fields['products_name'], 'id' => $record->fields['products_id'],
				'seo_url' => $record->fields['url_text']);
			$link = $xtLink->_link($link_array);
			$data[] = array(
				'name' => $record->fields['products_name'],
				'link' => $link,
				'type' => TEXT_NV_AUTOCOMPLETE_TYPE_PRODUCTS,
			);
			$record->MoveNext();
		} $record->Close();
	}
}


//CATEGORIES:

if(NV_AUTOCOMPLETE_CATEGORIES_ACTIVE == 'true') {
	$sq_categories = new getCategorySQL_query();
	$sq_categories->setPosition('getSearchData');
	$sq_categories->setFilter('Language');
	$sq_categories->setFilter('Seo');
	$sq_categories->getFilter();
	$sq_categories->setSQL_WHERE("AND cd.categories_name LIKE '".$keywords."'");
	$sq_categories->setSQL_SORT("cd.categories_name ASC");
	$sq_categories->setSQL_LIMIT(NV_AUTOCOMPLETE_MAX_CATEGORIES);
	$query = $sq_categories->getSQL_query('c.categories_id, cd.categories_name, su.url_text');
	$record = $db->Execute($query);

	if ($record->RecordCount() > 0) {
		while(!$record->EOF){
			$link_array = array('page'=> 'categorie',
				'type' => 'category',
				'name' => $record->fields['categories_name'],
				'text' => $record->fields['categories_name'],
				'id' => $record->fields['categories_id'],
				'seo_url' => $record->fields['url_text'],
			);
			$link = $xtLink->_link($link_array);
			$data[] = array(
				'name' => $record->fields['categories_name'],
				'link' => $link,
				'type' => TEXT_NV_AUTOCOMPLETE_TYPE_CATEGORIES,
			);
			$record->MoveNext();
		} $record->Close();
	}
}


//MANUFACTURERS:

if(NV_AUTOCOMPLETE_MANUFACTURERS_ACTIVE == 'true') {
	$sq_manufacturers = new getManufacturerSQL_query();
	$sq_manufacturers->setPosition('getSearchData');
	$sq_manufacturers->setFilter('Seo');
	$sq_manufacturers->setSQL_WHERE("AND m.manufacturers_name LIKE '".$keywords."' AND m.manufacturers_status = 1");
	$sq_manufacturers->setSQL_SORT("m.manufacturers_name ASC");
	$sq_manufacturers->setSQL_LIMIT(NV_AUTOCOMPLETE_MAX_MANUFACTURERS);
	$query = $sq_manufacturers->getSQL_query('m.manufacturers_id, m.manufacturers_name, su.url_text');
	$record = $db->Execute($query);

	if ($record->RecordCount() > 0) {
		while(!$record->EOF){
			$link_array = array(
				'page' => 'manufacturers',
				'type' => 'manufacturer',
				'name' => $record->fields['manufacturers_name'],
				'id' => $record->fields['manufacturers_id'],
				'seo_url' => $record->fields['url_text']
			);
			$link = $xtLink->_link($link_array);
			$data[] = array(
				'name' => $record->fields['manufacturers_name'],
				'link' => $link,
				'type' => TEXT_NV_AUTOCOMPLETE_TYPE_MANUFACTURERS,
			);
			$record->MoveNext();
		} $record->Close();
	}
}


//CONTENT:

if(NV_AUTOCOMPLETE_CONTENT_ACTIVE == 'true') {
	$sq_content = new getContentSQL_query();
	$sq_content->setPosition('getSearchData');
	$sq_content->setFilter('Language');
	$sq_content->setFilter('Seo');
	$sq_content->setSQL_WHERE("AND cd.content_title LIKE '".$keywords."' AND c.content_status = 1");
	$sq_content->setSQL_SORT("cd.content_title ASC");
	$sq_content->setSQL_LIMIT(NV_AUTOCOMPLETE_MAX_CONTENT);
	$query = $sq_content->getSQL_query('c.content_id, cd.content_title, su.url_text, c.link_ssl');
	$record = $db->Execute($query);

	if ($record->RecordCount() > 0) {
		while(!$record->EOF){
			$conn = 'NOSSL';
			if ($record->fields['link_ssl']=='1') {
				$conn='SSL';
			}
			$link_array = array(
				'page' => 'content',
				'type' => 'content',
				'name' => $record->fields['content_title'],
				'id' => $record->fields['content_id'],
				'seo_url' => $record->fields['url_text'],
				'conn' => $conn
			);
			$link = $xtLink->_link($link_array);
			$data[] = array(
				'name' => $record->fields['content_title'],
				'link' => $link,
				'type' => TEXT_NV_AUTOCOMPLETE_TYPE_CONTENT,
			);
			$record->MoveNext();
		} $record->Close();
	}
}


header('Access-Control-Allow-Origin: *');

echo json_encode($data);
