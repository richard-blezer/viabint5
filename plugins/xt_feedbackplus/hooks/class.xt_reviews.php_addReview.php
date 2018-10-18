<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

//global $data_array;
$feedbackplus_campaign_id = false;
if (isset($xtPlugin->active_modules['xt_feedbackplus']) && isset($data['feedback_life_circle_id'])) {
    $sql = "SELECT * FROM ".TABLE_FEEDBACKPLUS_LIFE_CIRCLES." WHERE feedback_life_circle_id=?";
    $rs = $db->Execute($sql, array($data['feedback_life_circle_id']));
    if ($rs->RecordCount()==1) {

        $testing = (int)$db->GetOne('SELECT `feedbackplus_campaign_testing` FROM '.TABLE_FEEDBACKPLUS_CAMPAIGNS. " WHERE `feedbackplus_campaign_id`=?", array($rs->fields['feedbackplus_campaign_id']));

        if ($testing == 1)
        {
            $data_array['review_source'] = 'feedback+test';
        }

        $data_array['feedbackplus_campaign_id']=$rs->fields['feedbackplus_campaign_id'];
        $data_array['feedbackplus_life_circle_id']=$data['feedback_life_circle_id'];
    }
}