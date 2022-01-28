<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2021 SuperAdmin <gerencia@suministrosenmetrologia.com>
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

use Luracast\Restler\RestException;

require_once DOL_DOCUMENT_ROOT . '/multicurrency/class/multicurrency.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
dol_include_once('/dinamicpricelist/lib/vivescloud.lib.php');

/**
 * \file    dinamicpricelist/class/api_dinamicpricelist.class.php
 * \ingroup dinamicpricelist
 * \brief   File for API management of dinamiccategorie.
 */

/**
 * API class for dinamicpricelist dinamiccategorie
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class DinamicpricelistApi extends DolibarrApi
{
    /**
     * @var DinamicCategorie $dinamiccategorie {@type DinamicCategorie}
     */
    public $dinamiccategorie;

    /**
     * Constructor
     *
     * @url     GET /
     *
     */
    public function __construct()
    {
        global $db, $conf;
        $this->db = $db;
        // $this->dinamiccategorie = new DinamicCategorie($this->db);
    }

    /**
     * Get properties of a dinamiccategorie object
     *
     * Return an array with dinamiccategorie informations
     *
     * @param     int     $id ID of dinamiccategorie
     * @return     array|mixed data without useless information
     *
     * @url    GET dinamiccategories/{id}
     *
     * @throws     RestException
     */
    // public function get($id)
    // {
    //     if (!DolibarrApiAccess::$user->rights->dinamicpricelist->read) {
    //         throw new RestException(401);
    //     }

    //     $result = $this->dinamiccategorie->fetch($id);
    //     if (!$result) {
    //         throw new RestException(404, 'DinamicCategorie not found');
    //     }

    //     if (!DolibarrApi::_checkAccessToResource('dinamiccategorie', $this->dinamiccategorie->id, 'dinamicpricelist_dinamiccategorie')) {
    //         throw new RestException(401, 'Access to instance id='.$this->dinamiccategorie->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
    //     }

    //     return $this->_cleanObjectDatas($this->dinamiccategorie);
    // }

    /**
     * List dinamiccategories
     *
     * Get a list of dinamiccategories
     *
     * @param string           $ref            		Product ref
     * @param string           $category            Product category
     * @param int              $supplier_id         Supplier ID
     * @return  array                               Array of order objects
     *
     * @throws RestException
     *
     * @url    GET /dinamiccategories/
     */
    public function index($ref, $category = null, $supplier_id = null)
    {
        $productos = [];
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

        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        

        if ($num > 0) {
            $i = 0;
            while ($i < $num) {
                $productos[] = getData($resql, $supplier_id);
                $i++;
            }

        }

      return $productos;

    }
    // public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '')
    // {
    //     global $db, $conf;

    //     $obj_ret = array();
    //     $tmpobject = new DinamicCategorie($db);

    //     if (!DolibarrApiAccess::$user->rights->dinamicpricelist->dinamiccategorie->read) {
    //         throw new RestException(401);
    //     }

    //     $socid = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : '';

    //     $restrictonsocid = 0; // Set to 1 if there is a field socid in table of object

    //     // If the internal user must only see his customers, force searching by him
    //     $search_sale = 0;
    //     if ($restrictonsocid && !DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) $search_sale = DolibarrApiAccess::$user->id;

    //     $sql = "SELECT t.rowid";
    //     if ($restrictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) $sql .= ", sc.fk_soc, sc.fk_user"; // We need these fields in order to filter by sale (including the case where the user can only see his prospects)
    //     $sql .= " FROM ".MAIN_DB_PREFIX.$tmpobject->table_element." as t";

    //     if ($restrictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc"; // We need this table joined to the select in order to filter by sale
    //     $sql .= " WHERE 1 = 1";

    //     // Example of use $mode
    //     //if ($mode == 1) $sql.= " AND s.client IN (1, 3)";
    //     //if ($mode == 2) $sql.= " AND s.client IN (2, 3)";

    //     if ($tmpobject->ismultientitymanaged) $sql .= ' AND t.entity IN ('.getEntity($tmpobject->element).')';
    //     if ($restrictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) $sql .= " AND t.fk_soc = sc.fk_soc";
    //     if ($restrictonsocid && $socid) $sql .= " AND t.fk_soc = ".$socid;
    //     if ($restrictonsocid && $search_sale > 0) $sql .= " AND t.rowid = sc.fk_soc"; // Join for the needed table to filter by sale
    //     // Insert sale filter
    //     if ($restrictonsocid && $search_sale > 0) {
    //         $sql .= " AND sc.fk_user = ".$search_sale;
    //     }
    //     if ($sqlfilters)
    //     {
    //         if (!DolibarrApi::_checkFilters($sqlfilters)) {
    //             throw new RestException(503, 'Error when validating parameter sqlfilters '.$sqlfilters);
    //         }
    //         $regexstring = '\(([^:\'\(\)]+:[^:\'\(\)]+:[^:\(\)]+)\)';
    //         $sql .= " AND (".preg_replace_callback('/'.$regexstring.'/', 'DolibarrApi::_forge_criteria_callback', $sqlfilters).")";
    //     }

    //     $sql .= $db->order($sortfield, $sortorder);
    //     if ($limit) {
    //         if ($page < 0) {
    //             $page = 0;
    //         }
    //         $offset = $limit * $page;

    //         $sql .= $db->plimit($limit + 1, $offset);
    //     }

    //     $result = $db->query($sql);
    //     if ($result)
    //     {
    //         $num = $db->num_rows($result);
    //         while ($i < $num)
    //         {
    //             $obj = $db->fetch_object($result);
    //             $tmp_object = new DinamicCategorie($db);
    //             if ($tmp_object->fetch($obj->rowid)) {
    //                 $obj_ret[] = $this->_cleanObjectDatas($tmp_object);
    //             }
    //             $i++;
    //         }
    //     }
    //     else {
    //         throw new RestException(503, 'Error when retrieving dinamiccategorie list: '.$db->lasterror());
    //     }
    //     if (!count($obj_ret)) {
    //         throw new RestException(404, 'No dinamiccategorie found');
    //     }
    //     return $obj_ret;
    // }

    /**
     * Create dinamiccategorie object
     *
     * @param array $request_data   Request datas
     * @return int  ID of dinamiccategorie
     *
     * @throws RestException
     *
     * @url    POST dinamiccategories/
     */
    public function post($request_data = null)
    {
        if (!DolibarrApiAccess::$user->rights->dinamicpricelist->write) {
            throw new RestException(401);
        }
        // Check mandatory fields
        $result = $this->_validate($request_data);

        foreach ($request_data as $field => $value) {
            $this->dinamiccategorie->$field = $value;
        }
        if (!$this->dinamiccategorie->create(DolibarrApiAccess::$user)) {
            throw new RestException(500, "Error creating DinamicCategorie", array_merge(array($this->dinamiccategorie->error), $this->dinamiccategorie->errors));
        }
        return $this->dinamiccategorie->id;
    }

    /**
     * Update dinamiccategorie
     *
     * @param int   $id             Id of dinamiccategorie to update
     * @param array $request_data   Datas
     * @return int
     *
     * @throws RestException
     *
     * @url    PUT dinamiccategories/{id}
     */
    public function put($id, $request_data = null)
    {
        if (!DolibarrApiAccess::$user->rights->dinamicpricelist->write) {
            throw new RestException(401);
        }

        $result = $this->dinamiccategorie->fetch($id);
        if (!$result) {
            throw new RestException(404, 'DinamicCategorie not found');
        }

        if (!DolibarrApi::_checkAccessToResource('dinamiccategorie', $this->dinamiccategorie->id, 'dinamicpricelist_dinamiccategorie')) {
            throw new RestException(401, 'Access to instance id=' . $this->dinamiccategorie->id . ' of object not allowed for login ' . DolibarrApiAccess::$user->login);
        }

        foreach ($request_data as $field => $value) {
            if ($field == 'id') {
                continue;
            }

            $this->dinamiccategorie->$field = $value;
        }

        if ($this->dinamiccategorie->update($id, DolibarrApiAccess::$user) > 0) {
            return $this->get($id);
        } else {
            throw new RestException(500, $this->dinamiccategorie->error);
        }
    }

    /**
     * Delete dinamiccategorie
     *
     * @param   int     $id   DinamicCategorie ID
     * @return  array
     *
     * @throws RestException
     *
     * @url    DELETE dinamiccategories/{id}
     */
    public function delete($id)
    {
        if (!DolibarrApiAccess::$user->rights->dinamicpricelist->delete) {
            throw new RestException(401);
        }
        $result = $this->dinamiccategorie->fetch($id);
        if (!$result) {
            throw new RestException(404, 'DinamicCategorie not found');
        }

        if (!DolibarrApi::_checkAccessToResource('dinamiccategorie', $this->dinamiccategorie->id, 'dinamicpricelist_dinamiccategorie')) {
            throw new RestException(401, 'Access to instance id=' . $this->dinamiccategorie->id . ' of object not allowed for login ' . DolibarrApiAccess::$user->login);
        }

        if (!$this->dinamiccategorie->delete(DolibarrApiAccess::$user)) {
            throw new RestException(500, 'Error when deleting DinamicCategorie : ' . $this->dinamiccategorie->error);
        }

        return array(
            'success' => array(
                'code' => 200,
                'message' => 'DinamicCategorie deleted',
            ),
        );
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
    /**
     * Clean sensible object datas
     *
     * @param   object  $object    Object to clean
     * @return    array    Array of cleaned object properties
     */
    protected function _cleanObjectDatas($object)
    {
        // phpcs:enable
        $object = parent::_cleanObjectDatas($object);

        unset($object->rowid);
        unset($object->canvas);

        /*unset($object->name);
        unset($object->lastname);
        unset($object->firstname);
        unset($object->civility_id);
        unset($object->statut);
        unset($object->state);
        unset($object->state_id);
        unset($object->state_code);
        unset($object->region);
        unset($object->region_code);
        unset($object->country);
        unset($object->country_id);
        unset($object->country_code);
        unset($object->barcode_type);
        unset($object->barcode_type_code);
        unset($object->barcode_type_label);
        unset($object->barcode_type_coder);
        unset($object->total_ht);
        unset($object->total_tva);
        unset($object->total_localtax1);
        unset($object->total_localtax2);
        unset($object->total_ttc);
        unset($object->fk_account);
        unset($object->comments);
        unset($object->note);
        unset($object->mode_reglement_id);
        unset($object->cond_reglement_id);
        unset($object->cond_reglement);
        unset($object->shipping_method_id);
        unset($object->fk_incoterms);
        unset($object->label_incoterms);
        unset($object->location_incoterms);
         */

        // If object has lines, remove $db property
        if (isset($object->lines) && is_array($object->lines) && count($object->lines) > 0) {
            $nboflines = count($object->lines);
            for ($i = 0; $i < $nboflines; $i++) {
                $this->_cleanObjectDatas($object->lines[$i]);

                unset($object->lines[$i]->lines);
                unset($object->lines[$i]->note);
            }
        }

        return $object;
    }

    /**
     * Validate fields before create or update object
     *
     * @param    array        $data   Array of data to validate
     * @return    array
     *
     * @throws    RestException
     */
    private function _validate($data)
    {
        $dinamiccategorie = array();
        foreach ($this->dinamiccategorie->fields as $field => $propfield) {
            if (in_array($field, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat')) || $propfield['notnull'] != 1) {
                continue;
            }
            // Not a mandatory field
            if (!isset($data[$field])) {
                throw new RestException(400, "$field field missing");
            }

            $dinamiccategorie[$field] = $data[$field];
        }
        return $dinamiccategorie;
    }
}
