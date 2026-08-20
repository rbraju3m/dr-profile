<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Stat;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['Years of Experience', 'বছরের অভিজ্ঞতা', 22, '+', 'award'],
            ['Patients Treated', 'চিকিৎসা নেওয়া রোগী', 48000, '+', 'users'],
            ['Cardiac Procedures', 'হৃদরোগ প্রসিডিওর', 6200, '+', 'heart-pulse'],
            ['Published Papers', 'প্রকাশিত গবেষণাপত্র', 34, '', 'book-open'],
        ];

        foreach ($stats as $i => [$en, $bn, $value, $suffix, $icon]) {
            Stat::updateOrCreate(
                ['label_en' => $en],
                ['label_bn' => $bn, 'value' => $value, 'suffix' => $suffix, 'icon' => $icon, 'sort_order' => $i, 'is_active' => true]
            );
        }

        $sliders = [
            [
                'title_en' => 'Heart care that explains itself',
                'title_bn' => 'যে হৃদরোগ চিকিৎসা নিজেই বুঝিয়ে দেয়',
                'sub_en' => 'Twenty-two years of interventional cardiology, and a consultation that ends only when your questions do.',
                'sub_bn' => 'বাইশ বছরের ইন্টারভেনশনাল কার্ডিওলজি অভিজ্ঞতা, আর এমন পরামর্শ যা আপনার প্রশ্ন শেষ হলেই কেবল শেষ হয়।',
                'cta_en' => 'Book an Appointment', 'cta_bn' => 'অ্যাপয়েন্টমেন্ট নিন',
            ],
            [
                'title_en' => 'Three chambers across Dhaka',
                'title_bn' => 'ঢাকাজুড়ে তিনটি চেম্বার',
                'sub_en' => 'Bashundhara, Dhanmondi and Sher-e-Bangla Nagar — pick the one nearest to you.',
                'sub_bn' => 'বসুন্ধরা, ধানমন্ডি ও শেরেবাংলা নগর — আপনার কাছেরটি বেছে নিন।',
                'cta_en' => 'See Schedules', 'cta_bn' => 'সময়সূচি দেখুন',
            ],
        ];

        foreach ($sliders as $i => $s) {
            Slider::updateOrCreate(
                ['title_en' => $s['title_en']],
                [
                    'title_bn' => $s['title_bn'],
                    'subtitle_en' => $s['sub_en'],
                    'subtitle_bn' => $s['sub_bn'],
                    'cta_label_en' => $s['cta_en'],
                    'cta_label_bn' => $s['cta_bn'],
                    'cta_url' => null,
                    'image' => '',
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }

        $pages = [
            [
                'slug' => 'privacy-policy',
                'title_en' => 'Privacy Policy', 'title_bn' => 'গোপনীয়তা নীতি',
                'content_en' => '<p>We collect only the information needed to schedule and deliver your care: your name, phone number, and any details you choose to add when booking. Appointment records are visible to chamber staff only.</p><p>We do not sell or share your information with third parties. Contact the chamber if you would like your details removed from our records.</p>',
                'content_bn' => '<p>আপনার চিকিৎসার সময়সূচি ও সেবা প্রদানের জন্য প্রয়োজনীয় তথ্যই কেবল আমরা সংগ্রহ করি — আপনার নাম, ফোন নম্বর এবং বুকিংয়ের সময় আপনি যে তথ্য দিতে চান তা। অ্যাপয়েন্টমেন্টের তথ্য কেবল চেম্বারের কর্মীরাই দেখতে পান।</p><p>আমরা আপনার তথ্য কোনো তৃতীয় পক্ষের কাছে বিক্রি বা হস্তান্তর করি না। রেকর্ড থেকে তথ্য মুছে ফেলতে চাইলে চেম্বারে যোগাযোগ করুন।</p>',
                'show_in_footer' => true,
            ],
            [
                'slug' => 'terms-of-use',
                'title_en' => 'Terms of Use', 'title_bn' => 'ব্যবহারের শর্তাবলি',
                'content_en' => '<p>The information published here is general health information. It does not replace an in-person consultation, and it must not be used to diagnose or treat a condition on your own.</p><p>Booking a slot reserves a serial number; it is not a payment and does not guarantee a specific consultation time if an emergency arises.</p>',
                'content_bn' => '<p>এখানে প্রকাশিত তথ্য সাধারণ স্বাস্থ্য তথ্য। এটি সরাসরি চিকিৎসকের পরামর্শের বিকল্প নয় এবং নিজে থেকে রোগ নির্ণয় বা চিকিৎসার জন্য ব্যবহার করা যাবে না।</p><p>সময় বুক করার অর্থ একটি সিরিয়াল নম্বর সংরক্ষণ; এটি কোনো পেমেন্ট নয় এবং জরুরি পরিস্থিতিতে নির্দিষ্ট সময়ে পরামর্শের নিশ্চয়তা দেয় না।</p>',
                'show_in_footer' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page + ['is_published' => true]);
        }

        $settings = [
            ['site_name_en', 'Prof. Dr. Ayesha Rahman', 'general'],
            ['site_name_bn', 'অধ্যাপক ডা. আয়েশা রহমান', 'general'],
            ['footer_note_en', 'Interventional cardiology consultation in Dhaka.', 'general'],
            ['footer_note_bn', 'ঢাকায় ইন্টারভেনশনাল কার্ডিওলজি পরামর্শ সেবা।', 'general'],
            ['contact_address_en', 'Plot 81, Block E, Bashundhara R/A, Dhaka 1229', 'contact'],
            ['contact_address_bn', 'প্লট ৮১, ব্লক ই, বসুন্ধরা আবাসিক এলাকা, ঢাকা ১২২৯', 'contact'],
            ['appointment_notice_en', 'Online serials close one hour before each sitting begins.', 'booking'],
            ['appointment_notice_bn', 'প্রতিটি বসার এক ঘণ্টা আগে অনলাইন সিরিয়াল বন্ধ হয়ে যায়।', 'booking'],
        ];

        foreach ($settings as [$key, $value, $group]) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group, 'type' => 'text']);
        }
    }
}
