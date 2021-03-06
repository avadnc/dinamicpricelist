<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry    <jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       dinamicpricelist/dinamicpricelistindex.php
 *    \ingroup    dinamicpricelist
 *    \brief      Home page of dinamicpricelist top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}

// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}

if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}

// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}

if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}

if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}

if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/multicurrency/class/multicurrency.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
dol_include_once('/dinamicpricelist/class/dinamiccategorie.class.php');
dol_include_once('/dinamicpricelist/lib/Smartie.php');
include(DOL_DOCUMENT_ROOT . '/custom/dinamicpricelist/lib/vivescloud.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("dinamicpricelist@dinamicpricelist", "companies", "commercial", "customers", "suppliers"));
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$ref = GETPOST('ref', 'alpha');
$category = GETPOST('category', 'alpha');
$ajax = GETPOST('ajax', 'int');
$cost_price = GETPOST('cost_price');
$price = GETPOST('price');
$currencypost = GETPOST('currency');
$supplier_id = GETPOST('supid', 'int');
$margin = GETPOST('margin');

// Security check

if (!$user->rights->dinamicpricelist->updateprice->write) {
	accessforbidden();
}

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}
//Dolibarr 14
if ($supplier_id < 0) {
	$supplier_id = NULL;
}
if (empty($currencypost)) {
	$currencypost = $conf->currency;
}

$max = 5;
$now = dol_now();

// Objects

$smarty = new Smartie();

//Internal Functions

/*
 * Actions
 */

if ($action == "getproducts") {

	$productos = [];
	$sql = createSQL($ref, $category, $supplier_id);
	$resql = $db->query($sql);
	$num = $db->num_rows($resql);

	if ($num > 0) {
		$i = 0;
		while ($i < $num) {
			array_push($productos, getData($resql, $supplier_id, $exchange));
			$i++;
		}
	}

	echo json_encode($productos);
	exit;
}

if ($action == "getpricesupplier") {

	echo json_encode(getProductSupplier($id, $supplier_id));
	exit;
}

if ($action == "update") {

	echo json_encode(updatePrice($id, $supplier_id, $cost_price, $price, $margin));
	exit;
}

/*
 * View
 */

/**
 * Smarty assignation
 */
if ($conf->multicurrency->enabled) {
	$smarty->assign('currencies', $exchange);
}
$form = new Form($db);
$formfile = new FormFile($db);
$smarty->assign('supplier', $form->select_company('', 'supid', 's.fournisseur in(1)', 'SelectThirdParty', 0, 0, null, 0, "flat searchstring minwidth200", 1));
$smarty->assign('category', $form->select_all_categories(Categorie::TYPE_PRODUCT, 'auto', 'category', 0, 0, 0, 0, 'flat searchstring minwidth200', 1));
$smarty->assign('newToken', newToken());
$smarty->assign('localcurrency', $conf->currency);
llxHeader("", $langs->trans("DinamicpricelistUpdate"));

print load_fiche_titre($langs->trans("DinamicpricelistUpdate"), $header, 'updateprice.png@dinamicpricelist');

print '<div class="fichecenter">';

$smarty->display('updatepricelist.tpl');

print '</div>';

dol_fiche_end();
llxFooter();
$db->close();
