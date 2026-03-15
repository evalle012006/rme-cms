<?php

use Illuminate\Database\Seeder;
use App\Models\BlogCategory;
use App\Models\Blogs;
use App\Models\BlogAvailableCategory;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $blogDate = '2020-02-19 10:35:53';
        $blogDesc = 'test description';
        $blogStatus = 0;
        $seo_title = 'test';
        $seo_desc = 'test';
        $seo_keyword = 'test';

//        $buildingImg = 'feature-image/2020/02/12/SEp0IUiMDwIrSiNJ7HwtT89teTV1Zt5DhJo2N3P9.jpeg';
//        $blogImage = 'feature-image/2020/02/12/wCnXKkC9NAjhVouOrf4JrxErmtxT9bMLB8GOEfG8.jpeg';
//        $financeImg = 'blog-category-image/2020/02/12/F6QccrtU178EtGpsnLa9qhxX7dQdhMDHGv5PP67B.png';
//        $buyingImg = 'blog-category-image/2020/02/12/r4bNZwEulolVjOEdh0gHxSLoQXqRGjI9EkIIKJUW.png';
//        $investImg = 'blog-category-image/2020/02/12/H4zWDPDiVRjVOn9Pckmcs7yX4Ue9MVIJvygww9ri.png';
//        $buildImg = 'blog-category-image/2020/02/12/vSvzZeE2c0AIxi15lc1RbaustqEdgJpFXjSx2Ggx.png';


        // live img
        $blogImage = 'feature-image/2020/02/25/orum6O1FzDkUIKnU3Tgc0MQxLKgJUG3BXrPwx9NR.jpeg';
        $buildingImg = 'feature-image/2020/02/25/OYPTiDaCqX6B5KR2Qf8XMqVRkf9TLmlGjT78AUUL.jpeg';
        $financeImg = 'blog-category-image/2020/02/25/57Jmi4PSaBR0oekZkc1ZzaJmuxoVXwH4i3Msl9in.png';
        $buyingImg = 'blog-category-image/2020/02/25/U9VUWj4MVstWYN0KVeTLMplsCYihDMSi0fnDpLki.png';
        $investImg = 'blog-category-image/2020/02/25/KzNyITNqaz6awc3N7VTPX2pAZXPIIsgbJERET4J1.png';
        $buildImg = 'blog-category-image/2020/02/25/uGV6boTCSxaofc1qE3z3ugCRoDYOxk3YThslQWr0.png';

        $finance = [
            'name' => 'finance',
            'image' => $financeImg,
            'slug' => 'finance'
        ];
        $cat_finance = BlogCategory::create($finance);

        $blogFinance1 = [
            'title' => 'Describe Variation Finance1',
            'feature_image' => $blogImage,
            'blog_date' => $blogDate,
            'slug' => 'f1',
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_finance->id,
            'created_by' => 1
        ];
        $finance1 = Blogs::create($blogFinance1);
        BlogAvailableCategory::create([
            'blog_id' => $finance1->id,
            'blog_category_id' => $cat_finance->id
        ]);

        $blogFinance2 = [
            'title' => 'Describe Variation Finance2',
            'feature_image' => $blogImage,
            'slug' => 'f2',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_finance->id,
            'created_by' => 1
        ];
        $finance2 = Blogs::create($blogFinance2);
        BlogAvailableCategory::create([
            'blog_id' => $finance2->id,
            'blog_category_id' => $cat_finance->id
        ]);

        $blogFinance3 = [
            'title' => 'Describe Variation Finance3',
            'feature_image' => $blogImage,
            'slug' => 'f3',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_finance->id,
            'created_by' => 1
        ];
        $finance3 = Blogs::create($blogFinance3);
        BlogAvailableCategory::create([
            'blog_id' => $finance3->id,
            'blog_category_id' => $cat_finance->id
        ]);

        $blogFinance4 = [
            'title' => 'Describe Variation Finance4',
            'feature_image' => $blogImage,
            'slug' => 'f4',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_finance->id,
            'created_by' => 1
        ];
        $finance4 = Blogs::create($blogFinance4);
        BlogAvailableCategory::create([
            'blog_id' => $finance4->id,
            'blog_category_id' => $cat_finance->id
        ]);


        $buying = [
            'name' => 'buying',
            'image' => $buyingImg,
            'slug' => 'buying'
        ];
        $cat_buying = BlogCategory::create($buying);
        $blogBuying1 = [

            'title' => 'Describe Variation Buying1',
            'feature_image' => $blogImage,
            'blog_date' => $blogDate,
            'slug' => 'bu1',
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_buying->id,
            'created_by' => 1
        ];
        $buying1 = Blogs::create($blogBuying1);
        BlogAvailableCategory::create([
            'blog_id' => $buying1->id,
            'blog_category_id' => $cat_buying->id
        ]);

        $blogBuying2 = [
            'title' => 'Describe Variation Buying2',
            'feature_image' => $blogImage,
            'blog_date' => $blogDate,
            'slug' => 'bu2',
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_buying->id,
            'created_by' => 1
        ];
        $buying2 = Blogs::create($blogBuying2);
        BlogAvailableCategory::create([
            'blog_id' => $buying2->id,
            'blog_category_id' => $cat_buying->id
        ]);


        $blogBuying3 = [
            'title' => 'Describe Variation Buying3',
            'feature_image' => $blogImage,
            'slug' => 'bu3',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_buying->id,
            'created_by' => 1
        ];
        $buying3 = Blogs::create($blogBuying3);
        BlogAvailableCategory::create([
            'blog_id' => $buying3->id,
            'blog_category_id' => $cat_buying->id
        ]);

        $blogBuying4 = [
            'title' => 'Describe Variation Buying4',
            'feature_image' => $blogImage,
            'slug' => 'bu4',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_buying->id,
            'created_by' => 1
        ];
        $buying4 = Blogs::create($blogBuying4);
        BlogAvailableCategory::create([
            'blog_id' => $buying4->id,
            'blog_category_id' => $cat_buying->id
        ]);


        $investing = [
            'name' => 'investing',
            'image' => $investImg,
            'slug' => 'investing'
        ];
        $cat_investing = BlogCategory::create($investing);
        $blogInvesting1 = [
            'title' => 'Describe Variation Investing1',
            'feature_image' => $blogImage,
            'slug' => 'in1',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_investing->id,
            'created_by' => 1
        ];
        $investing1 = Blogs::create($blogInvesting1);
        BlogAvailableCategory::create([
            'blog_id' => $investing1->id,
            'blog_category_id' => $cat_investing->id
        ]);

        $blogInvesting2 = [
            'title' => 'Describe Variation Investing2',
            'feature_image' => $blogImage,
            'slug' => 'in2',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_investing->id,
            'created_by' => 1
        ];
        $investing2 = Blogs::create($blogInvesting2);
        BlogAvailableCategory::create([
            'blog_id' => $investing2->id,
            'blog_category_id' => $cat_investing->id
        ]);

        $blogInvesting3 = [
            'title' => 'Describe Variation Investing3',
            'feature_image' => $blogImage,
            'slug' => 'in3',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_investing->id,
            'created_by' => 1
        ];
        $investing3 = Blogs::create($blogInvesting3);
        BlogAvailableCategory::create([
            'blog_id' => $investing3->id,
            'blog_category_id' => $cat_investing->id
        ]);

        $blogInvesting4 = [
            'title' => 'Describe Variation Investing4',
            'feature_image' => $blogImage,
            'slug' => 'in4',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_investing->id,
            'created_by' => 1
        ];
        $investing4 = Blogs::create($blogInvesting4);
        BlogAvailableCategory::create([
            'blog_id' => $investing4->id,
            'blog_category_id' => $cat_investing->id
        ]);


        $building = [
            'name' => 'building',
            'image' => $buildImg,
            'slug' => 'building'
        ];
        $cat_building = BlogCategory::create($building);
        $blogBuilding1 = [
            'title' => 'Describe Variation Building1',
            'feature_image' => $buildingImg,
            'blog_date' => $blogDate,
            'slug' => 'bl1',
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_building->id,
            'created_by' => 1
        ];
        $building1 = Blogs::create($blogBuilding1);
        BlogAvailableCategory::create([
            'blog_id' => $building1->id,
            'blog_category_id' => $cat_building->id
        ]);

        $blogBuilding2 = [
            'title' => 'Describe Variation Building2',
            'feature_image' => $buildingImg,
            'slug' => 'bl2',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_building->id,
            'created_by' => 1
        ];
        $building2 = Blogs::create($blogBuilding2);
        BlogAvailableCategory::create([
            'blog_id' => $building2->id,
            'blog_category_id' => $cat_building->id
        ]);

        $blogBuilding3 = [
            'title' => 'Describe Variation Building3',
            'feature_image' => $buildingImg,
            'slug' => 'bl3',
            'blog_date' => $blogDate,
            'description' => $blogDesc,
            'status' => $blogStatus,
            'seo_title' => $seo_title,
            'seo_description' => $seo_desc,
            'seo_keywords' => $seo_keyword,
            'category_id' => $cat_building->id,
            'created_by' => 1
        ];
        $building3 = Blogs::create($blogBuilding3);
        BlogAvailableCategory::create([
            'blog_id' => $building3->id,
            'blog_category_id' => $cat_building->id
        ]);


    }
}
