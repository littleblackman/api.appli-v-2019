<?php

namespace App\Service\MyClub;
use App\Service\MyClub\MigrationQueryBuilder;


/**
 * Trait MigrationImportFunctions
 */
trait MigrationImportFunctions
{

    use MigrationQueryBuilder;

    public function setImported($table_name, $id) {
      $this->resetQuery();
      $query = 'update '.$table_name.' set _importedInPV = 1 where id = '.$id;
      $this->setExecute($query);
    }

    public function importActivitys($date, $limit = 10) {

        // first query  // child with desiderata
        /*"SELECT esd.child_id, esd.sport_list, ecp.day_ref
        FROM ea_sport_desiderata esd
        LEFT JOIN ea_child_presence ecp ON ecp.id = esd.presence_id
        WHERE 1 =1
        AND ecp.date_presence = "2019-09-28"
        LIMIT 0 , 30"*/

        // second query // child in the seance
        /*SELECT es.date_seance, es.sport_id, esc.child_id, es.moment
        FROM ea_seance es
        LEFT JOIN ea_seance_child esc ON esc.seance_id = es.id
        WHERE es.date_seance = "2019-09-28"*/

        $this->resetQuery();

        $this->setTable('ea_seance es');
        $this->addFields(array('es.date_seance as date_seance' ,'es.sport_id as sport_id', 'es.moment as moment'));
        $this->addJoin('ea_seance_child esc', 'es.id', 'esc.seance_id');
        $this->addWhere("es.date_seance = '".$date."'");

        // child and family informations
        $this->addFields(array('c.id as child_id', 'c.first_name as firstname', 'c.child_last_name as lastname', 'c.sexe as gender', 'c.birthday as birthdate', 'c.medical_note as medical', 'c.family_id as family_id', 'c.created_at as c_created_at', 'c.updated_at as updated_at'));
        $this->addFields(array('f.name as family_name'));
        $this->addFields(array('ref.name as pickup_instruction'));

        $this->addJoin('ea_child as c', 'c.id', 'esc.child_id');
        $this->addJoin('ea_family as f', 'f.id', 'c.family_id');
        $this->addJoin('padaref_ref as ref', 'ref.id', 'c.need_call');

        $this->setOrderBy('child_id');
        $this->setLimit($limit);

        $datas = $this->createSelectQuery()->getDatas();


        return $datas;
    }

    public function importPresenceId($date)
    {
        $this->resetQuery();

        $this->setTable('ea_child_presence pe');
        $this->addFields(array('pe.child_id as child_id' ,'pe.date_presence as date_presence', 'pe.day_ref as day_ref'));
        $this->addWhere("pe.date_presence = '".$date."'");
        $this->setGroupBy('pe.child_id');

        $list = $this->createSelectQuery()->getDatas();

        return $list;

      }

    public function importTransport($date, $limit = 10){

        $this->resetQuery();

        $this->setTable('ea_transport as t');

        // driver
        $this->addFields(array('d.name as driver_name' ,'d.last_name as last_name', 'd.id as driver_id'));

        // transport information
        $this->addFields(array('t.id as transport_id', 't.driver_id as driver_id', 't.child_id as child_id', 't.presence_id as presence_id'));
        $this->addFields(array('t.date_presence as date', 't.moment as moment', 't.type as type'));
        $this->addFields(array('t.order_rdv as order_rdv', 't.time_rdv as time_rdv', 't.vehicle_id as vehicle_id'));
        $this->addFields(array('t.abs as abs', 't.taked as taked', 't.time_taked as time_taked'));
        $this->addFields(array('t.time_taked as time_taked', 't.address as address'));
        $this->addFields(array('ref.name as pickup_instruction'));

        $this->addFields(array('c.created_at as created_at', 'c.updated_at as updated_at'));

        // child and family informations
        $this->addFields(array('c.id as child_id', 'c.first_name as firstname', 'c.child_last_name as lastname', 'c.sexe as gender', 'c.birthday as birthdate', 'c.medical_note as medical', 'c.family_id as family_id', 'c.created_at as c_created_at', 'c.updated_at as updated_at'));
        $this->addFields(array('f.name as family_name'));

        $this->addJoin('ea_driver as d', 'd.id', 't.driver_id');
        $this->addJoin('ea_child as c', 'c.id', 't.child_id');
        $this->addJoin('ea_family as f', 'f.id', 'c.family_id');
        $this->addJoin('padaref_ref as ref', 'ref.id', 'c.need_call');

        $this->addWhere('t._importedInPV = 0');
        $this->addWhere("t.date_presence = '".$date."'");

        $this->setLimit($limit);

        $datas = $this->createSelectQuery()->getDatas();

        return $datas;

    }

    public function importFamily($family_id) {

        $this->resetQuery();

        $this->setTable('ea_family as f');

        // child and family
        $this->addFields(array('c.id as child_id', 'c.first_name as firstname', 'c.child_last_name as lastname', 'c.sexe as gender', 'c.birthday as birthdate', 'c.medical_note as medical', 'c.family_id as family_id', 'c.created_at as c_created_at'));
        $this->addFields(array('f.name as family_name', 'f.id as family_id'));

        // address
        $this->addFields(array('a.id as address_id', 'a.address as address', 'a.postal_code as postal', 'a.city as town', 'a.type_id as address_type_id'));
        $this->addFields(array('e.id as email_id','e.email as email', 'e.type_id as email_type_id'));
        $this->addFields(array('t.id as phone_id','t.telephon as phone', 't.type_id as telephon_type_id'));

        $this->addJoin('ea_child as c', 'c.family_id', 'f.id');
        $this->addJoin('ea_family_address as a', 'a.family_id', 'f.id');
        $this->addJoin('ea_family_email as e', 'e.family_id', 'f.id');
        $this->addJoin('ea_family_telephon as t', 't.family_id', 'f.id');

        $this->addWhere('f.is_archived = 0');
        $this->addWhere('f.id = '.$family_id);

        $datas = $this->createSelectQuery()->getDatas();

        return $datas;

    }
}
