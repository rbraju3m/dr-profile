<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name_en' => 'Coronary Angiogram & Angioplasty',
                'name_bn' => 'করোনারি অ্যাঞ্জিওগ্রাম ও অ্যাঞ্জিওপ্লাস্টি',
                'short_en' => 'Imaging of the heart arteries and stenting of blockages through a wrist puncture.',
                'short_bn' => 'হাতের কব্জির সূক্ষ্ম ছিদ্রের মাধ্যমে হৃদযন্ত্রের ধমনী পরীক্ষা ও ব্লক খুলে স্টেন্ট বসানো।',
                'icon' => 'heart-pulse', 'fee' => null, 'featured' => true,
            ],
            [
                'name_en' => 'Heart Attack & Chest Pain Care',
                'name_bn' => 'হার্ট অ্যাটাক ও বুকে ব্যথার চিকিৎসা',
                'short_en' => 'Rapid assessment of chest pain, emergency angioplasty and post-infarct recovery planning.',
                'short_bn' => 'বুকে ব্যথার দ্রুত মূল্যায়ন, জরুরি অ্যাঞ্জিওপ্লাস্টি এবং হার্ট অ্যাটাক পরবর্তী পুনর্বাসন পরিকল্পনা।',
                'icon' => 'siren', 'fee' => null, 'featured' => true,
            ],
            [
                'name_en' => 'Hypertension Management',
                'name_bn' => 'উচ্চ রক্তচাপ ব্যবস্থাপনা',
                'short_en' => 'Finding the cause of stubborn blood pressure and simplifying the medicine list.',
                'short_bn' => 'নিয়ন্ত্রণে না আসা রক্তচাপের কারণ নির্ণয় এবং ওষুধের তালিকা সহজ করা।',
                'icon' => 'gauge', 'fee' => 1500, 'featured' => true,
            ],
            [
                'name_en' => 'Heart Failure Clinic',
                'name_bn' => 'হার্ট ফেইলিওর ক্লিনিক',
                'short_en' => 'Breathlessness and swelling assessed with echo, then treated with guideline-based therapy.',
                'short_bn' => 'শ্বাসকষ্ট ও শরীর ফোলা ইকো পরীক্ষার মাধ্যমে মূল্যায়ন করে নির্দেশিকা-ভিত্তিক চিকিৎসা।',
                'icon' => 'activity', 'fee' => 1500, 'featured' => true,
            ],
            [
                'name_en' => 'Arrhythmia & Palpitations',
                'name_bn' => 'অনিয়মিত হৃৎস্পন্দন ও ধড়ফড়',
                'short_en' => 'Holter and event monitoring to catch the rhythm that causes your symptoms.',
                'short_bn' => 'হোল্টার ও ইভেন্ট মনিটরিংয়ের মাধ্যমে উপসর্গের পেছনের হৃৎস্পন্দন শনাক্তকরণ।',
                'icon' => 'waves', 'fee' => 1500, 'featured' => true,
            ],
            [
                'name_en' => 'Echocardiography',
                'name_bn' => 'ইকোকার্ডিওগ্রাফি',
                'short_en' => 'Ultrasound of the heart valves, chambers and pumping function, reported the same day.',
                'short_bn' => 'হৃদযন্ত্রের ভাল্ভ, প্রকোষ্ঠ ও পাম্পিং ক্ষমতার আল্ট্রাসাউন্ড পরীক্ষা, একই দিনে রিপোর্ট।',
                'icon' => 'scan-heart', 'fee' => 3000, 'featured' => true,
            ],
            [
                'name_en' => 'Pacemaker Implantation',
                'name_bn' => 'পেসমেকার স্থাপন',
                'short_en' => 'Permanent pacemakers for slow heart rates, with long-term device follow-up.',
                'short_bn' => 'ধীর হৃৎস্পন্দনের জন্য স্থায়ী পেসমেকার স্থাপন এবং দীর্ঘমেয়াদি ফলো-আপ।',
                'icon' => 'cpu', 'fee' => null, 'featured' => false,
            ],
            [
                'name_en' => 'Preventive Heart Check-up',
                'name_bn' => 'হৃদরোগ প্রতিরোধমূলক পরীক্ষা',
                'short_en' => 'Risk scoring, lipid review and a realistic plan for people with a family history.',
                'short_bn' => 'ঝুঁকি নির্ণয়, লিপিড পর্যালোচনা এবং পারিবারিক ইতিহাস থাকলে বাস্তবসম্মত পরিকল্পনা।',
                'icon' => 'shield-check', 'fee' => 2000, 'featured' => true,
            ],
            [
                'name_en' => 'Diabetes & Heart Risk',
                'name_bn' => 'ডায়াবেটিস ও হৃদরোগ ঝুঁকি',
                'short_en' => 'Joint management of diabetes and cardiac risk, coordinated with your physician.',
                'short_bn' => 'আপনার চিকিৎসকের সঙ্গে সমন্বয় করে ডায়াবেটিস ও হৃদরোগ ঝুঁকির যৌথ ব্যবস্থাপনা।',
                'icon' => 'droplet', 'fee' => 1500, 'featured' => false,
            ],
            [
                'name_en' => 'Pre-operative Cardiac Clearance',
                'name_bn' => 'অস্ত্রোপচারপূর্ব হৃদরোগ ছাড়পত্র',
                'short_en' => 'Fitness assessment before non-cardiac surgery, with a written risk opinion.',
                'short_bn' => 'হৃদরোগ-বহির্ভূত অস্ত্রোপচারের আগে শারীরিক সক্ষমতা মূল্যায়ন ও লিখিত ঝুঁকি মতামত।',
                'icon' => 'clipboard-check', 'fee' => 2000, 'featured' => false,
            ],
        ];

        foreach ($services as $i => $s) {
            Service::updateOrCreate(
                ['slug' => Str::slug($s['name_en'])],
                [
                    'name_en' => $s['name_en'],
                    'name_bn' => $s['name_bn'],
                    'short_description_en' => $s['short_en'],
                    'short_description_bn' => $s['short_bn'],
                    'description_en' => '<p>'.$s['short_en'].'</p><p>Every consultation begins with your history and an explanation of what the test can and cannot tell us. Reports are discussed with you directly, and a printed summary goes home with you.</p>',
                    'description_bn' => '<p>'.$s['short_bn'].'</p><p>প্রতিটি পরামর্শ শুরু হয় আপনার রোগের ইতিহাস শোনার মাধ্যমে এবং কোন পরীক্ষা কী জানাতে পারে বা পারে না তা ব্যাখ্যা করে। রিপোর্ট আপনার সঙ্গে সরাসরি আলোচনা করা হয় এবং একটি মুদ্রিত সারসংক্ষেপ আপনাকে দেওয়া হয়।</p>',
                    'icon' => $s['icon'],
                    'fee' => $s['fee'],
                    'is_featured' => $s['featured'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }
}
