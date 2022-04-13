<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Doc Indexer
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
/**
 * Doc Indexer Component Index Model
 *
 * @package		Joomla
 * @subpackage	Doc Indexer
 * @since 1.5
 */
class DocIndexerModelIndex extends JModelLegacy
{
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();		
	}
	/**
	 * Indexing the selected files
	 *
	 * @param array $indexed
	 */
	function index($data) {
		jimport('joomla.filesystem.folder') ;
		$db = & JFactory::getDBO();		
		$access = $data['access'] ;
		$config = DocIndexerHelper::getConfig(false) ;		
		$allowedFileTypes = $config->allowed_file_types ;
		$sql = "SELECT filename FROM #__dix_docs";
		$db->setQuery($sql) ;
		if (version_compare(JVERSION, '3.0', 'ge'))
			$rows = $db->loadColumn();
		else
			$rows = $db->loadResultArray();
		$regex = '\.('.$allowedFileTypes.')$' ;					
		$path = JPath::clean($config->documents_path);
		$origpath = $path;
		$path = JPATH_ROOT . '/' . ltrim($path, '/');
		$pathLength = strlen($path);
		$docs =  JFolder::files($path, $regex, true, true);
		for ($i = 0 , $n = count($docs); $i < $n; $i++) {
			$file = $docs[$i] ;
			$file = substr($file, $pathLength + 1) ;								
			$file = str_replace("\\", '/', $file) ;
			$docs[$i] = $file ;			
		}				
		$docs = array_diff($docs, $rows) ;									
		if(count($docs)) 
		{
			require_once JPATH_ROOT.'/components/com_docindexer/helper/docs.php';
			$doc = array_pop($docs);
			if ($doc) 
			{
				//Index this doc
				$indexer = new DixDocs();
				$text = $indexer->getText($path.'/'.$doc) ;
				$row = JTable::getInstance('docindexer', 'Table') ;
				$row->title = $doc ;
				$row->directory = $origpath ;
				$row->filename = $doc ;
				$row->filesize =  filesize($path.'/'.$doc) ;
				$row->doc_content = $text ;
				$row->hits = 0 ;
				$row->access =  $access ;
				$row->published =  1 ;
				$row->component = JFactory::getApplication()->input->getString('component', '') ;
				$row->store();
				if ($row->component == 'com_docman') 
				{					
					$sql = 'UPDATE #__docman_documents SET indexed_content='.$db->Quote($row->doc_content).' WHERE storage_path='.$db->quote($doc);
					$db->setQuery($sql) ;
					$db->execute();	
				}
				elseif ($row->component == 'com_edocman')
				{
					$sql = 'UPDATE #__edocman_documents SET indexed_content='.$db->Quote($row->doc_content).' WHERE filename='.$db->quote($doc);
					$db->setQuery($sql);
					$db->execute();
				}
				$input = JFactory::getApplication()->input;
				$input->set('file', $doc) ;
				$input->set('success', true) ;
				return true ;
			} 
			else 
			{
				return false ;
			}											
		} 
		else 
		{
			return false ;		
		}
	}
}