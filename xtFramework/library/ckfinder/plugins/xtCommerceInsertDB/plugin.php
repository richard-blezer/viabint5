<?php
function insertImageToXtDB($currentFolder, $uploadedFile, $sFilePath)
{
	global $xtclass;
//var_dump(/*$currentFolder, */$uploadedFile, $sFilePath, $_GET/*, $xtclass*/);

	$type = strtolower(substr(get_class($xtclass), 5));
	$filename = substr(strrchr($sFilePath, '/'), 1);
/*var_dump($_GET); exit;
	if ( ! empty($_GET['mgID']))
	{*/
		if ($type === 'files')
		{
			$xtclass->setMediaData(array(
				'file' => $filename,
				'type' => $type,
				'class' => $_GET['type'],
				'download_status' => ($_GET['type'] === 'files_free' ? 'free' : 'order'),
				'mgID' => isset($_GET['mgID']) ? $_GET['mgID'] : ''
			));
		}
		else
		{
			$m_id = $xtclass->setMediaData(array(
				'file' => $filename,
				'type' => $type,
				'class' => $_GET['type'],
				'mgID' => isset($_GET['mgID']) ? $_GET['mgID'] : ''
			));

			$xtclass->setMediaToCurrentType($filename, $m_id);
			$xtclass->processImage($filename);
		}
//	}


/*	global $db;

	$sql = "INSERT INTO `".TABLE_MEDIA."` (`file`, `type`, `class`, `download_status`, `owner`) VALUES (?, ?, ?, ?, ?)";
	$values = array(
		substr(strrchr($sFilePath, DIRECTORY_SEPARATOR), 1),
		in_array($_GET['type'], array('Files_order', 'Files_free'), TRUE) ? 'files' : 'images',
		strtolower($_GET['type']),
		($_GET['type'] === 'Files_order') ? 'order' : 'free',
		$_SESSION['admin_user']['user_id']
	);

	$db->Execute($sql, $values);
	$values = array($db->Insert_ID());
	$rs = $db->Execute('SELECT `mg_id` FROM `'.TABLE_MEDIA_GALLERY.'` WHERE `class` = ?', array(strtolower($_GET['type'])));
	if ($rs->RecordCount() != 1)
	{
		return false;
	}

	$values[] = $rs->fields['mg_id'];

	$db->Execute('INSERT INTO `'.TABLE_MEDIA_TO_MEDIA_GALLERY.'` (`m_id`, `mg_id`) VALUES(?, ?)', $values);
*/
	return true;
}

$config['Hooks']['AfterFileUpload'][] = 'insertImageToXtDB';