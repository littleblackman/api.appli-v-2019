<?php

namespace App\Service\MyClub;

/**
 * Class MigrationTransformDatas
 *
 * transform the data to import value
 */
trait MigrationTransformDatas
{

    private $chield_fields = [
                            'gender' => 1, 'firstname' => 1, 'lastname' => 1, 'phone' => 0, 'birthdate' => 1,
                            'medical' => 1, 'pickup_instruction' => 1, 'photo' => 0, 'c_created_at' => 1, 'updated_at' => 1,
                            'family_id' => 1, 'child_id' => 0
                         ];

     private $person_fields = [
                             'gender' => 1, 'firstname' => 1, 'lastname' => 1, 'phone' => 0, 'birthdate' => 1,
                             'medical' => 1, 'pickup_instruction' => 1, 'photo' => 0, 'created_at' => 1, 'updated_at' => 1,
                             'family_id' => 1, 'child_id' => 0
                          ];

      private $padaref_ref = [
                                                    328  => 'frère'  ,
                                                    327  => 'soeur'  ,
                                                    320  => 'Ecole'  ,
                                                    81  => 'Enfant'  ,
                                                    77  => 'Grand-parent'  ,
                                                    76  => 'Baby-sitter'  ,
                                                    75  => 'Mère'  ,
                                                    74  => 'Père'  ,
                                                    73  => 'mobile2'  ,
                                                    72  => 'mobile1'  ,
                                                    71  => 'Bureau2'  ,
                                                    70  => 'Bureau1'  ,
                                                    69  => 'Domicile2' ,
                                                    68  => 'Domicile1' ,
                                                    307 => 'Prise en charge',
                                                    308 => 'Postale',
                                                    86  => 'Prise en charge',
                                                    85  => 'Prioritaire',
                                                    342 => 'Personnel',
                                                    343 => 'Professionnel',
                                                    332 => 'Point de rdv'
                                            ];

    public function extractChild($data) {
        $child = [];
        foreach($this->chield_fields as $field => $needed)
        {
            if(array_key_exists($field, $data)) {
                $value = $data[$field];
                if($field == 'lastname' && $value == "") $value = $data['family_name'];
                if($field == 'gender' && $value == "M") $value = "h";
                if($field == 'gender' && $value == "F") $value = "f";
                $child[$field] = $value;
            }
        }
        return $child;
    }

    public function extractPerson($data) {
        $person = [];
        foreach($this->chield_fields as $field => $needed)
        {
            if(array_key_exists($field, $data)) {
                $value = $data[$field];
                if($field == 'lastname' && $value == "") $value = $data['family_name'];
                if($field == 'gender' && $value == "M") $value = "h";
                if($field == 'gender' && $value == "F") $value = "f";
                $child[$field] = $value;
            }
        }
        return $person;
    }

    public function isValidChild($child)
    {
        $valid = 1;
        foreach($this->chield_fields as $field => $needed)
        {
            if(!array_key_exists($field, $child) && $needed == 1) $valid = 0;
        }
        return $valid;
    }

    public function extractPostalCode($address) {

        $postal = null;
        preg_match_all('!\d+!', $address, $matches);
        if(isset($matches[0])) {
            foreach($matches[0] as $number) {
                if (preg_match('#^[0-9]{5}$#',$number)) {
                    $postal = $number;
                }
            }
        }

        return $postal;
    }

    public function extractFamilyDatas($datas)
    {
        if(empty($datas)) return null;
        $u = 1; $p = 1;
        foreach($datas as $data) {
            // extract childs
            if($childArray = $this->extractChild($data)) {
                $childs[$childArray['child_id']] = $childArray;
            }

            // user
            if($u == 1) {
                if(isset($data['email']) && $data['email'] != "") {
                    $user['email'] = $data['email'];
                    $user['password'] = "zlatan";
                } else {
                    $user['email'] = "";
                    $user['password'] = "zlatan";
                    $u = 0;
                }
                $u = 0;
            }

            // person
            if($p == 1) {
                if(isset($data['family_name']) && $data['family_name'] != "") {

                    if($u == 0) {
                        $element = explode('@', $user['email']);
                        $firstname = ucfirst($element[0]);
                    } else {
                        $firstname = "x";
                    }
                    $person['firstname'] = $firstname;
                    $person['lastname'] = $data['family_name'];
                    $p = 0;
                }
            }


            // address
            $address[$data['address_id']] = [
                                        'name' => $this->padaref_ref[$data['address_type_id']],
                                        'address' => $data['address'],
                                        'postal' => $data['postal'],
                                        'town' => $data['town'],
                                        'country' => 'France'
                                ];


            // phone
            $phones[$data['phone_id']] = [
                                        'name' => $this->padaref_ref[$data['telephon_type_id']],
                                        'phone' => $data['phone']
                                ];

        }
        return $datas = [
                                        'user'    => $user,
                                        'person'  => $person,
                                        'address' => $address,
                                        'phones'  => $phones,
                                        'childs'  => $childs
                        ];
    }

}
