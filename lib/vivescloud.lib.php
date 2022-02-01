<?php
/* Copyright (C) 2020 SuperAdmin
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
 * \file    vivescloud/lib/vivescloud.lib.php
 * \ingroup vivescloud
 * \brief   Library files with common functions for Vivescloud
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function vivescloudAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("vivescloud@vivescloud");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/vivescloud/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
    $head[$h][0] = dol_buildpath("/vivescloud/admin/myobject_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFields");
    $head[$h][2] = 'myobject_extrafields';
    $h++;
     */

	$head[$h][0] = dol_buildpath("/vivescloud/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//    'entity:+tabname:Title:@vivescloud:/vivescloud/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//    'entity:-tabname:Title:@vivescloud:/vivescloud/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'vivescloud');

	return $head;
}
/**
 * @var resql Response from DB Query
 */

function getData(&$resql, $idsup = null, $currency = null)
{
	global  $db;

	$obj = $db->fetch_object($resql);

	if ($obj) {

		$supplier = [];
		$supplier = getProductSupplier($obj->rowid, $idsup);

		$prices_currency = [];
		$product = new Product($db);
		$product->fetch($obj->rowid);

		if ($product->status == 1) {

			$sql = "SELECT date_price FROM " . MAIN_DB_PREFIX . "product_price where fk_product = " . $obj->rowid . " ORDER BY rowid DESC";
			$resqlprice = $db->query($sql);
			$num_row = $db->num_rows($resqlprice);
			if ($num_row > 0) {
				$objtmp = $db->fetch_object($resqlprice);
				$date_price = $objtmp->date_price;
			}

			if (isset($currency)) {
				foreach ($currency as $current) {

					$result_array = [
						$current['currency'] => round($product->price * price2num($current['rate']), 2),
					];
					$prices_currency[] = $result_array;
				}
			}

			// You can use here results
			$cost_price = $product->cost_price ? round($product->cost_price, 2) : 0;
			$price = $product->price ? round($product->price, 2) : 0;

			$product_array = [

				'id' => $product->id,
				'ref' => "<a href='" . DOL_MAIN_URL_ROOT . "/product/card.php?id=" . $product->id . "'>" . $product->ref . "</a>",
				'label' => $product->label,
				'stock_reel' => $product->stock_reel,
				'price' => $price,
				'currency' => $prices_currency,
				'date_price' => $date_price ? date('d-m-Y', strtotime($date_price)) : null

			];

			if ($cost_price > 0) {
				$product_array['cost_price'] = $cost_price;
			}

			if (count($supplier) > 0) {
				$product_array['supplier'] = $supplier;
			}

			return $product_array;
		}
	}
}

function getCurrency()
{
	global $db, $conf;

	$currency = new MultiCurrency($db);

	$sql = "SELECT rowid, code from " . MAIN_DB_PREFIX . "multicurrency where entity = " . $conf->entity;

	$resql = $db->query($sql);
	$num = $db->num_rows($resql);
	$result = [];

	if ($num > 0) {
		$i = 0;

		while ($i < $num) {
			$obj = $db->fetch_object($resql);
			$currency->fetch($obj->rowid);
			$result_array = [
				'currency' => $obj->code,
				'rate' => $currency->rates[0]->rate,
				'date' => $currency->rates[0]->date_sync,
			];

			$result[] = $result_array;
			$i++;
		}

		return $result;
	}
}

function calcProfit($cost_price, $price)
{

	if ($cost_price == 0 && $price == 0) {

		return 0;
	} else if ($cost_price == 0) {

		return 0;
	} else {

		return round((($price / $cost_price) * 100) - 100, 2);
	}
}

function returnCurrency($arr, $curr)
{

	$num = count($arr);

	for ($i = 0; $i < $num; $i++) {
		if ($arr[$i]['currency'] == $curr) {
			return $arr[$i]['rate'];
		}
	}
}
/**
 * @var refproduct (varchar) Ref product to update price
 * @var cost_price (double) Cost price product
 * @var price (double) Price product
 */

function updatePrice($id, $idsup, $cost_price, $price, $margin, $response = true)
{
	global $db, $user, $conf, $currencypost;

	$currency = getCurrency();
	$product = new Product($db);
	$product->fetch($id);
	$product_fourn = new ProductFournisseur($db);
	$supplier = new Fournisseur($db);
	$supplier->fetch($idsup);
	$category = new Categorie($db);
	$categorie = $category->containing($id, 'product');
	// var_dump($categorie[0]->id);exit;
	$ref_fourn = null;
	$tva_tx = null;
	$delivery_time_days = null;
	$supplier_reputation = null;
	$multicurrency_code = null;
	$multicurrency_tx = null;
	$multicurrency_price = null;
	$supplier_description = null;
	$newprice = price2num($cost_price);
	$idprice = null;
	$pricecategory = new DinamicCategorie($db);
	$pricecategory->fetch(null, $categorie[0]->id);
	if (!empty($margin) || !is_null($margin) && $price <= 0) {

		if (floatval($margin) < floatval($pricecategory->value)) {
			return ["err", "El margen no puede ser inferior a " . $pricecategory->value];
			exit;
		}
	}
	if (!is_numeric($cost_price)) {
		echo "error";
		exit;
	}

	if (!empty($id)) {

		$product_fourn_list = $product_fourn->list_product_fournisseur_price($id);

		foreach ($product_fourn_list as $productfourn) {

			if (!empty($idsup)) {
				if ($productfourn->fourn_id == $idsup) {

					$ref_fourn = $productfourn->fourn_ref;
					$tva_tx = $productfourn->fourn_tva_tx;
					$delivery_time_days = $productfourn->delivery_time_days;
					$supplier_reputation = $productfourn->supplier_reputation;
					$multicurrency_code = $productfourn->fourn_multicurrency_code;
					$idprice = $productfourn->product_fourn_price_id;

					foreach ($currency as $rate) {
						if ($rate['currency'] == $multicurrency_code) {
							$multicurrency_tx = $rate['rate'];
						}
					}

					if ($multicurrency_code != $conf->currency) {


						$multicurrency_price = price2num($cost_price, 'MU');
						$sup_newprice = $multicurrency_price / price2num($multicurrency_tx);

					} else {

						$sup_newprice = price2num($cost_price, 'MU');

						$multicurrency_price = price2num($cost_price, 'MU');
					}

					$supplier_description = $product_fourn->description;
				}
			}
		}

		$db->begin();
		if ($conf->multicurrency->enabled) {
			// echo $product_fourn->product_fourn_price_id;exit;
			$product_fourn->fetch_product_fournisseur_price($idprice);
			$ret = $product_fourn->update_buyprice(
				1,
				$sup_newprice,
				$user,
				'HT',
				$supplier,
				0,
				$ref_fourn,
				$tva_tx,
				0,
				0,
				0,
				0,
				$delivery_time_days,
				$supplier_reputation,
				array(),
				'',
				$multicurrency_price,
				"HT",
				$multicurrency_tx,
				$multicurrency_code,
				$supplier_description,
				null,
				null
			);
		}
		//else {
		// $ret = $product_fourn->update_buyprice(
		//     1,
		//     $newprice,
		//     $user,
		//     $_POST["price_base_type"],
		//     $supplier,
		//     $_POST["oselDispo"],
		//     $ref_fourn,
		//     $tva_tx,
		//     $_POST["charges"],
		//     $remise_percent,
		//     0,
		//     $npr,
		//     $delivery_time_days,
		//     $supplier_reputation,
		//     array(),
		//     '',
		//     0,
		//     'HT',
		//     1,
		//     '',
		//     $supplier_description,
		//     null,
		//     null);
		// }
		if ($ret > 0) {
			$db->commit();
		}
		
		if (!empty($margin)) {
			if ($product->array_options['options_currency'] != $conf->currency) {

				foreach ($currency as $rate) {

					if ($rate['currency'] == $product->array_options['options_currency'] && $currencypost == $product->array_options['options_currency']) {

						$newprice = (price2num($cost_price) / (1 - (price2num($margin) / 100))) / price2num($rate['rate']);
						$newprice = price2num($newprice, 'MU');
						goto save;
					}
				}
			} else {
				$newprice =  price2num($cost_price) / (1 - (price2num($margin)) / 100);
				$newprice = price2num($price, 'MU');
			}
		}
	
		if (!empty($price)) {
			$newprice = price2num($price, 'MU');
		}
		save:
		if ($product->array_options['options_currency'] != $conf->currency) {
			foreach ($currency as $rate) {

				if ($rate['currency'] == $product->array_options['options_currency'] && $currencypost == $product->array_options['options_currency']) {
					$product->array_options['options_price'] = $newprice *	price2num($rate['rate']);
				}
			}
		}

		$newprice_min = null;
		$db->begin();
		$result = $product->update($id, $user);
		if ($result > 0) {
			$db->commit();
			$product->fetch($id);
		}

		if ($pricecategory->type == 'fixed') {
			$newprice_min = $sup_newprice + round(floatval($pricecategory->value), 2);
			$newprice_min = price2num($newprice_min);
		}
		if ($pricecategory->type == 'percent') {

			$newprice_min = $sup_newprice / (1 - (floatval(round(floatval($pricecategory->value), 2))  / 100));
			$newprice_min = price2num($newprice_min);
		}

		if (!$pricecategory->type) {

			$newprice_min = $sup_newprice / (1 - (20 / 100));
			$newprice_min = price2num($newprice_min);
		}

		$res = $product->updatePrice($newprice, 'HT', $user, $product->tva_tx ? $product->tva_tx : '16.00', $newprice_min ? $newprice_min : 0, 0, 0, 0, 0, array('0' => 0));

		if ($res < 0) {

			//error
			$sql = "INSERT INTO " . MAIN_DB_PREFIX . "temp_error (type,value) VALUES ('err_product','" . $product->id . "')";
			$db->query($sql);
			// .error
			setEventMessages($product->ref, $product->errors, 'errors');
			$db->rollback();
			return ["err", "El margen no puede ser inferior a " . $pricecategory->value];
		}

		$db->commit();

		$product->update($product->id, $user);

		if ($response == true) {
			$sql = "SELECT rowid from " . MAIN_DB_PREFIX . "product where rowid =" . $product->id;
			$resql = $db->query($sql);
			// var_dump(getData($resql,null,$currency));
			echo json_encode(getData($resql, $idsup, $currency));
			exit;
		}
	}
}

//, $sortfield, $sortorder, $limit, $offset
function getProductSupplier($idprod, $idsup = null)
{
	global $db, $conf;

	$product_fourn = new ProductFournisseur($db);
	$product = new Product($db);
	$product->fetch($idprod);
	$currencies = getCurrency();

	$supplier = [];

	if (!empty($idprod)) {
		$product_fourn_list = $product_fourn->list_product_fournisseur_price($idprod, 'supplier_reputation');

		foreach ($product_fourn_list as $productfourn) {

			if (!empty($idsup)) {
				if ($productfourn->fourn_id == $idsup) {

					$supplier_arr = [
						'supid' => $productfourn->fourn_id,
						'name' => $productfourn->fourn_name,
						'modification_date' => dol_print_date($productfourn->fourn_date_modification, "%d/%m/%Y"),
						'currency' => $productfourn->fourn_multicurrency_code,
					];

					if ($productfourn->fourn_multicurrency_code != $conf->currency) {
						$supplier_arr['price'] = round($productfourn->fourn_multicurrency_price, 2);

						$rate = returnCurrency($currencies, $productfourn->fourn_multicurrency_code);

						$price_profit = floatval($product->price) * floatval($rate);
						$supplier_arr['profit'] = calcProfit($supplier_arr['price'], $price_profit);
					} else {
						$supplier_arr['price'] = round($productfourn->fourn_unitprice, 2);
						$supplier_arr['profit'] = calcProfit($supplier_arr['price'], price2num($product->price));
					}

					array_push($supplier, $supplier_arr);

					goto response;
				}
			} else {

				$supplier_arr = [
					'supid' => $productfourn->fourn_id,
					'name' => $productfourn->fourn_name,
					'modification_date' => dol_print_date($productfourn->fourn_date_modification, "%d/%m/%Y"),
					'currency' => $productfourn->fourn_multicurrency_code,
					// 'price' => round($productfourn->fourn_unitprice, 2),
				];
				if ($productfourn->fourn_multicurrency_code != $conf->currency) {
					$supplier_arr['price'] = round($productfourn->fourn_multicurrency_price, 2);
					$rate = returnCurrency($currencies, $productfourn->fourn_multicurrency_code);
					// echo "el precio es ".$product->price;
					// $price_profit = 0;
					$price_profit = $product->price ? round(floatval($product->price), 2) * floatval($rate) : 0;

					$supplier_arr['profit'] = calcProfit($supplier_arr['price'], $price_profit);
				} else {
					$supplier_arr['price'] = round($productfourn->fourn_unitprice, 2);
					$supplier_arr['profit'] = calcProfit($supplier_arr['price'], price2num($product->price));
				}

				array_push($supplier, $supplier_arr);
			}
		}

		response:
		return $supplier;
	}
}

function createSQL($ref, $category = 0, $supplier_id = 0)
{

	$jointable = "";
	$wheretable = "";
	$sql = "SELECT p.rowid from " . MAIN_DB_PREFIX . "product p";
	if ($category > 0) {
		$jointable .= " LEFT JOIN " . MAIN_DB_PREFIX . "categorie_product cp on p.rowid = cp.fk_product ";
		$jointable .= " LEFT JOIN " . MAIN_DB_PREFIX . "categorie c on cp.fk_categorie = c.rowid ";
		$wheretable .= " AND c.rowid = " . $category;
	} else {
		$category = null;
	}

	if (!empty($ref)) {
		$wheretable .= "AND ref = '" . $ref . "' OR (ref LIKE '%" . $ref . "%' OR label LIKE '%" . $ref . "%') AND tosell IN(1)";
	}

	if ($supplier_id > 0) {
		$jointable .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price pfp on p.rowid = pfp.fk_product ";
		$jointable .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe s on s.rowid = pfp.fk_soc ";
		$wheretable .= " AND s.rowid = " . $supplier_id;
	} else {

		$supplier_id = null;
	}

	$sql .= $jointable . " where tosell IN(1) " . $wheretable;

	return $sql;
}

global $exchange;
$exchange = getCurrency();

foreach ($exchange as $currency) {
	if ($currency['currency'] != $conf->currency) {
		$header = "<h2 style=\"color:red\"> 1 USD = " . $currency['rate'] . "MXN - <span style=\"color:black;font-size:small;\"> " . $currency['date'] . "</span></h2>";
	}
}
