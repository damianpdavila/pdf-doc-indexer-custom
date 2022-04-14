<?php
/**
 * @version		1.0.0
 * @package		Joomla
 * @subpackage	Doc Indexer
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once JPATH_ROOT.'/components/com_docindexer/helper/helper.php';
class plgSearchDoc_Indexer extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	* @return array An array of search areas
	*/
	function onContentSearchAreas()
	{
		static $areas = array(
    		'doc_indexer' => 'Journals'
    	);
    	return $areas;
	}

	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		=& JFactory::getDBO();	
	    if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys($this->onContentSearchAreas()) )) {
				return array();
			}
		}    	
		$limit =  $this->params->get('search_limit');
    	$text = trim( $text );
        $text =  strtolower($text);
    	if ($text == '') {
    		return array();
    	}
    	$section 	= JText::_( 'PDF Files' );
    	$wheres 	= array();
    	switch ($phrase)
    	{
    		case 'exact':
    			$text		= $db->Quote( '%'.$db->escape( $text, true ).'%', false );
    			$wheres2 	= array();
    			$wheres2[] 	= 'LOWER(a.title) LIKE '.$text;
    			$wheres2[] 	= 'LOWER(a.doc_content) LIKE '.$text;
    			$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
    			break;
    
    		case 'all':
    		case 'any':
    		default:
    			$words 	= explode( ' ', $text );
    			$wheres = array();
    			foreach ($words as $word)
    			{
    				$word		= $db->Quote( '%'.$db->escape( $word, true ).'%', false );
    				$wheres2 	= array();
    				$wheres2[] 	= 'LOWER(a.title) LIKE '.$word;
    				$wheres2[] 	= 'LOWER(a.doc_content) LIKE '.$word;
    				$wheres[] 	= implode( ' OR ', $wheres2 );
    			}
    			$where 	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
    			break;
    	}
    
    	switch ( $ordering )
    	{
    		case 'oldest':
    			$order = 'a.id ASC';
    			break;
    
    		case 'popular':
    			$order = 'a.hits DESC';
    			break;
    
    		case 'alpha':
    			$order = 'a.title ASC';
    			break;
    		case 'newest':
    		default:
    			$order = 'a.id DESC';
    	}	
    	$user = & JFactory::getUser() ;    	
    	$query = 'SELECT a.id, a.id AS cat_id, a.title AS title, a.doc_content AS text, a.component, NOW() AS `created`, '	
    	.$db->Quote($section) .' AS section, a.directory AS directory, a.filename AS filename, '
    	. ' "1" AS browsernav'
    	. ' FROM #__dix_docs AS a'	
    	. ' WHERE ('. $where .') AND a.access IN ('.implode(',', $user->getAuthorisedViewLevels()).')' 
    	. ' AND a.published = 1'	
    	. ' ORDER BY '. $order
    	;
    	$db->setQuery( $query, 0, $limit );		
    	$rows = $db->loadObjectList();    	
    	foreach($rows as $key => $row) {
    		if($row->component) 
			{
    			$rows[$key]->href = DocIndexerHelper::getDetailLink($row->component, $row->id) ;
			}
    		elseif(isset($row->directory))
			{
				$rows[$key]->href = ltrim($row->directory, '/') . '/' . $row->filename;
			}
			else
			{
    			$rows[$key]->href = JRoute::_('index.php?option=com_docindexer&task=download&id='.$row->id);
			}
    	}
    	return $rows ;
	}	
}