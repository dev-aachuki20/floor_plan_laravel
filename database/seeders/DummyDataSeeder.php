<?php

namespace Database\Seeders;

use App\Models\Trust;
use App\Models\Hospital;
use App\Models\Speciality;
use App\Models\SubSpeciality;


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

        $hospital_index = 0;
        foreach ($trusts as $key => $data) {

            $created_trust = Trust::create($data);

            if ($created_trust) {

                $hospitals = [];
             
                for ($key = 0; $key < 3; $key++) {
                    $hospital_index = $hospital_index + 1;
                    $hospitals[] = [
                        'trust' => $created_trust->id,
                        'hospital_name' => 'Hospital ' . $hospital_index,
                        'hospital_description' => null
                    ];
                }

                foreach ($hospitals as $hospital) {
                    $created_hospital = Hospital::create($hospital);

                    [
                        'trust' => $created_trust->id,
                        'hospital_name' => 'Hospital ' . $hospital_index,
                        'hospital_description' => null
                    ];

                }

            }
        }

        //End Trust & Hospital


        //Speciality & Subspeciality
        $specialities = [
            'Orthopaedics' => [
                'Spine Surgery',
                'Sports Medicine',
                'Joint Replacement',
                'Pediatric Orthopaedics',
                'Hand Surgery',
                'Orthopaedic Oncology',
            ],
            'Ophthalmology' => [
                'Retina/Vitreous Surgery',
                'Cornea/External Disease',
                'Glaucoma',
                'Pediatric Ophthalmology',
                'Oculoplastics',
                'Neuro-Ophthalmology',
            ],
            'ENT' => [
                'Pediatric ENT',
                'Head and Neck Surgery',
                'Otology/Neurotology',
                'Rhinology/Sinus Surgery',
                'Laryngology',
                'Facial Plastic and Reconstructive Surgery',
            ],
            'Gynaecology' => [
                'Reproductive Endocrinology and Infertility',
                'Gynecologic Oncology',
                'Maternal-Fetal Medicine',
                'Urogynecology',
                'Pediatric and Adolescent Gynecology',
            ],
            'Urology' => [
                'Pediatric Urology',
                'Urologic Oncology',
                'Female Pelvic Medicine and Reconstructive Surgery',
                'Endourology/Stone Disease',
                'Male Infertility',
            ],
            'General' => [
                'Trauma Surgery',
                'Critical Care Surgery',
                'Colorectal Surgery',
                'Endocrine Surgery',
                'Minimally Invasive Surgery',
            ],
            'MFU' => [
                'Physical Therapy',
                'Sports Medicine',
                'Orthopaedic Rehabilitation',
                'Pain Management',
            ],
            'Breast' => [
                'Breast Surgery',
                'Breast Oncology',
                'Breast Imaging',
                'Reconstructive Breast Surgery',
            ],
            'Vascular' => [
                'Vascular Surgery',
                'Endovascular Surgery',
                'Vascular Medicine',
                'Vascular Imaging',
            ],
            'Unavailable' => null
        ];


        foreach ($specialities as $speciality_key => $subSpecialities) {

            $speciality_data = [
                'speciality_name' => $speciality_key,
                'speciality_description' => null,
            ];

            $speciality = Speciality::create($speciality_data);

            foreach ($subSpecialities as $subSpeciality) {

                $sub_specialities =
                [
                    'parent_speciality_id' => $speciality->id,
                    'sub_speciality_name' => $subSpeciality,
                    'sub_speciality_description' => null,
                ];

                SubSpeciality::create($sub_specialities);
            }
        }
        //End Speciality & Subspeciality



    }
}
