<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
if ($extConfiguration['DEVMODE'] == '1') {
	// Save hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/tx_saveDce.php:tx_saveDce';
}

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php']
	= t3lib_extMgm::extPath($_EXTKEY).'Classes/Hooks/class.ux_t3lib_tcemain.php';

if (TYPO3_MODE === 'BE') {
	require_once(t3lib_extMgm::extPath($_EXTKEY).'Classes/UserFunction/class.tx_dce_codemirrorField.php');
	require_once(t3lib_extMgm::extPath($_EXTKEY) . 'Classes/UserFunction/class.tx_dce_dceFieldCustomLabel.php');
}
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_dce_formevals_lowerCamelCase'] = 'EXT:dce/Classes/UserFunction/class.tx_dce_formevals_lowerCamelCase.php';

$pathDceLocalconf = PATH_typo3conf . 'temp_CACHED_dce_ext_localconf.php';
if (!file_exists($pathDceLocalconf)) {
	/** @var $dceCache Tx_Dce_Cache */
	$dceCache = t3lib_div::makeInstance('Tx_Dce_Cache');
	$dceCache->createLocalconf($pathDceLocalconf);
}
require_once($pathDceLocalconf);
?>