<?php

namespace Database\Seeders;

use App\Models\Trust;
use App\Models\Hospital;
use App\Models\Speciality;
use App\Models\Subspeciality;


use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        //Trust & Hospital
        $trusts = [
            [
                'trust_name'        => 'Trust 1',
                'trust_description' => null,
                'chair'             => null,
            ],
            [
                'trust_name'        => 'Trust 2',
                'trust_description' => null,
                'chair'             => null,
            ],
        
        ];

        foreach($trusts as $key=>$data){

            $created_trust = Trust::create($data);

            if($created_trust){

                $hospitals =[
                    'trust' => $created_trust->id,
                    'hospital_name' => 'Hospital '.$key+1,
                    'hospital_description' => null
                ];
                    

                    $created_hospital = Hospital::create($hospitals);

                      //Speciality & Subspeciality
                        $specialities = 
                            [
                                'hospital_id' => $created_hospital->id,
                                'speciality_name' => 'Speciality '.$key+1,
                                'speciality_description' => null,
                            ];
                            

                            $speciality = Speciality::create($specialities);


                            $sub_specialities = [
                                [
                                    'parent_speciality_id' => $speciality->id,
                                    'sub_speciality_name' => 'Sub Speciality 1',
                                    'sub_speciality_description' => null,
                                ],
                                [
                                    'parent_speciality_id' => $speciality->id,
                                    'sub_speciality_name' => 'Sub Speciality 2',
                                    'sub_speciality_description' => null,
                                ]
                            ];

                            foreach($sub_specialities as $sub_specialities_data){

                                Subspeciality::create($sub_specialities_data);

                            }

                        
                      //End Speciality & Subspeciality
                
                   

            }

        }

        //End Trust & Hospital

      

       
    }
}
