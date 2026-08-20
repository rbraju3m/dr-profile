<?php

namespace Database\Seeders;

use App\Models\Credential;
use App\Models\DoctorProfile;
use Illuminate\Database\Seeder;

class DoctorProfileSeeder extends Seeder
{
    public function run(): void
    {
        DoctorProfile::query()->delete();

        DoctorProfile::create([
            'name_en' => 'Ayesha Rahman',
            'name_bn' => 'আয়েশা রহমান',
            'title_en' => 'Prof. Dr.',
            'title_bn' => 'অধ্যাপক ডা.',
            'designation_en' => 'Senior Consultant, Interventional Cardiology',
            'designation_bn' => 'সিনিয়র কনসালট্যান্ট, ইন্টারভেনশনাল কার্ডিওলজি',
            'tagline_en' => 'Caring for your heart with clarity, patience and evidence.',
            'tagline_bn' => 'স্বচ্ছতা, ধৈর্য ও প্রমাণভিত্তিক চিকিৎসায় আপনার হৃদয়ের যত্ন।',
            'degrees_en' => 'MBBS (DMC), FCPS (Medicine), MD (Cardiology), FACC (USA)',
            'degrees_bn' => 'এমবিবিএস (ঢামেক), এফসিপিএস (মেডিসিন), এমডি (কার্ডিওলজি), এফএসিসি (ইউএসএ)',
            'short_bio_en' => 'Prof. Dr. Ayesha Rahman is an interventional cardiologist with 22 years of clinical practice in Dhaka. She has performed more than 6,000 coronary procedures and leads the cardiac catheterisation programme at her primary hospital.',
            'short_bio_bn' => 'অধ্যাপক ডা. আয়েশা রহমান একজন ইন্টারভেনশনাল কার্ডিওলজিস্ট, ঢাকায় যাঁর ২২ বছরের চিকিৎসা অভিজ্ঞতা রয়েছে। তিনি ছয় হাজারেরও বেশি করোনারি প্রসিডিওর সম্পন্ন করেছেন এবং নিজ হাসপাতালের ক্যাথ ল্যাব কার্যক্রমের নেতৃত্ব দিচ্ছেন।',
            'bio_en' => "<p>Prof. Dr. Ayesha Rahman completed her MBBS at Dhaka Medical College in 2001, followed by FCPS in Medicine and an MD in Cardiology from the National Institute of Cardiovascular Diseases. She trained in interventional cardiology at the Royal Brompton Hospital, London, and remains a Fellow of the American College of Cardiology.</p><p>Over the past two decades she has built a practice around one idea: a patient who understands their own heart makes better decisions than one who is merely instructed. Consultations run long by design, and every angiogram is explained on screen before any intervention is discussed.</p><p>She teaches postgraduate cardiology trainees, chairs her hospital's clinical audit committee, and has published widely on outcomes after primary angioplasty in South Asian populations.</p>",
            'bio_bn' => '<p>অধ্যাপক ডা. আয়েশা রহমান ২০০১ সালে ঢাকা মেডিকেল কলেজ থেকে এমবিবিএস সম্পন্ন করেন। এরপর মেডিসিনে এফসিপিএস এবং জাতীয় হৃদরোগ ইনস্টিটিউট থেকে কার্ডিওলজিতে এমডি ডিগ্রি অর্জন করেন। লন্ডনের রয়্যাল ব্রম্পটন হাসপাতালে ইন্টারভেনশনাল কার্ডিওলজিতে প্রশিক্ষণ নেন এবং বর্তমানে আমেরিকান কলেজ অব কার্ডিওলজির ফেলো।</p><p>গত দুই দশকে তিনি একটি বিশ্বাসকে কেন্দ্র করে তাঁর চিকিৎসাসেবা গড়ে তুলেছেন — যে রোগী নিজের হৃদযন্ত্র সম্পর্কে বোঝেন, তিনি নির্দেশ মেনে চলা রোগীর চেয়ে ভালো সিদ্ধান্ত নিতে পারেন। তাই প্রতিটি পরামর্শে সময় দেওয়া হয় এবং কোনো চিকিৎসার সিদ্ধান্তের আগে অ্যাঞ্জিওগ্রামের ফলাফল রোগীকে পর্দায় দেখিয়ে বুঝিয়ে বলা হয়।</p><p>তিনি স্নাতকোত্তর কার্ডিওলজি শিক্ষার্থীদের পাঠদান করেন, হাসপাতালের ক্লিনিক্যাল অডিট কমিটির সভাপতি এবং দক্ষিণ এশীয় জনগোষ্ঠীতে প্রাইমারি অ্যাঞ্জিওপ্লাস্টির ফলাফল নিয়ে বহু গবেষণাপত্র প্রকাশ করেছেন।</p>',
            'philosophy_en' => '<p>Nobody should leave a cardiology chamber unsure of what is wrong with them. I explain the diagnosis in plain language, show you the images, name the alternatives — including doing nothing — and let you decide with your family. Medication is reviewed at every visit so that nobody stays on a drug they no longer need.</p>',
            'philosophy_bn' => '<p>কোনো রোগী যেন নিজের সমস্যা না বুঝে চেম্বার থেকে ফিরে না যান। আমি সহজ ভাষায় রোগ নির্ণয় ব্যাখ্যা করি, ছবি দেখাই, সম্ভাব্য সব বিকল্প — এমনকি চিকিৎসা না নেওয়ার বিকল্পটিও — জানাই এবং পরিবারের সঙ্গে আলোচনা করে সিদ্ধান্ত নেওয়ার সুযোগ দিই। প্রতিটি ভিজিটে ওষুধের তালিকা পর্যালোচনা করা হয়, যাতে অপ্রয়োজনীয় ওষুধ কেউ চালিয়ে না যান।</p>',
            'gender' => 'female',
            'experience_years' => 22,
            'bmdc_reg_no' => 'A-28401',
            'languages_en' => 'Bangla, English, Hindi',
            'languages_bn' => 'বাংলা, ইংরেজি, হিন্দি',
            'email' => 'chamber@drayesharahman.test',
            'phone' => '+880 1711 000000',
            'hotline' => '10666',
            'whatsapp' => '+8801711000000',
            'facebook_url' => 'https://facebook.com/',
            'youtube_url' => 'https://youtube.com/',
            'linkedin_url' => 'https://linkedin.com/',
            'meta_title_en' => 'Prof. Dr. Ayesha Rahman — Interventional Cardiologist, Dhaka',
            'meta_title_bn' => 'অধ্যাপক ডা. আয়েশা রহমান — ইন্টারভেনশনাল কার্ডিওলজিস্ট, ঢাকা',
            'meta_description_en' => 'Book an appointment with Prof. Dr. Ayesha Rahman, interventional cardiologist in Dhaka. Chamber schedules, fees and online booking.',
            'meta_description_bn' => 'ঢাকার ইন্টারভেনশনাল কার্ডিওলজিস্ট অধ্যাপক ডা. আয়েশা রহমানের অ্যাপয়েন্টমেন্ট নিন। চেম্বারের সময়সূচি, ফি ও অনলাইন বুকিং।',
        ]);

        Credential::query()->delete();

        $rows = [
            ['education', 'MBBS', 'এমবিবিএস', 'Dhaka Medical College', 'ঢাকা মেডিকেল কলেজ', 1995, 2001],
            ['education', 'FCPS (Medicine)', 'এফসিপিএস (মেডিসিন)', 'Bangladesh College of Physicians & Surgeons', 'বাংলাদেশ কলেজ অব ফিজিশিয়ানস অ্যান্ড সার্জনস', 2003, 2006],
            ['education', 'MD (Cardiology)', 'এমডি (কার্ডিওলজি)', 'National Institute of Cardiovascular Diseases', 'জাতীয় হৃদরোগ ইনস্টিটিউট', 2007, 2010],
            ['training', 'Fellowship in Interventional Cardiology', 'ইন্টারভেনশনাল কার্ডিওলজিতে ফেলোশিপ', 'Royal Brompton Hospital, London', 'রয়্যাল ব্রম্পটন হাসপাতাল, লন্ডন', 2011, 2013],
            ['training', 'Advanced Structural Heart Intervention', 'অ্যাডভান্সড স্ট্রাকচারাল হার্ট ইন্টারভেনশন', 'Mount Sinai Hospital, New York', 'মাউন্ট সাইনাই হাসপাতাল, নিউইয়র্ক', 2016, 2016],
            ['experience', 'Senior Consultant & Head, Cath Lab', 'সিনিয়র কনসালট্যান্ট ও প্রধান, ক্যাথ ল্যাব', 'Evercare Hospital Dhaka', 'এভারকেয়ার হাসপাতাল ঢাকা', 2018, null],
            ['experience', 'Associate Professor of Cardiology', 'কার্ডিওলজির সহযোগী অধ্যাপক', 'National Institute of Cardiovascular Diseases', 'জাতীয় হৃদরোগ ইনস্টিটিউট', 2014, 2018],
            ['experience', 'Consultant Cardiologist', 'কনসালট্যান্ট কার্ডিওলজিস্ট', 'Square Hospitals Ltd.', 'স্কয়ার হাসপাতাল লিমিটেড', 2010, 2014],
            ['award', 'Best Clinical Research Paper', 'শ্রেষ্ঠ ক্লিনিক্যাল গবেষণা পত্র', 'Bangladesh Cardiac Society', 'বাংলাদেশ কার্ডিয়াক সোসাইটি', 2021, 2021],
            ['award', 'Distinguished Teacher Award', 'বিশিষ্ট শিক্ষক সম্মাননা', 'BSMMU', 'বিএসএমএমইউ', 2019, 2019],
            ['membership', 'Fellow, American College of Cardiology', 'ফেলো, আমেরিকান কলেজ অব কার্ডিওলজি', 'ACC, USA', 'এসিসি, যুক্তরাষ্ট্র', 2015, null],
            ['membership', 'Life Member', 'আজীবন সদস্য', 'Bangladesh Cardiac Society', 'বাংলাদেশ কার্ডিয়াক সোসাইটি', 2011, null],
            ['certification', 'Advanced Cardiac Life Support Instructor', 'অ্যাডভান্সড কার্ডিয়াক লাইফ সাপোর্ট প্রশিক্ষক', 'American Heart Association', 'আমেরিকান হার্ট অ্যাসোসিয়েশন', 2017, null],
        ];

        foreach ($rows as $i => [$type, $titleEn, $titleBn, $orgEn, $orgBn, $start, $end]) {
            Credential::create([
                'type' => $type,
                'title_en' => $titleEn,
                'title_bn' => $titleBn,
                'organization_en' => $orgEn,
                'organization_bn' => $orgBn,
                'start_year' => $start,
                'end_year' => $end,
                'is_current' => $end === null,
                'sort_order' => $i,
            ]);
        }
    }
}
