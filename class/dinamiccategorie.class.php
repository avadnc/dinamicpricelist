<?php
class DinamicCategorie extends CommonObject
{

    public $db;
    public $fk_categorie;
    public $type = null;
    public $value = 0;
    public $entity = 0;
    public $tms;
    public $lines;

    public $fields = array(
        'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 10),
        'fk_categorie' => array('type' => 'integer:Categorie:categories/class/categorie.class.php', 'label' => 'CategorieID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 15),
        'type' => array('type' => 'varchar(50)', 'label' => 'Type', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 20),
        'value' => array('type' => 'double(20,2)', 'label' => 'Value', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 25),
        'user' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 30),
        'entity' => array('type' => 'integer', 'label' => 'entity', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 35),
        'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 40),
    );

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create(User $user)
    {

        global $conf;

        $fk_categorie = $this->fk_categorie;
        $type = $this->type;
        $value = $this->value;
        $entity = $this->entity;
        $error = 0;

        if (!is_numeric($fk_categorie) || !is_numeric($value)) {
            $error--;
            return "numeric error " . $error;
        }

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "dinamicprice_categorie (fk_categorie,type,value,user,entity) VALUES (";
        $sql .= $fk_categorie . ",";
        $sql .= "'" . $type . "',";
        $sql .= $value . ",";
        $sql .= $user->id . ",";
        $sql .= $entity;
        $sql .= ")";
        
        $this->db->begin();
        $resql = $this->db->query($sql);

        if (!$resql) {
            $this->error = $this->db->lasterror();

            dol_syslog(get_class($this) . "::create error " . $this->error, LOG_ERR);

            $this->db->rollback();

            $error--;
            return $error;

        } else {
            $this->db->commit();
            return 1;
        }
    }

    public function fetch($id = null, $fk_categorie = null)
    {

        $sql = "SELECT rowid,fk_categorie,type,value,user,entity,tms from " . MAIN_DB_PREFIX . "dinamicprice_categorie where ";
        $sql .= $id ? "rowid = " . $id : null;
        $sql .= $fk_categorie ? "fk_categorie = " . $fk_categorie : null;
		$sql .= " ORDER BY tms DESC ";
        $resql = $this->db->query($sql);
        if ($this->db->num_rows($resql)) {
            $obj = $this->db->fetch_object($resql);

            $this->rowid = $obj->rowid;
            $this->fk_categorie = $obj->fk_categorie;
            $this->type = $obj->type;
            $this->value = $obj->value;
            $this->user = $obj->user;
            $this->entity = $obj->entity;
            $this->tms = $obj->tms;
        }

    }

    public function fetchAll($sortorder = null, $limit = null)
    {
        $sql = "SELECT rowid,fk_categorie,type,value,user,entity,tms from " . MAIN_DB_PREFIX . "dinamicprice_categorie ";
        $sql .= $sortorder ? " ORDER BY " . $sortorder . " DESC " : null;
        $sql .= $limit ? " LIMIT " . $limit : null;

        $resql = $this->db->query($sql);
        $num = $this->db->num_rows($resql);
        if ($num > 0) {
            $i = 0;

            $this->lines = [];

            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);

                $this->lines[$i]['rowid'] = $obj->rowid;
                $this->lines[$i]['fk_categorie'] = $obj->fk_categorie;
                $this->lines[$i]['type'] = $obj->type;
                $this->lines[$i]['value'] = $obj->value;
                $this->lines[$i]['user'] = $obj->user;
                $this->lines[$i]['entity'] = $obj->entity;
                $this->lines[$i]['tms'] = $obj->tms;

                $i++;
            }
        }
    }
}
