<?php
/**
 * Administration panel
 *
 * @package Cotonti
 * @version 0.7.0
 * @author Neocrome, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('admin', 'a');
cot_block($usr['isadmin']);

cot_require_api('extrafields');
cot_require_api('auth');

$id = cot_import('id', 'G', 'INT');
$c = cot_import('c', 'G', 'TXT');
$d = cot_import('d', 'G', 'INT');
$d = empty($d) ? 0 : (int) $d;
$area = cot_import('area', 'G', 'ALP');

$t = new XTemplate(cot_skinfile(array('admin', 'structure', $area)));
/* === Hook === */
foreach (cot_getextplugins('admin.structure.first') as $pl)
{
	include $pl;
}
/* ===== */

if (empty($area))
{
	cot_redirect(cot_url('message', 'msg=950', '', true));
}

if ($n == 'options')
{
	if ($a == 'update')
	{
		$rstructure['structure_code'] = cot_import('rstructurecode', 'P', 'TXT');
		$rstructure['structure_path'] = cot_import('rstructurepath', 'P', 'TXT');
		$rstructure['structure_title'] = cot_import('rstructuretitle', 'P', 'TXT');
		$rstructure['structure_desc'] = cot_import('rstructuredesc', 'P', 'TXT');
		$rstructure['structure_icon'] = cot_import('rstructureicon', 'P', 'TXT');
		$rstructure['structure_locked'] = (cot_import('rstructurelocked', 'P', 'BOL')) ? 1 : 0;
		$rstructure['structure_order'] = cot_import('rstructureorder', 'P', 'ALP').".".cot_import('rstructureway', 'P', 'ALP');
		$rstructure['structure_ratings'] = cot_import('rstructureallowratings', 'P', 'BOL');

		foreach ($cot_extrafields['structure'] as $row)
		{
			$rstructure[$row['field_name']] = cot_import_extrafields('rstructure'.$row['field_name'], $row);
		}

		$rtplmode = cot_import('rstructuretplmode', 'P', 'INT');
		$rstructure['structure_tpl'] = ($rtplmode == 1) ? '' : (($rtplmode == 3) ? 'same_as_parent' : cot_import('rtplforced', 'P', 'ALP'));

		$sqql = $db->query("SELECT structure_code FROM $db_structure WHERE structure_id='".$id."' ");
		$roww = $sqql->fetch();

		/* === Hook === */
		foreach (cot_getextplugins('admin.structure.options.update') as $pl)
		{
			include $pl;
		}
		/* ===== */

		if ($roww['structure_code'] != $rstructure['structure_code'])
		{
			$sql = $db->update($db_structure, array("structure_code" => $rstructure['structure_code']), "structure_code='".$db->prep($roww['structure_code'])."' AND structure_area='".$db->prep($area)."'");
			$sql = $db->update($db_auth, array("auth_option" => $rstructure['structure_code']), "auth_code='".$db->prep($area)."' AND auth_option='".$db->prep($roww['structure_code'])."'");
			$area_updatecat = 'cot_'.$area.'_updatecat';
			(function_exists($area_updatecat)) ? $area_updatecat($roww['structure_code'], $rstructure['structure_code']) : FALSE;
			cot_auth_reorder();
			cot_auth_clear('all');
		}

		$db->update($db_structure, $rstructure, "structure_id='".$id."'");

		if ($cache)
		{
			$cache->db->remove('structure', 'system');
			$cfg['cache_'.$area] && $cache->page->clear($area);
		}

		cot_message('Updated');

		cot_redirect(cot_url('admin', 'm=structure&area='.$area.'&d='.$d.$additionsforurl, '', true));
	}
	elseif ($a == 'resync')
	{
		cot_check_xg();
		$area_resync = 'cot_'.$area.'_resync';
		(function_exists($area_resync)) ? ($area_resync($id) ? cot_message('Resynced') : cot_message('Error')) : cot_message('Error');
		($cache && $cfg['cache_'.$area]) && $cache->page->clear($area);
	}

	$sql = $db->query("SELECT * FROM $db_structure WHERE structure_id='$id' LIMIT 1");
	cot_die($sql->rowCount() == 0);

	$handle = opendir('./themes/'.$cfg['defaultskin'].'/');
	$allskinfiles = array();

	while ($f = readdir($handle))
	{
		if (($f != '.') && ($f != '..') && mb_strtolower(mb_substr($f, mb_strrpos($f, '.') + 1, 4)) == 'tpl')
		{
			$allskinfiles[] = $f;
		}
	}
	closedir($handle);

	$allskinfiles = implode(',', $allskinfiles);

	$row = $sql->fetch();

	$raw = explode('.', $row['structure_order']);
	$sort = $raw[0];
	$way = $raw[1];

	reset($options_sort);
	reset($options_way);

	if (empty($row['structure_tpl']))
	{
		$check_tpl = "1";
	}
	elseif ($row['structure_tpl'] == 'same_as_parent')
	{
		$structure_tpl_sym = "*";
		$check_tpl = "2";
	}
	else
	{
		$structure_tpl_sym = "+";
		$check_tpl = "3";
	}

	$adminpath[] = array (cot_url('admin', "m=structure&area='.$area.'&n=options&id=".$id), htmlspecialchars($row['structure_title']));

	foreach ($structure[$area] as $i => $x)
	{
		if ($i != 'all')
		{
			$cat_path[$i] = $x['tpath'];
		}
	}
	$cat_selectbox = cot_selectbox($row['structure_tpl'], 'rstructuretplforced', array_keys($cat_path), array_values($cat_path), false);

	$t->assign(array(
		'ADMIN_STRUCTURE_UPDATE_FORM_URL' => cot_url('admin', 'm=structure&area='.$area.'&n=options&a=update&id='.$row['structure_id'].'&d='.$d.'&'.cot_xg()),
		'ADMIN_STRUCTURE_CODE' => cot_inputbox('text', 'rstructurecode', $row['structure_code'], 'size="16"'),
		'ADMIN_STRUCTURE_PATH' => cot_inputbox('text', 'rstructurepath', $row['structure_path'], 'size="16" maxlength="16"'),
		'ADMIN_STRUCTURE_TITLE' => cot_inputbox('text', 'rstructuretitle', $row['structure_title'], 'size="64" maxlength="100"'),
		'ADMIN_STRUCTURE_DESC' => cot_inputbox('text', 'rstructuredesc', $row['structure_desc'], 'size="64" maxlength="255"'),
		'ADMIN_STRUCTURE_ICON' => cot_inputbox('text', 'rstructureicon', $row['structure_icon'], 'size="64" maxlength="128"'),
		'ADMIN_STRUCTURE_LOCKED' => cot_checkbox(($structure_pages || $row['structure_locked']), 'rstructurelocked'),
		'ADMIN_STRUCTURE_SELECT' => $cat_selectbox,
		'ADMIN_STRUCTURE_TPLMODE' => cot_radiobox($check_tpl, 'rstructuretplmode', array('1'. '2', '3'), array($L['adm_tpl_empty'], $L['adm_tpl_forced'].'  '.$cat_selectbox, $L['adm_tpl_parent']), '', '<br />'),
		'ADMIN_STRUCTURE_WAY' => cot_selectbox($way, 'rstructureway', array_keys($options_way), array_values($options_way), false),
		'ADMIN_STRUCTURE_ORDER' => cot_selectbox($sort, 'rstructureorder', array_keys($options_sort), array_values($options_sort), false),
		'ADMIN_STRUCTURE_RATINGS' => cot_radiobox($row['structure_ratings'], 'rallowratings', array(1, 0), array($L['Yes'], $L['No'])),
		'ADMIN_STRUCTURE_RESYNC' => cot_url('admin', 'm=structure&area='.$area.'&n=options&a=resync&id='.$row['structure_id'].'&c='.$row['structure_code'].'&'.cot_xg()),
	));

	// Extra fields
	foreach($cot_extrafields['structure'] as $i => $row2)
	{
		$uname = strtoupper($row2['field_name']);
		$t->assign('ADMIN_STRUCTURE_'.$uname, cot_build_extrafields('rstructure'.$row2['field_name'],  $row2, $row['structure_'.$row2['field_name']]));
		$t->assign('ADMIN_STRUCTURE_'.$uname.'_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);

		// extra fields universal tags
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD', cot_build_extrafields('rstructure'.$row2['field_name'],  $row2, $row['structure_'.$row2['field_name']]));
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);
		$t->parse('MAIN.OPTIONS.EXTRAFLD');
	}

	/* === Hook === */
	foreach (cot_getextplugins('admin.structure.options.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */
	$t->parse('MAIN.OPTIONS');
}

else
{
	if ($a == 'update')
	{
		$rstructurecode = cot_import('rstructurecode', 'P', 'ARR');
		$rstructurepath = cot_import('rstructurepath', 'P', 'ARR');
		$rstructuretitle = cot_import('rstructuretitle', 'P', 'ARR');
		$rstructuredesc = cot_import('rstructuredesc', 'P', 'ARR');
		$rstructureicon = cot_import('rstructureicon', 'P', 'ARR');
		$rstructurelocked = cot_import('rstructurelocked', 'P', 'ARR');
		$rstructureorder = cot_import('rstructureorder', 'P', 'ARR');
		$rstructureway =	cot_import('rstructureway', 'P', 'ARR');
		$rstructureratings = cot_import('rstructureallowratings', 'P', 'ARR');

		foreach ($cot_extrafields['structure'] as $row)
		{
			$rstructurearray[$row['field_name']] = cot_import('rstructure'.$row['field_name'], 'P', 'ARR');
		}

		foreach ($rstructurecode as $i => $k)
		{
			$rstructure['structure_code'] = cot_import($rstructurecode[$i], 'D', 'TXT');
			$rstructure['structure_path'] = cot_import($rstructurepath[$i], 'D', 'TXT');
			$rstructure['structure_title'] = cot_import($rstructuretitle[$i], 'D', 'TXT');
			$rstructure['structure_desc'] = cot_import($rstructuredesc[$i], 'D', 'TXT');
			$rstructure['structure_icon'] = cot_import($rstructureicon[$i], 'D', 'TXT');
			$rstructure['structure_locked'] = (cot_import($rstructurelocked[$i], 'D', 'BOL')) ? 1 : 0;
			$rstructure['structure_order'] = cot_import($rstructureorder[$i], 'D', 'TXT').".".cot_import($rstructureway[$i], 'D', 'ALP');
			$rstructure['structure_ratings'] = cot_import($rstructureratings[$i], 'D', 'BOL');

			foreach ($cot_extrafields['structure'] as $row)
			{
				$rstructure[$row['field_name']] = cot_import_extrafields($rstructurearray[$row['field_name']][$i], $row, 'D');
			}

			$sqql = $db->query("SELECT structure_code FROM $db_structure WHERE structure_id='".$i."' ");
			$roww = $sqql->fetch();

			/* === Hook === */
			foreach (cot_getextplugins('admin.structure.update') as $pl)
			{
				include $pl;
			}
			/* ===== */

			if ($roww['structure_code'] != $rstructure['structure_code'])
			{
				$sql = $db->update($db_structure, array("structure_code" => $rstructure['structure_code']), "structure_code='".$db->prep($roww['structure_code'])."' AND structure_area='".$db->prep($area)."'");
				$sql = $db->update($db_auth, array("auth_option" => $rstructure['structure_code']), "auth_code='".$db->prep($area)."' AND auth_option='".$db->prep($roww['structure_code'])."'");
				$area_updatecat = 'cot_'.$area.'_updatecat';
				(function_exists($area_updatecat)) ? $area_updatecat($roww['structure_code'], $rstructure['structure_code']) : FALSE;
				cot_auth_reorder();
				cot_auth_clear('all');
			}
			$sql1 = $db->update($db_structure, $rstructure, "structure_id='".$i."'");
		}

		cot_auth_clear('all');
		if ($cache)
		{
			$cache->db->remove('structure', 'system');
			// TODO: Page sep
			$cfg['cache_'.$area] &&  $cache->page->clear($area);
		}

		cot_message('Updated');
	}
	elseif ($a == 'add')
	{
		$rstructure['structure_code'] = cot_import('rstructurecode', 'P', 'TXT');
		$rstructure['structure_path'] = cot_import('rstructurepath', 'P', 'TXT');
		$rstructure['structure_title'] = cot_import('rstructuretitle', 'P', 'TXT');
		$rstructure['structure_desc'] = cot_import('rstructuredesc', 'P', 'TXT');
		$rstructure['structure_icon'] = cot_import('rstructureicon', 'P', 'TXT');
		$rstructure['structure_locked'] = (cot_import('rstructurelocked', 'P', 'BOL')) ? 1 : 0;
		$rstructure['structure_order'] = cot_import('rstructureorder', 'P', 'ALP').".".cot_import('rstructureway', 'P', 'ALP');
		$rstructure['structure_ratings'] = cot_import('rstructureallowratings', 'P', 'BOL');
		$rstructure['structure_area'] = $area;

		foreach ($cot_extrafields['structure'] as $row)
		{
			$rstructure[$row['field_name']] = cot_import_extrafields('rstructure'.$row['field_name'], $row);
		}

		$rtplmode = cot_import('rtplmode', 'P', 'INT');
		$rstructure['structure_tpl'] = ($rtplmode == 1) ? '' : (($rtplmode == 3) ? 'same_as_parent' : cot_import('rtplforced', 'P', 'ALP'));

		/* === Hook === */
		foreach (cot_getextplugins('admin.structure.add') as $pl)
		{
			include $pl;
		}
		/* ===== */

		if (!empty($rstructure['structure_title']) && !empty($rstructure['structure_code']) && !empty($rstructure['structure_path']) && $rstructure['structure_code'] != 'all')
		{
			$sql = $db->query("SELECT structure_code FROM $db_structure WHERE structure_code='".$db->prep($rstructure['structure_code'])."' LIMIT 1");
			if ($sql->rowCount() == 0)
			{
				$colname = '';
				$colvalue = '';

				$sql = $db->insert($db_structure, $rstructure);
				$auth_permit = array(COT_GROUP_DEFAULT => 7, COT_GROUP_GUESTS => 5, COT_GROUP_MEMBERS => 7);
				$auth_lock = array(COT_GROUP_DEFAULT => 0, COT_GROUP_GUESTS => 250, COT_GROUP_MEMBERS => 128);
				cot_auth_add_item($area, $rstructure['structure_code'], $auth_permit, $auth_lock);
				$cache && $cache->db->remove('structure', 'system');
				cot_message('Added');
			}
			else
			{
				cot_message('Error');
			}
		}
		else
		{
			cot_message('Error');
		}
		($cache && $cfg['cache_'.$area]) && $cache->page->clear($area);

	}
	elseif ($a == 'delete')
	{
		cot_check_xg();

		/* === Hook === */
		foreach (cot_getextplugins('admin.structure.delete') as $pl)
		{
			include $pl;
		}
		/* ===== */

		$sql = $db->delete($db_structure, "structure_code='".$db->prep($c)."' AND structure_area='".$db->prep($area)."'");
		cot_auth_remove_item($area, $c);
		if ($cache)
		{
			$cache->db->remove('structure', 'system');
			$cfg['cache_'.$area] && $cache->page->clear($area);
		}
		cot_message('Deleted');
	}
	elseif ($a == 'resyncall')
	{
		cot_check_xg();

		$res = TRUE;
		$sql = $db->query("SELECT structure_code FROM $db_structure WHERE structure_area='".$db->prep($area)."'");
		while ($row = $sql->fetch())
		{
			$area_resync = 'cot_'.$area.'_resync';
			$res &= (function_exists($area_resync)) ? $area_resync($row['structure_code']) : FALSE;
		}
		$sql->closeCursor();
		$res ? cot_message('Resynced') : cot_message('Error');
		($cache && $cfg['cache_'.$area]) && $cache->page->clear($area);
	}

// TODO: Page sep
	$sql = $db->query("SELECT DISTINCT(page_cat), COUNT(*) FROM {$GLOBALS['db_pages']} WHERE 1 GROUP BY page_cat");

	while ($row = $sql->fetch())
	{
		$pagecount[$row['page_cat']] = $row['COUNT(*)'];
	}

	$totalitems = $db->query("SELECT COUNT(*) FROM $db_structure WHERE structure_area='".$db->prep($area)."'")->fetchColumn();
	$sql = $db->query("SELECT * FROM $db_structure WHERE structure_area='".$db->prep($area)."' ORDER BY structure_path ASC, structure_code ASC LIMIT $d, ".$cfg['maxrowsperpage']);

	$pagenav = cot_pagenav('admin', 'm=structure&area='.$area, $d, $totalitems, $cfg['maxrowsperpage'], 'd', '', $cfg['jquery'] && $cfg['turnajax']);
	$ii = 0;
	/* === Hook - Part1 : Set === */
	$extp = cot_getextplugins('admin.structure.loop');
	/* ===== */
	while ($row = $sql->fetch())
	{
		$jj++;
		$structure_id = $row['structure_id'];
		$structure_code = $row['structure_code'];

		$pathfieldlen = (mb_strpos($row['structure_path'], '.') == 0) ? 3 : 3;
		$pathfieldimg = (mb_strpos($row['structure_path'], '.') == 0) ? '' : $R['admin_icon_pathfieldimg'];
		$pathfielddep = count(explode(".", $row['structure_path']));
		$pagecount[$structure_code] = (!$pagecount[$structure_code]) ? 0 : $pagecount[$structure_code];
		$raw = explode('.', $row['structure_order']);
		$sort = $raw[0];
		$way = $raw[1];

		reset($options_sort);
		reset($options_way);

		if (empty($row['structure_tpl']))
		{
			$structure_tpl_sym = '-';
		}
		elseif ($row['structure_tpl'] == 'same_as_parent')
		{
			$structure_tpl_sym = '*';
		}
		else
		{
			$structure_tpl_sym = '+';
		}

		$dozvil = ($pagecount[$structure_code] > 0) ? false : true;

		$t->assign(array(
			'ADMIN_STRUCTURE_UPDATE_DEL_URL' => cot_url('admin', 'm=structure&area='.$area.'&a=delete&id='.$structure_id.'&c='.$row['structure_code'].'&d='.$d.'&'.cot_xg()),
			'ADMIN_STRUCTURE_ID' => $structure_id,
			'ADMIN_STRUCTURE_CODE' => cot_inputbox('text', 'rstructurecode['.$structure_id.']', $structure_code, 'size="8" maxlength="255"'),
			'ADMIN_STRUCTURE_PATHFIELDIMG' => $pathfieldimg,
			'ADMIN_STRUCTURE_PATH' => cot_inputbox('text', 'rstructurepath['.$structure_id.']', $row['structure_path'], 'size="'.$pathfieldlen.'" maxlength="24"'),
			'ADMIN_STRUCTURE_TPL_SYM' => $structure_tpl_sym,
			'ADMIN_STRUCTURE_TITLE' => cot_inputbox('text', 'rstructuretitle['.$structure_id.']', $row['structure_title'], 'size="18" maxlength="100"'),
			'ADMIN_STRUCTURE_DESC' => cot_inputbox('text', 'rstructuredesc['.$structure_id.']', $row['structure_desc'], 'size="64" maxlength="255"'),
			'ADMIN_STRUCTURE_ICON' => cot_inputbox('text', 'rstructureicon['.$structure_id.']', $row['structure_icon'], 'size="64" maxlength="128"'),
			'ADMIN_STRUCTURE_WAY' => cot_selectbox($way, 'rstructureway['.$structure_id.']', array_keys($options_way), array_values($options_way), false, 'style="width:85px;"'),
			'ADMIN_STRUCTURE_ORDER' => cot_selectbox($sort, 'rstructureorder['.$structure_id.']', array_keys($options_sort), array_values($options_sort), false, 'style="width:85px;"'),
			'ADMIN_STRUCTURE_LOCKED' => cot_checkbox($row['structure_locked'], 'rstructurelocked['.$structure_id.']'),
			'ADMIN_STRUCTURE_PAGECOUNT' => $pagecount[$structure_code],
			/*TODO*/		'ADMIN_STRUCTURE_JUMPTO_URL' => cot_url($area, 'c='.$structure_code),
			'ADMIN_STRUCTURE_RIGHTS_URL' => cot_url('admin', 'm=rightsbyitem&ic='.$area.'&io='.$structure_code),
			'ADMIN_STRUCTURE_OPTIONS_URL' => cot_url('admin', 'm=structure&area='.$area.'&n=options&id='.$structure_id.'&'.cot_xg()),
			'ADMIN_STRUCTURE_ODDEVEN' => cot_build_oddeven($ii)
		));

		foreach($cot_extrafields['structure'] as $i => $row2)
		{
			$t->assign('ADMIN_STRUCTURE_'.strtoupper($row2['field_name']), cot_build_extrafields('rstructure'.$row2['field_name'].'['.$structure_id.']', $row2, $row['structure_'.$row2['field_name']]));
		}


		/* === Hook - Part2 : Include === */
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */

		$t->parse('MAIN.DEFULT.ROW');

		$ii++;
	}

	reset($options_sort);
	reset($options_way);

	$t->assign(array(
		'ADMIN_STRUCTURE_UPDATE_FORM_URL' => cot_url('admin', 'm=structure&area='.$area.'&a=update&d='.$d),
		'ADMIN_STRUCTURE_PAGINATION_PREV' => $pagenav['prev'],
		'ADMIN_STRUCTURE_PAGNAV' => $pagenav['main'],
		'ADMIN_STRUCTURE_PAGINATION_NEXT' => $pagenav['next'],
		'ADMIN_STRUCTURE_TOTALITEMS' => $totalitems,
		'ADMIN_STRUCTURE_COUNTER_ROW' => $ii,
		'ADMIN_PAGE_STRUCTURE_RESYNCALL' => cot_url('admin', 'm=structure&area='.$area.'&a=resyncall&'.cot_xg().'&d='.$d),
		'ADMIN_STRUCTURE_URL_FORM_ADD' => cot_url('admin', 'm=structure&area='.$area.'&a=add'),
		'ADMIN_STRUCTURE_CODE' => cot_inputbox('text', 'rstructurecode', '', 'size="16"'),
		'ADMIN_STRUCTURE_PATH' => cot_inputbox('text', 'rstructurepath', '', 'size="16" maxlength="16"'),
		'ADMIN_STRUCTURE_TITLE' => cot_inputbox('text', 'rstructuretitle', '', 'size="64" maxlength="100"'),
		'ADMIN_STRUCTURE_DESC' => cot_inputbox('text', 'rstructuredesc', '', 'size="64" maxlength="255"'),
		'ADMIN_STRUCTURE_ICON' => cot_inputbox('text', 'rstructureicon', '', 'size="64" maxlength="128"'),
		'ADMIN_STRUCTURE_LOCKED' => cot_checkbox(0, 'rstructurelocked'),
		'ADMIN_STRUCTURE_WAY' => cot_selectbox('asc', 'rstructureway', array_keys($options_way), array_values($options_way), false),
		'ADMIN_STRUCTURE_ORDER' => cot_selectbox('title', 'rstructureorder', array_keys($options_sort), array_values($options_sort), false),
		'ADMIN_STRUCTURE_RATINGS' => cot_radiobox(1, 'rstructureallowratings', array(1, 0), array($L['Yes'], $L['No']))
	));

	// Extra fields
	foreach($cot_extrafields['structure'] as $i => $row2)
	{
		$uname = strtoupper($row2['field_name']);
		$t->assign('ADMIN_STRUCTURE_'.$uname, cot_build_extrafields('rstructure'.$row2['field_name'],  $row2, ''));
		$t->assign('ADMIN_STRUCTURE_'.$uname.'_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);

		// extra fields universal tags
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD', cot_build_extrafields('rstructure'.$row2['field_name'],  $row2, ''));
		$t->assign('ADMIN_STRUCTURE_EXTRAFLD_TITLE', isset($L['structure_'.$row2['field_name'].'_title']) ?  $L['structure_'.$row2['field_name'].'_title'] : $row2['field_description']);
		$t->parse('MAIN.DEFULT.EXTRAFLD');
	}
	$t->parse('MAIN.DEFULT');
}

$t->assign(array(
	'ADMIN_STRUCTURE_URL_CONFIG' => cot_url('admin', 'm=config&n=edit&o=core&p=structure'),
	'ADMIN_STRUCTURE_URL_EXTRAFIELDS' => cot_url('admin', 'm=extrafields&n=structure')
));

cot_display_messages($t);

/* === Hook  === */
foreach (cot_getextplugins('admin.structure.tags') as $pl)
{
	include $pl;
}
/* ===== */

$t->parse('MAIN');
if (COT_AJAX)
{
	$t->out('MAIN');
}
else
{
	$adminmain = $t->text('MAIN');
}

?>