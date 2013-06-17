<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
/**
 * function to migrate quickmail history files attachment to the new file version from 1.9 to 2.x
 */
function migrate_quickmail_20(){
	global $DB;
	//migration of attachments
	$fs = get_file_storage();
	$quickmail_log_records=$DB->get_records_select('block_quickmail_log','attachment<>\'\'');
	foreach($quickmail_log_records as $quickmail_log_record){
		//searching file into mdl_files
		//analysing attachment content
		$filename=$quickmail_log_record->attachment;
		$filepath='';
		$notrootfile=strstr($quickmail_log_record->attachment,'/');		
		if($notrootfile){
			$filename=substr($quickmail_log_record->attachment,strrpos($quickmail_log_record->attachment, '/',-1)+1);
			$filepath='/'.substr($quickmail_log_record->attachment,0,strrpos($quickmail_log_record->attachment,'/',-1)+1);
		}else{
			$filepath='/';
			$filename=$quickmail_log_record->attachment;
		}
		$fs = get_file_storage();
		$coursecontext = get_context_instance(CONTEXT_COURSE,$quickmail_log_record->courseid);
		$coursefile=$fs->get_file($coursecontext->id, 'course', 'legacy', 0, $filepath, $filename);
		if($coursefile){
			if($notrootfile){
				//rename
				$filename=str_replace('/', '_', $quickmail_log_record->attachment);
				$filepath='/';
				$quickmail_log_record->attachment=$filename;
				$DB->update_record('block_quickmail_log', $quickmail_log_record);
			}
			$file_record = array('contextid'=>$coursecontext->id, 'component'=>'block_quickmail', 'filearea'=>'attachment_log', 'itemid'=>$quickmail_log_record->id, 'filepath'=>$filepath, 'filename'=>$filename,
					'timecreated'=>$coursefile->get_timecreated(), 'timemodified'=>$coursefile->get_timemodified());
			if(!$fs->file_exists($coursecontext->id, 'block_quickmail', 'attachment_log', 0, $filepath, $filename)){
				$fs->create_file_from_storedfile($file_record, $coursefile->get_id());
			}
		}
	}
}