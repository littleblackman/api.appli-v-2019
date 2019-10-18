<?php

namespace App\Service\MyClub;

use PDO;


/**
 * Trait MigrationQueryBuilder
 * 
 * use to create PDO query on appli-v database
 */
trait MigrationQueryBuilder
{

    protected $table;
    protected $query;
    protected $fields = [];
    protected $joinList = [];
    protected $whereList = [];
    protected $orderBy;
    protected $limit;
    protected $groupBy;


    protected $conn;

    public function getConnexion()
    {
        try {
            $conn = new PDO("mysql:host=109.7.44.186:3306;dbname=myclub201606", 'appli-v', 'projetV2016*');
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
        $this->conn = $conn;
        return $conn;
    }

    public function resetQuery() {
      $this->table     = "";
      $this->query     = "";
      $this->fields    = [];
      $this->joinList  = [];
      $this->whereList = [];
      $this->orderBy   = "";
      $this->groupBy   = "";
      $this->limit     = "";
    }

    /**
     * set table target
     *
     * @param $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * get table
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * add fields list to retrieve
     * @param $fields
     */
    public function addFields($new_fields)
    {

        foreach($new_fields as $f)
        {
            $el = explode(' as ', $f);
            if(isset($el[1])) {
                $all_fields[] = $f;
            } else {
                $all_fields[] = $f.' as '.str_replace('.', '_', $f);
            }
        }

        $fields = array_merge($all_fields, $this->fields);
        $this->fields = $fields;
    }

    /**
     *
     * join a table
     *
     * @param $joinTable
     * @param $arg1
     * @param $arg2
     * @param string $join
     */
    public function addJoin($joinTable, $arg1, $arg2, $join = "LEFT") {
        $joinAction = array("table" => $joinTable, "arg1" => $arg1, "arg2" => $arg2, "join" => $join);
        $this->joinList[] = $joinAction;
    }

    /**
     * add the where aguments as string
     *
     * @param $where
     */
    public function addWhere($where)
    {
        $this->whereList[] = $where;
    }

    /**
     * add a limit
     *
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * set the order
     * @param $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * set the groupBy
     * @param $groupBy
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }


    public function createSelectQuery()
    {
        $query = " SELECT ";

        if(!$fields = $this->fields) return "Error ! no fields founded";

        $i = 0;
        foreach($fields as $field) {
            if($i === 1) $query .= ", ";
            $query .= $field;
            $i = 1;
        }

        $query .= " FROM ".$this->getTable()." ";

        if(count($this->joinList) > 0)
        {
            foreach($this->joinList as $joinAction)
            {
                $query .= " ".$joinAction['join']." JOIN ".$joinAction['table']." ON ".$joinAction['arg1']." = ".$joinAction['arg2'];
            }
        }


        $query .= " WHERE 1=1 ";
        if(count($this->whereList) > 0)  {
            foreach($this->whereList as $where)
            {
                $query .= " AND ".$where;
            }
        }


        if($this->orderBy) {
            $query .= " ORDER BY ".$this->orderBy;
        }

        if($this->groupBy) {
            $query .= " GROUP BY ".$this->groupBy;
        }


        if($this->limit) {
            $query .= " LIMIT ".$this->limit;
        }

        $this->query = $query;

        return $this;

    }

    /**
     * get the query string
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }


    /**
     *
     * retrieve all datas
     * @return array
     */
    public function getDatas($formatData = null)
    {
        $conn = $this->getConnexion();

        $r = $conn->prepare($this->query);
        $r->execute();

        $datas = $r->fetchAll(PDO::FETCH_ASSOC);

        $datas = $this->utf8encodeBddResult($datas);

        return $datas;
    }

    /**
     * execute query
     * @return mixed
     */
    public function setExecute($query)
    {
      $conn = $this->getConnexion();
      $r = $conn->prepare($query);
      $r->execute();
    }


    /**
     * Encode array of array in utf8
     * @param array as $key => $value to encode
     * @return array
     */
    public function utf8encodeBddResult($datas) {
        $new_datas = [];
        foreach($datas as $data)
        {
            $new_data = $this->utf8EncodeData($data);
            $new_datas[] = $new_data;
        }
        return $new_datas;
    }

    /**
     * Encode data in utf8
     * @param array as $key => $value to encode
     * @return array
     */
    public function utf8encodeData($data)
    {
        if(!is_array($data)) return $data;
        foreach($data as $k => $v)
        {
            $v = trim($v);
            (is_string($v)) ? $new_data[$k] = utf8_encode($v) : $new_data[$k] = $v;
        }
        return $new_data;
    }


}
