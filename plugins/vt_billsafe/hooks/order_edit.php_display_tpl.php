<?php
	if($this->order_data['order_data']['payment_code'] == 'vt_billsafe') {
		$template = new Template();
		$tpl = 'vt_billsafe_orderEdit.html';
		$template->getTemplatePath($tpl, 'vt_billsafe', 'xtCore/pages', 'plugin');

		$template->tpl_path = str_replace('xtCore/pages/', '', $template->tpl_path);
		$template->tpl_short_path = str_replace('xtCore/pages/', '', $template->tpl_short_path);
		$template->tpl_root_path = str_replace('xtCore/pages/', '', $template->tpl_root_path);
	}
?>