<?php

use App\Models\PropertyAvailableCategory;
use Illuminate\Database\Seeder;
use App\Models\PropertyCategory;
use App\Models\RatingConfiguration;
use App\Models\PropertyAvailableRating;

class PropertyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //live icon
        $buildOnTimeIcon = 'ratingconf-icon/2020/02/27/ANb6Vks9OL6JPkdbiSUPDiJQmKhBygaIlHzOYSur.png';
        $amenitiesIcon = 'ratingconf-icon/2020/02/27/oZM6TxKPrMrglGi7bHidELgpsE2SRAOFKpjdThkd.png';
        $designIcon = 'ratingconf-icon/2020/02/27/NATIGveUbM0WLN4ToUU3zJhKnT8LrhBZIMePdzu7.png';
        $custIcon = 'ratingconf-icon/2020/02/27/jsoJcusQ2tL2JpADWt51LqUyACbOw3bVs22wMwsa.png';
        $qualityIcon = 'ratingconf-icon/2020/02/27/swvDtqXKFdemHxXe5Q89C0UKhKoB1PK5uBf7prNB.png';
        $defectIcon = 'ratingconf-icon/2020/02/27/Ye7ZjWOeutkBXyKWMGeiDen8hplk9jG56O5HpxcQ.png';
        $locationIcon = 'ratingconf-icon/2020/02/27/zDlaWwTgaaI0OGevIJoybimE5gOSdcpiWaZA6sWS.png';
        $communityIcon = 'ratingconf-icon/2020/02/27/bWC72RMQlkA9QEEUKoNQM9JiyS3cY868a9mVQdpu.png';
        $settlementIcon = 'ratingconf-icon/2020/02/27/VwcBA5dUhhVdqZq9KAWmbgBhLRVN4a2Ahbqs1rot.png';


        $builtTime = RatingConfiguration::create(
            [
                'rating_icon' => $buildOnTimeIcon,
                'description' => 'Was the build completed as per contract of sale or did you experience delays?',
                'name' => 'Built on time'
            ]);
        $amenities = RatingConfiguration::create(
            [
                'rating_icon' => $amenitiesIcon,
                'description' => 'How would you rate the level of amenities in the estate or future estate to be developed? (Transport & Road Infrastructure, Parks, Shopping Centers, Schools)',
                'name' => 'Amenities'
            ]);
        $design = RatingConfiguration::create(
            [
                'rating_icon' => $designIcon,
                'description' => 'How active were you able to involved in the design or request variations?',
                'name' => 'Design Variation & input management'
            ]);
        $customer = RatingConfiguration::create(
            [
                'rating_icon' => $custIcon,
                'description' => 'How would you rate the Customer Service? Did you find the builder transparent, always in communication and professional both pre-settlement and post-settlement?',
                'name' => 'Customer service'
            ]);
        $quality = RatingConfiguration::create(
            [
                'rating_icon' => $qualityIcon,
                'description' => 'How would you rate the quality of the product, finishes, materials etc.',
                'name' => 'Quality'
            ]);
        $defect = RatingConfiguration::create(
            [
                'rating_icon' => $defectIcon,
                'description' => 'How would you rate defect management? Experience too many defects? Were they completed in a timely manner?',
                'name' => 'Defects & Warranty management'
            ]);
        $location = RatingConfiguration::create(
            [
                'rating_icon' => $locationIcon,
                'description' => 'How would you rate the location from work and the daily needs?',
                'name' => 'Location'
            ]);
        $community = RatingConfiguration::create(
            [
                'rating_icon' => $communityIcon,
                'description' => 'How would you rate the communities engagement, do you find the community safe and secure?',
                'name' => 'Community'
            ]);
        $settlement = RatingConfiguration::create(
            [
                'rating_icon' => $settlementIcon,
                'description' => 'How would you rate the settlement process? Were you provided with adequate notice prior to settlement? Was it an easy process and you were supported by the developer?',
                'name' => 'Settlement Process'
            ]);

        $homesite = PropertyCategory::create(
            [
                'name' => 'Homesite',
                'image' => 'test',
                'slug' => 'homesite',
                'description' => 'land/lot bought within a residential estate',
                'seo_title' => 'Homesite',
                'seo_description' => 'Homesite',
                'seo_keywords' => 'Homesite',
                'question' => 'What is the company or developer name?'
            ]);
        $homeland = PropertyCategory::create(
            [
                'name' => 'Home & Land',
                'slug' => 'homeland',
                'image' => 'test',
                'description' => 'home & land executed as two contracts one with developer and the other with the builder.',
                'seo_title' => 'Homeland',
                'seo_description' => 'Homeland',
                'seo_keywords' => 'Homeland',
                'question' => 'Who was your builder?'
            ]);
        $apartments = PropertyCategory::create(
            [
                'name' => 'Apartments',
                'slug' => 'apartments',
                'image' => 'test',
                'description' => 'a residential unit within a large residential complex/building',
                'seo_title' => 'Apartments',
                'seo_description' => 'Apartments',
                'seo_keywords' => 'Apartments',
                'question' => 'What is the builder name?'
            ]);
        $townhome = PropertyCategory::create(
            [
                'name' => 'Townhomes',
                'slug' => 'town-homes',
                'image' => 'test',
                //'description' => 'a row of house having shared dividing walls in a residential estate',
                'description' => 'a row of houses having shared diving walls in a residential estate',
                'seo_title' => 'Town Homes',
                'seo_description' => 'Town Homes',
                'seo_keywords' => 'Town Homes',
                'question' => 'What is the builder name?'
            ]);
        $homebuilder = PropertyCategory::create(
            [
                'name' => 'Homebuilder',
                'slug' => 'homebuilder',
                'image' => 'test',
                'description' => 'a company who build residential houses',
                'seo_title' => 'Homebuilder',
                'seo_description' => 'Homebuilder',
                'seo_keywords' => 'Homebuilder',
                'question' => 'Who was your builder?'

            ]);

        //manage rating
        $homesiteIns = [
            [
                'property_category_id' => $homesite->id,
                'rating_conf_id' => $amenities->id
            ],
            [
                'property_category_id' => $homesite->id,
                'rating_conf_id' => $customer->id
            ],
            [
                'property_category_id' => $homesite->id,
                'rating_conf_id' => $quality->id
            ],
            [
                'property_category_id' => $homesite->id,
                'rating_conf_id' => $location->id
            ],
            [
                'property_category_id' => $homesite->id,
                'rating_conf_id' => $community->id
            ],
            [
                'property_category_id' => $homesite->id,
                'rating_conf_id' => $settlement->id
            ],
        ];
        PropertyAvailableRating::insert($homesiteIns);

        $homelandIns = [
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $amenities->id
            ],
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $customer->id
            ],
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $quality->id
            ],
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $defect->id
            ],
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $location->id
            ],
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $community->id
            ],
            [
                'property_category_id' => $homeland->id,
                'rating_conf_id' => $settlement->id
            ],
        ];
        PropertyAvailableRating::insert($homelandIns);

        $apartmentsIns = [
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $builtTime->id
            ],
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $amenities->id
            ],
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $design->id
            ],
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $customer->id
            ],
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $quality->id
            ],
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $defect->id
            ],
            [
                'property_category_id' => $apartments->id,
                'rating_conf_id' => $settlement->id
            ],

        ];
        PropertyAvailableRating::insert($apartmentsIns);

        $townhomeIns = [
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $builtTime->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $amenities->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $design->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $customer->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $quality->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $defect->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $location->id
            ],
            [
                'property_category_id' => $townhome->id,
                'rating_conf_id' => $settlement->id
            ],
        ];
        PropertyAvailableRating::insert($townhomeIns);


        $homebuilderIns = [
            [
                'property_category_id' => $homebuilder->id,
                'rating_conf_id' => $builtTime->id
            ],
            [
                'property_category_id' => $homebuilder->id,
                'rating_conf_id' => $design->id
            ],
            [
                'property_category_id' => $homebuilder->id,
                'rating_conf_id' => $customer->id
            ],
            [
                'property_category_id' => $homebuilder->id,
                'rating_conf_id' => $quality->id
            ],
            [
                'property_category_id' => $homebuilder->id,
                'rating_conf_id' => $defect->id
            ],
            [
                'property_category_id' => $homebuilder->id,
                'rating_conf_id' => $settlement->id
            ],
        ];
        PropertyAvailableRating::insert($homebuilderIns);


//        //dummy property
//        $insProp['project_name'] = 'Eveleigth';
//        $insProp['developer_name'] = 1;
//        $insProp['status'] = 1;
//        $insProp['address'] = '36 china town';
//        $insProp['country'] = 'USA';
//        $insProp['state'] = 'USA';
//        $insProp['city'] = 'USA';
//        $insProp['lat'] = '-33.8688';
//        $insProp['long'] = '151.2093';
//        $prop_create = \App\Models\Properties::create($insProp);
//
//        $propCatIns['property_id'] = $prop_create->id;
//        $propCatIns['property_category_id'] = $homesite->id;
//        $propCat = PropertyAvailableRating::create($propCatIns);


    }
}
