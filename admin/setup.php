<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2021 SuperAdmin <gerencia@suministrosenmetrologia.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    dinamicpricelist/admin/setup.php
 * \ingroup dinamicpricelist
 * \brief   Dinamicpricelist setup page.
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
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}

if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}

if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/dinamicpricelist.lib.php';
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
dol_include_once('/dinamicpricelist/class/dinamiccategorie.class.php');
dol_include_once('/dinamicpricelist/lib/Smartie.php');
dol_include_once('/dinamicpricelist/lib/vivescloud.lib.php');

//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "dinamicpricelist@dinamicpricelist"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$value = GETPOST('value', 'alpha');


/*
 * Actions
 */

if ($action == 'update') {
	$num = count($_POST['id']);
	for ($i = 0; $i < $num; $i++) {
		if ($_POST['type'][$i] != "") {
			$pricecategorie = new DinamicCategorie($db);

			$pricecategorie->fk_categorie = intval($_POST['id'][$i]);
			$pricecategorie->type = $_POST['type'][$i];
			$pricecategorie->value = floatval($_POST['val'][$i]);
			$pricecategorie->entity = $conf->entity;
			$db->begin();
			$result = $pricecategorie->create($user);

			if ($result > 0) {
				$db->commit();
				$smarty->assign('msg', ["OK", "Registro Guardado Exitosamente"]);
				$category = new Categorie($db);

				$category->fetch(intval($_POST['id'][$i]));
				$pricecategorie->fetch(null, intval($_POST['id'][$i]));
				$data[$i] = [
					'id' => $category->id,
					'name' => $category->label,
					'type' => $pricecategorie->type,
					'value' => $pricecategorie->value,
					'date' => dol_print_date($pricecategorie->tms, "%d %m %Y"),
				];
				unset($category);
				unset($pricecategorie);
			} else {

				$db->rollback();
				$smarty->assign('msg', ["ERR", "Error en el Registro"]);
			}
		}
	}
}

if ($action == 'updateall') {

	$sql = "TRUNCATE TABLE ".MAIN_DB_PREFIX."temp_error";
	$db->query($sql);

	$pricecategorie = new DinamicCategorie($db);
	$pricecategorie->fetchAll();

	$products = [];
	$temp = [];
	foreach ($pricecategorie->lines as $line) {

		$sql = "SELECT fk_product rowid FROM " . MAIN_DB_PREFIX . "categorie_product WHERE fk_categorie = " . $line['fk_categorie'] ;
		$resql = $db->query($sql);
		$num = $db->num_rows($resql);
		if ($num > 0) {
			$i = 0;
			while ($i < $num) {
				array_push($products, getData($resql, null, $exchange));
				$i++;
			}
		}
	}
	
	foreach ($products as $product) {
		if (!empty($product['supplier'])) {
			$category = new Categorie($db);
			$categorie = $category->containing($product['id'], 'product');
			$pricecategorie->fetch(null, $categorie[0]->id);

			if ($product['price'] > 0) {

				if ($product['supplier'][0]['currency'] != $conf->currency) {

					if ($product['supplier'][0]['currency'] != NULL) {

						foreach ($exchange as $currency) {

							if ($currency['currency'] == $product['supplier'][0]['currency']) {
								$cost_price = price2num($product['supplier'][0]['price']) / price2num($currency['rate']);
							}
						}

						if (price2num($product['price']) > $cost_price) {

							updatePrice($product['id'], $product['supplier'][0]['supid'], $cost_price, price2num($product['price']), null, false);
						} else {

							//error
							$sql = "INSERT INTO " . MAIN_DB_PREFIX . "temp_error (type,value) VALUES ('cost_price_over_price','" . $product['id'] . "')";
							$db->query($sql);
							// .error
						}
					} else {

						//error
						$sql = "INSERT INTO " . MAIN_DB_PREFIX . "temp_error (type,value) VALUES ('sup_product_null_curr','" . $product['id'] . "')";
						$db->query($sql);
						// .error
					}
				} else {

					if (price2num($product['price']) > price2num($product['supplier'][0]['price'])) {

						updatePrice($product['id'], $product['supplier'][0]['supid'], price2num($product['supplier'][0]['price']), price2num($product['price']), null, false);
					} else {
						//error
						$sql = "INSERT INTO " . MAIN_DB_PREFIX . "temp_error (type,value) VALUES ('sup_product_null_curr','" . $product['id'] . "')";
						$db->query($sql);
						// .error

					}
				}
			} else {

				//error
				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "temp_error (type,value) VALUES ('product_price_0','" . $product['id'] . "')";
				$db->query($sql);
				// .error
			}
		}
	}

	echo '<script>window.location.href ="' . DOL_URL_ROOT . '/custom/dinamicpricelist/admin/setup.php"</script>';

	exit;
}



/**
 * Data
 */

$smarty = new Smartie();

$data = [];

$sql = "SELECT rowid, label from " . MAIN_DB_PREFIX . "categorie where type=0";
$resql = $db->query($sql);
$num = $db->num_rows($resql);

if ($num > 0) {
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($resql);
		$category = new Categorie($db);
		$pricecategorie = new DinamicCategorie($db);
		$category->fetch($obj->rowid);
		$pricecategorie->fetch(null, $obj->rowid);
		$data[$i] = [
			'id' => $category->id,
			'name' => $category->label,
			'type' => $pricecategorie->type,
			'value' => $pricecategorie->value,
			'date' => dol_print_date($pricecategorie->tms, "%d %m %Y"),
		];
		unset($category);
		unset($pricecategorie);
		$i++;
	}
}
$smarty->assign('data', $data);
$error = 0;
$setupnotempty = 0;



/*
 * View
 */

$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "DinamicpricelistSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_dinamicpricelist@dinamicpricelist');

// Configuration header
$head = dinamicpricelistAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', '', -1, "dinamicpricelist@dinamicpricelist");
$smarty->assign('newToken', newToken());
$self = DOL_DOCUMENT_ROOT . $_SERVER['PHP_SELF'];
$smarty->assign('sefl', $self);
$smarty->display('setup.tpl');



$sql = "SELECT type, value from " . MAIN_DB_PREFIX . "temp_error where type LIKE '%product%' GROUP BY value";
$resql = $db->query($sql);
$num = $db->num_rows($resql);

if ($num > 0) {
	//Tabla para los fallidos
	print '<div class="div-table-responsive-no-min">';
	print '<div><h2>'.$langs->trans('product_with_errors').' </h2></div>';
	print '<table class="noborder">';
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($resql);
		$product = new Product($db);
		$product->fetch($obj->value);
		// echo '<pre>';var_dump($product);
		print '<tr>';
		print '<td>' . $product->getNomUrl(1) . '</td>';
		print '<td>' . $langs->trans($obj->type) . '</td>';
		print '</tr>';
		$i++;
	}
	print '</table></div>';
}

// Page end
dol_fiche_end();

llxFooter();
$db->close();
