<?php
//
// Created on 2006/11/09 by nao-pon http://hypweb.net/
// $Id: pginfo.en.php,v 1.1 2006/11/12 08:43:57 nao-pon Exp $
//

$msg = array(
	'title_update'  => '�ڡ�������DB����',
	'msg_adminpass' => '�����ԥѥ����',
	'msg_all' => 'DB�򤹤٤ƽ����&������',
	'msg_select' => '�ʲ��������򤷤ƽ����&������',
	'msg_hint' => '���Ƴ�����Ϥ��٤Ƥ˥����å���Ĥ��Ƽ¹Ԥ��Ƥ���������',
	'msg_init' => '�ڡ������ܾ���DB',
	'msg_count' => '�ڡ��������󥿡�����DB',
	'msg_noretitle' => '��¸�Υڡ����ϥ����ȥ������ݻ����롣',
	'msg_retitle' => '��¸�Υڡ����⥿���ȥ�����Ƽ������롣',
	'msg_plain_init' => '�����ѥƥ�����DB �� �ڡ����֥�󥯾���DB',
	'msg_plain_init_notall' => '�����ѥƥ�����DB�����Υڡ����Τ߽������롣',
	'msg_plain_init_all' => '���٤ƤΥڡ�����������롣(���֤��ݤ���ޤ���)',
	'msg_attach_init' => 'ź�եե��������DB',
	'msg_progress_report' => '��Ľ����:',
	'msg_now_doing' => '�����������С�¦�ǽ�����Ǥ���<br />���ο�Ľ���̤ˡ֤��٤Ƥν�������λ���ޤ������פ�ɽ�������ޤ�<br />���Υڡ����򳫤����ޤޤˤ����֤��Ƥ���������',
	'msg_next_do' => '<span style="color:blue;">�����С��μ¹Ի������¤ˤ����������Ǥ��ޤ�����<br />���ο�Ľ���̺ǲ����Ρ�³���ν�����¹ԡפ򥯥�å�����<br />����³��������ԤäƤ���������</span>',
	'btn_submit'    => '�¹�',
	'btn_next_do'    => '³���ν�����¹�',
	'msg_done'      => '���٤Ƥν�������λ���ޤ�����',
	'msg_usage'     => "
* Description

:Update Page Information DB|
Scan all page files and rebuild page information DB.

* Notice

Please wait a while, after clicking 'Run' button.

Max PHP execution time on this server is set to &font(red,b){%1d}; seconds.
So, this process will be paused at every &font(red,b){%2d}; seconds and will show 'Continue' button.
If you see 'Continue' button, you should click this to complete this procedure.

* Run

Please click 'Run' button.
If you cannot see 'Run' button, you should login as a Administrator user.

Options marked * mean, they have not beed processed yet.",
	// for page permission
	'title_permission' => 'Permission setting of $1',
	'edit_permission' => 'Editable Permission',
	'view_parmission' => 'Readable Permission',
	'parmission_setting' => 'Detailed setting of permission(An administrator & administer group are always admitted.)',
	'lower_page_inherit' => 'Inherit setting to a lower page.',
	'inherit_forced' => 'Inherited forcibly. (cannot set it in a lower page)',
	'inherit_default' => 'Inherited as the default value. (can set it in a lower page)',
	'inherit_onlythis' => 'Not Inherited. (Setting only for this page)',
	'permission_none' => 'Not set permission. (Reset value)',
	'default_inherit' => 'Indication contents of the following "Detailed setting of permission" are applied now.<br />When you change detailed setting of permission, please choose either of "Inherit setting to a lower page".',
	'can_not_set' => 'Setting of this page is not possible so that the forced inherit is set in a higher page.',
	'admit_all_group' => 'Admit in all groups.',
	'not_admit_all_group' => 'Not admit in all groups.',
	'admit_select_group' => 'Admit only in select groups.',
	'admit_all_user' => 'Admit in all users.',
	'not_admit_all_user' => 'Not admit in all users.',
	'admit_select_user' => 'Admit only in select users.',
	'submit' => 'Regist edit / read permission setting',
	'no_parmission_title' => 'Not have enough permission to make permission setting of $1',
	'no_parmission' => 'You don\'t have enough permission to make permission setting. It is only an administrator and a page creator that can do it by authority setting.',
	'done_ok' => 'Saved editing / reading permission.',
);
?>