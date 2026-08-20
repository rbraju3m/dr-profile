<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Publication;
use App\Models\Service;
use App\Models\SuccessStory;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->successStories();
        $this->postsAndCategories();
        $this->testimonials();
        $this->faqs();
        $this->publications();
        $this->gallery();
    }

    private function successStories(): void
    {
        $angio = Service::where('slug', 'coronary-angiogram-angioplasty')->first();
        $failure = Service::where('slug', 'heart-failure-clinic')->first();
        $htn = Service::where('slug', 'hypertension-management')->first();

        $stories = [
            [
                'title_en' => 'Back to the classroom eight days after a heart attack',
                'title_bn' => 'হার্ট অ্যাটাকের আট দিন পরেই আবার শ্রেণিকক্ষে',
                'patient' => 'Md. Shahidul Islam', 'age' => 54,
                'loc_en' => 'Mirpur, Dhaka', 'loc_bn' => 'মিরপুর, ঢাকা',
                'cond_en' => 'Acute anterior myocardial infarction with a fully blocked left anterior descending artery.',
                'cond_bn' => 'তীব্র অ্যান্টেরিয়র মায়োকার্ডিয়াল ইনফার্কশন, বাম দিকের প্রধান ধমনী সম্পূর্ণ বন্ধ।',
                'sum_en' => 'A school teacher reached the emergency room 90 minutes after his chest pain began. Primary angioplasty opened the artery the same night.',
                'sum_bn' => 'বুকে ব্যথা শুরুর ৯০ মিনিটের মধ্যে একজন স্কুলশিক্ষক জরুরি বিভাগে পৌঁছান। সেই রাতেই প্রাইমারি অ্যাঞ্জিওপ্লাস্টির মাধ্যমে ধমনী খুলে দেওয়া হয়।',
                'service' => $angio, 'days_ago' => 40, 'featured' => true,
            ],
            [
                'title_en' => 'Breathing easily again after two years of swelling',
                'title_bn' => 'দুই বছরের শরীর ফোলা শেষে আবার স্বাভাবিক শ্বাস',
                'patient' => 'Rehana Begum', 'age' => 61,
                'loc_en' => 'Narayanganj', 'loc_bn' => 'নারায়ণগঞ্জ',
                'cond_en' => 'Heart failure with reduced ejection fraction of 28 percent.',
                'cond_bn' => 'হৃদযন্ত্রের পাম্পিং ক্ষমতা ২৮ শতাংশে নেমে আসা হার্ট ফেইলিওর।',
                'sum_en' => 'Guideline-directed therapy, salt discipline and monthly review lifted her ejection fraction to 45 percent within a year.',
                'sum_bn' => 'নির্দেশিকা অনুযায়ী চিকিৎসা, লবণ নিয়ন্ত্রণ ও মাসিক পর্যালোচনায় এক বছরে তাঁর পাম্পিং ক্ষমতা ৪৫ শতাংশে উন্নীত হয়।',
                'service' => $failure, 'days_ago' => 90, 'featured' => true,
            ],
            [
                'title_en' => 'Five blood pressure tablets reduced to two',
                'title_bn' => 'রক্তচাপের পাঁচটি ওষুধ কমে দুইটিতে',
                'patient' => 'Anwar Hossain', 'age' => 47,
                'loc_en' => 'Uttara, Dhaka', 'loc_bn' => 'উত্তরা, ঢাকা',
                'cond_en' => 'Resistant hypertension caused by an undiagnosed adrenal adenoma.',
                'cond_bn' => 'অনির্ণীত অ্যাড্রিনাল অ্যাডেনোমার কারণে নিয়ন্ত্রণহীন উচ্চ রক্তচাপ।',
                'sum_en' => 'Looking for the cause rather than adding another drug found a treatable adrenal tumour.',
                'sum_bn' => 'আরেকটি ওষুধ যোগ না করে কারণ খোঁজায় ধরা পড়ে চিকিৎসাযোগ্য অ্যাড্রিনাল টিউমার।',
                'service' => $htn, 'days_ago' => 150, 'featured' => true,
            ],
            [
                'title_en' => 'A wedding attended, three weeks after a pacemaker',
                'title_bn' => 'পেসমেকারের তিন সপ্তাহ পরেই বিয়ের অনুষ্ঠানে',
                'patient' => 'Jahanara Khatun', 'age' => 72,
                'loc_en' => 'Cumilla', 'loc_bn' => 'কুমিল্লা',
                'cond_en' => 'Complete heart block causing repeated blackouts.',
                'cond_bn' => 'সম্পূর্ণ হার্ট ব্লকের কারণে বারবার জ্ঞান হারানো।',
                'sum_en' => 'A permanent pacemaker ended the falls that had kept her housebound for a year.',
                'sum_bn' => 'স্থায়ী পেসমেকার বসানোর পর এক বছর ধরে ঘরবন্দি করে রাখা পড়ে যাওয়ার সমস্যা দূর হয়।',
                'service' => null, 'days_ago' => 200, 'featured' => false,
            ],
            [
                'title_en' => 'Chest pain that turned out not to be the heart',
                'title_bn' => 'বুকে ব্যথা, অথচ কারণ হৃদযন্ত্র নয়',
                'patient' => 'Tanvir Ahmed', 'age' => 35,
                'loc_en' => 'Gazipur', 'loc_bn' => 'গাজীপুর',
                'cond_en' => 'Non-cardiac chest pain with severe health anxiety.',
                'cond_bn' => 'হৃদরোগ-বহির্ভূত বুকে ব্যথা এবং তীব্র স্বাস্থ্য উদ্বেগ।',
                'sum_en' => 'A normal stress test and a long conversation prevented an unnecessary angiogram.',
                'sum_bn' => 'স্বাভাবিক স্ট্রেস টেস্ট ও দীর্ঘ আলোচনার মাধ্যমে অপ্রয়োজনীয় অ্যাঞ্জিওগ্রাম এড়ানো গেছে।',
                'service' => null, 'days_ago' => 260, 'featured' => false,
            ],
            [
                'title_en' => 'Safe pregnancy with a repaired heart valve',
                'title_bn' => 'মেরামত করা হার্ট ভাল্ভ নিয়েও নিরাপদ মাতৃত্ব',
                'patient' => 'Nusrat Jahan', 'age' => 29,
                'loc_en' => 'Sylhet', 'loc_bn' => 'সিলেট',
                'cond_en' => 'Rheumatic mitral stenosis, previously treated with balloon valvuloplasty.',
                'cond_bn' => 'রিউম্যাটিক মাইট্রাল স্টেনোসিস, আগে বেলুন ভালভুলোপ্লাস্টি করা হয়েছিল।',
                'sum_en' => 'Shared care with her obstetrician carried her safely through to a term delivery.',
                'sum_bn' => 'প্রসূতি বিশেষজ্ঞের সঙ্গে যৌথ তত্ত্বাবধানে নিরাপদে পূর্ণ মেয়াদে সন্তান প্রসব সম্ভব হয়।',
                'service' => null, 'days_ago' => 320, 'featured' => false,
            ],
        ];

        foreach ($stories as $i => $s) {
            SuccessStory::updateOrCreate(
                ['slug' => Str::slug($s['title_en'])],
                [
                    'service_id' => $s['service']?->id,
                    'title_en' => $s['title_en'],
                    'title_bn' => $s['title_bn'],
                    'patient_name' => $s['patient'],
                    'patient_age' => $s['age'],
                    'patient_location_en' => $s['loc_en'],
                    'patient_location_bn' => $s['loc_bn'],
                    'condition_en' => $s['cond_en'],
                    'condition_bn' => $s['cond_bn'],
                    'summary_en' => $s['sum_en'],
                    'summary_bn' => $s['sum_bn'],
                    'content_en' => '<p>'.$s['sum_en'].'</p><p>'.$s['cond_en'].' The plan was explained to the family in full before anything was started, including what would happen if we chose to wait.</p><p>Follow-up continues at the chamber, and the story is published here with written consent from the patient.</p>',
                    'content_bn' => '<p>'.$s['sum_bn'].'</p><p>'.$s['cond_bn'].' চিকিৎসা শুরুর আগে পরিবারের সঙ্গে পুরো পরিকল্পনা আলোচনা করা হয়, অপেক্ষা করলে কী হতে পারত তাও জানানো হয়।</p><p>চেম্বারে নিয়মিত ফলো-আপ চলছে এবং রোগীর লিখিত সম্মতিক্রমে গল্পটি এখানে প্রকাশ করা হলো।</p>',
                    'treatment_date' => Carbon::today()->subDays($s['days_ago']),
                    'published_at' => Carbon::today()->subDays($s['days_ago'] - 5),
                    'is_featured' => $s['featured'],
                    'is_published' => true,
                    'sort_order' => $i,
                    'views' => random_int(120, 2400),
                ]
            );
        }
    }

    private function postsAndCategories(): void
    {
        $categories = [
            ['heart-health', 'Heart Health', 'হৃদরোগ ও সুস্থতা'],
            ['nutrition', 'Nutrition', 'পুষ্টি'],
            ['living-well', 'Living Well', 'সুস্থ জীবনযাপন'],
            ['announcements', 'Announcements', 'ঘোষণা'],
        ];

        foreach ($categories as $i => [$slug, $en, $bn]) {
            PostCategory::updateOrCreate(
                ['slug' => $slug],
                ['name_en' => $en, 'name_bn' => $bn, 'sort_order' => $i, 'is_active' => true]
            );
        }

        $heart = PostCategory::where('slug', 'heart-health')->first();
        $nutrition = PostCategory::where('slug', 'nutrition')->first();
        $living = PostCategory::where('slug', 'living-well')->first();
        $news = PostCategory::where('slug', 'announcements')->first();

        $posts = [
            // News
            ['news', $news, 'New cath lab opens at the Bashundhara chamber', 'বসুন্ধরা চেম্বারে নতুন ক্যাথ ল্যাব চালু',
                'A second catheterisation laboratory has been commissioned, cutting the wait for elective angioplasty from three weeks to five days.',
                'দ্বিতীয় ক্যাথেটারাইজেশন ল্যাব চালু হওয়ায় নির্ধারিত অ্যাঞ্জিওপ্লাস্টির অপেক্ষা তিন সপ্তাহ থেকে কমে পাঁচ দিনে নেমেছে।', 6, true],
            ['news', $news, 'Free heart screening camp served 340 patients in Cumilla', 'কুমিল্লায় বিনামূল্যে হৃদরোগ পরীক্ষায় ৩৪০ জন রোগী',
                'ECG, blood pressure and blood sugar checks were offered without charge over two days, with 41 patients referred for further assessment.',
                'দুই দিনে বিনামূল্যে ইসিজি, রক্তচাপ ও রক্তে শর্করা পরীক্ষা করা হয়; ৪১ জন রোগীকে আরও পরীক্ষার জন্য পাঠানো হয়েছে।', 24, false],
            ['news', $news, 'Research paper accepted by the Asian Cardiovascular Journal', 'এশিয়ান কার্ডিওভাস্কুলার জার্নালে গবেষণাপত্র গৃহীত',
                'A five-year review of primary angioplasty outcomes in 1,842 Bangladeshi patients has been accepted for publication.',
                '১,৮৪২ জন বাংলাদেশি রোগীর প্রাইমারি অ্যাঞ্জিওপ্লাস্টির পাঁচ বছরের ফলাফল পর্যালোচনা প্রকাশের জন্য গৃহীত হয়েছে।', 45, false],
            ['news', $news, 'Chamber timings updated for the winter season', 'শীত মৌসুমের জন্য চেম্বারের সময়সূচি হালনাগাদ',
                'Evening sittings at Dhanmondi now begin thirty minutes earlier. Existing appointments are unaffected.',
                'ধানমন্ডিতে সান্ধ্যকালীন সময় এখন আধা ঘণ্টা আগে শুরু হবে। পূর্বনির্ধারিত অ্যাপয়েন্টমেন্টে কোনো পরিবর্তন হবে না।', 70, false],

            // Events
            ['event', $news, 'World Heart Day public seminar', 'বিশ্ব হৃদরোগ দিবসের উন্মুক্ত সেমিনার',
                'An open session for patients and families on recognising a heart attack and what to do in the first hour.',
                'হার্ট অ্যাটাক চেনা এবং প্রথম এক ঘণ্টায় করণীয় নিয়ে রোগী ও পরিবারের জন্য উন্মুক্ত আলোচনা।', -14, true],
            ['event', $news, 'Free hypertension screening camp, Mirpur', 'মিরপুরে বিনামূল্যে উচ্চ রক্তচাপ পরীক্ষা শিবির',
                'Walk-in blood pressure checks and a fifteen-minute counselling slot with a cardiology trainee.',
                'সরাসরি এসে রক্তচাপ পরীক্ষা এবং কার্ডিওলজি প্রশিক্ষণার্থীর সঙ্গে পনেরো মিনিটের পরামর্শ।', -28, false],
            ['event', $news, 'Live webinar: statins, side effects and the facts', 'সরাসরি ওয়েবিনার: স্ট্যাটিন, পার্শ্বপ্রতিক্রিয়া ও বাস্তবতা',
                'An online question-and-answer session addressing the most common fears about cholesterol medication.',
                'কোলেস্টেরলের ওষুধ নিয়ে সবচেয়ে প্রচলিত ভয়গুলোর জবাবে অনলাইন প্রশ্নোত্তর পর্ব।', -7, false],
            ['event', $news, 'CPR training for school teachers', 'স্কুলশিক্ষকদের জন্য সিপিআর প্রশিক্ষণ',
                'A hands-on half-day workshop teaching chest compressions and AED use to secondary school staff.',
                'মাধ্যমিক বিদ্যালয়ের শিক্ষকদের জন্য বুকে চাপ প্রয়োগ ও এইইডি ব্যবহারের হাতে-কলমে অর্ধদিবস কর্মশালা।', 35, false],

            // Blog
            ['blog', $heart, 'Six signs of a heart attack that are not chest pain', 'হার্ট অ্যাটাকের ছয়টি লক্ষণ, যেগুলো বুকে ব্যথা নয়',
                'Jaw ache, sudden sweating and unexplained exhaustion send more people home from the emergency room than they should.',
                'চোয়ালে ব্যথা, হঠাৎ ঘাম আর অকারণ ক্লান্তি — এই লক্ষণগুলোর কারণে বহু রোগী জরুরি বিভাগ থেকে ফিরে যান, যা হওয়া উচিত নয়।', 3, true],
            ['blog', $nutrition, 'How much salt is actually in a plate of Bangladeshi food?', 'এক প্লেট বাঙালি খাবারে আসলে কতটা লবণ থাকে?',
                'Pickles, shutki and packaged snacks add up faster than the salt shaker on the table.',
                'আচার, শুঁটকি আর প্যাকেটজাত খাবার টেবিলের লবণদানির চেয়েও দ্রুত লবণের পরিমাণ বাড়িয়ে দেয়।', 11, true],
            ['blog', $living, 'Walking for your heart: how fast, how far, how often', 'হৃদযন্ত্রের জন্য হাঁটা: কত দ্রুত, কতদূর, কতবার',
                'The evidence points to brisk walking most days rather than heroic weekend efforts.',
                'সপ্তাহান্তের বিশাল পরিশ্রমের চেয়ে প্রায় প্রতিদিন দ্রুত হাঁটাই বেশি উপকারী — গবেষণা তাই বলে।', 19, false],
            ['blog', $heart, 'Do I really need a stent? Questions worth asking', 'আমার কি সত্যিই স্টেন্ট দরকার? যে প্রশ্নগুলো করা উচিত',
                'Stable angina and a heart attack are different situations, and the honest answer differs too.',
                'স্থিতিশীল অ্যানজাইনা আর হার্ট অ্যাটাক এক নয়, তাই সৎ উত্তরটিও ভিন্ন।', 27, false],
            ['blog', $nutrition, 'Cooking oil: what the labels do not tell you', 'রান্নার তেল: লেবেলে যা লেখা থাকে না',
                'Smoke point matters more than the marketing on the bottle.', 'বোতলের বিজ্ঞাপনের চেয়ে তেলের স্মোক পয়েন্ট অনেক বেশি গুরুত্বপূর্ণ।', 38, false],
            ['blog', $living, 'Sleep apnoea and the blood pressure that will not settle', 'স্লিপ অ্যাপনিয়া এবং নিয়ন্ত্রণে না আসা রক্তচাপ',
                'Loud snoring plus morning headaches plus resistant hypertension is a pattern worth investigating.',
                'জোরে নাক ডাকা, সকালে মাথাব্যথা আর নিয়ন্ত্রণহীন রক্তচাপ — এই ধরনটি পরীক্ষা করে দেখা জরুরি।', 52, false],
        ];

        foreach ($posts as $i => [$type, $category, $titleEn, $titleBn, $exEn, $exBn, $daysAgo, $featured]) {
            $isEvent = $type === 'event';
            // negative "days ago" means an upcoming event
            $when = Carbon::today()->subDays($daysAgo);

            Post::updateOrCreate(
                ['slug' => Str::slug($titleEn)],
                [
                    'type' => $type,
                    'post_category_id' => $category?->id,
                    'title_en' => $titleEn,
                    'title_bn' => $titleBn,
                    'excerpt_en' => $exEn,
                    'excerpt_bn' => $exBn,
                    'content_en' => '<p>'.$exEn.'</p><p>This is demonstration content seeded with the application. Replace it from the admin panel with the real article text, images and links.</p>',
                    'content_bn' => '<p>'.$exBn.'</p><p>এটি অ্যাপ্লিকেশনের সঙ্গে যুক্ত নমুনা বিষয়বস্তু। অ্যাডমিন প্যানেল থেকে প্রকৃত লেখা, ছবি ও লিংক দিয়ে এটি প্রতিস্থাপন করুন।</p>',
                    'event_start_at' => $isEvent ? $when->copy()->setTime(15, 0) : null,
                    'event_end_at' => $isEvent ? $when->copy()->setTime(18, 0) : null,
                    'event_venue_en' => $isEvent ? 'Auditorium, Evercare Hospital Dhaka' : null,
                    'event_venue_bn' => $isEvent ? 'অডিটোরিয়াম, এভারকেয়ার হাসপাতাল ঢাকা' : null,
                    'event_is_online' => $isEvent && str_contains($titleEn, 'webinar'),
                    'event_registration_url' => $isEvent ? 'https://example.com/register' : null,
                    'tags' => $type === 'blog' ? ['heart', 'prevention'] : null,
                    'reading_minutes' => $type === 'blog' ? random_int(3, 8) : null,
                    'is_featured' => $featured,
                    'is_published' => true,
                    'published_at' => $isEvent ? Carbon::today()->subDays(max(1, abs($daysAgo) - 20)) : $when,
                    'views' => random_int(80, 3200),
                ]
            );
        }
    }

    private function testimonials(): void
    {
        $rows = [
            ['Farida Yasmin', 'Schoolteacher', 'স্কুলশিক্ষক',
                'She drew my blocked artery on paper before the procedure so I would know exactly what was going to happen. I have never had a doctor do that.',
                'প্রসিডিওরের আগে তিনি কাগজে এঁকে আমার বন্ধ ধমনীটি দেখিয়েছিলেন, যাতে আমি বুঝতে পারি কী হতে যাচ্ছে। আগে কোনো ডাক্তার এভাবে বোঝাননি।', 5],
            ['Md. Rafiqul Islam', 'Retired banker', 'অবসরপ্রাপ্ত ব্যাংকার',
                'I came in on nine tablets a day. I left on four, and my pressure has been better ever since.',
                'দিনে নয়টি ওষুধ নিয়ে এসেছিলাম। চারটিতে নেমে এসেছি, আর রক্তচাপও আগের চেয়ে ভালো আছে।', 5],
            ['Shirin Akter', 'Homemaker', 'গৃহিণী',
                'My mother is 74 and deaf in one ear. The doctor moved her chair and spoke to her directly instead of talking to me about her.',
                'আমার মায়ের বয়স ৭৪, এক কানে শোনেন না। ডাক্তার নিজের চেয়ার সরিয়ে আমার সঙ্গে নয়, সরাসরি মায়ের সঙ্গেই কথা বলেছেন।', 5],
            ['Tanjil Hasan', 'Software engineer', 'সফটওয়্যার প্রকৌশলী',
                'Booked online at midnight, got a serial for Sunday evening, and was seen within ten minutes of the time given.',
                'রাত ১২টায় অনলাইনে বুক করে রবিবার সন্ধ্যার সিরিয়াল পেয়েছি, আর নির্ধারিত সময়ের দশ মিনিটের মধ্যেই ডাক পড়েছে।', 5],
            ['Nasima Khatun', 'Garment worker', 'পোশাক শ্রমিক',
                'I was worried about the cost. She told me which test could wait and which could not. That honesty mattered to me.',
                'খরচ নিয়ে দুশ্চিন্তায় ছিলাম। তিনি বলে দিয়েছেন কোন পরীক্ষা এখন না করলেও চলবে, কোনটি করতেই হবে। এই সততাটুকু আমার কাছে অনেক।', 5],
            ['Dr. Kamrul Ahsan', 'General physician', 'সাধারণ চিকিৎসক',
                'I refer my difficult hypertension patients to her. The letters that come back actually explain the reasoning.',
                'উচ্চ রক্তচাপের জটিল রোগীদের আমি তাঁর কাছে পাঠাই। ফিরতি চিঠিতে সিদ্ধান্তের যুক্তিটাও ব্যাখ্যা করা থাকে।', 5],
        ];

        foreach ($rows as $i => [$name, $titleEn, $titleBn, $enText, $bnText, $rating]) {
            Testimonial::updateOrCreate(
                ['patient_name' => $name],
                [
                    'patient_title_en' => $titleEn,
                    'patient_title_bn' => $titleBn,
                    'content_en' => $enText,
                    'content_bn' => $bnText,
                    'rating' => $rating,
                    'visited_on' => Carbon::today()->subDays(($i + 1) * 23),
                    'is_featured' => $i < 3,
                    'is_published' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }

    private function faqs(): void
    {
        $rows = [
            ['appointment', 'How do I book an appointment?', 'কীভাবে অ্যাপয়েন্টমেন্ট নেব?',
                'Use the booking form on this website — choose a chamber, a date and a free time slot. You will get a serial number immediately. You can also call the chamber number listed on the contact page.',
                'এই ওয়েবসাইটের বুকিং ফর্ম ব্যবহার করুন — চেম্বার, তারিখ ও খালি সময় বেছে নিন। সঙ্গে সঙ্গেই সিরিয়াল নম্বর পাবেন। চাইলে যোগাযোগ পাতায় দেওয়া চেম্বারের নম্বরেও কল করতে পারেন।'],
            ['appointment', 'How far in advance can I book?', 'কত দিন আগে বুক করা যায়?',
                'Online booking opens for the next thirty days. Beyond that, please call the chamber.',
                'অনলাইনে আগামী ত্রিশ দিনের জন্য বুকিং করা যায়। এর বেশি সময়ের জন্য চেম্বারে কল করুন।'],
            ['appointment', 'Can I change or cancel my appointment?', 'অ্যাপয়েন্টমেন্ট পরিবর্তন বা বাতিল করা যাবে?',
                'Yes. Call the chamber number with your serial number at least four hours before your slot so it can be offered to another patient.',
                'হ্যাঁ। নির্ধারিত সময়ের অন্তত চার ঘণ্টা আগে সিরিয়াল নম্বরসহ চেম্বারে কল করুন, যাতে সময়টি অন্য রোগীকে দেওয়া যায়।'],
            ['appointment', 'What should I bring with me?', 'সঙ্গে কী কী আনতে হবে?',
                'All previous reports, ECGs and discharge papers, plus the actual strips of every medicine you are taking now.',
                'পূর্ববর্তী সব রিপোর্ট, ইসিজি ও ছাড়পত্র এবং বর্তমানে যেসব ওষুধ খাচ্ছেন তার প্রকৃত পাতাগুলো।'],
            ['fees', 'What is the consultation fee?', 'পরামর্শ ফি কত?',
                'Fees differ by chamber and are listed on each chamber card. Follow-up within four weeks is charged at the reduced follow-up rate.',
                'চেম্বারভেদে ফি ভিন্ন এবং প্রতিটি চেম্বারের তথ্যে তা উল্লেখ করা আছে। চার সপ্তাহের মধ্যে ফলো-আপে কম হারে ফি নেওয়া হয়।'],
            ['fees', 'Do you accept card payment?', 'কার্ডে পেমেন্ট নেওয়া হয়?',
                'The hospital chambers accept cards and mobile financial services. The private chamber is cash only.',
                'হাসপাতালের চেম্বারগুলোতে কার্ড ও মোবাইল ব্যাংকিং গ্রহণ করা হয়। ব্যক্তিগত চেম্বারে কেবল নগদ।'],
            ['fees', 'Is the appointment fee refundable?', 'অ্যাপয়েন্টমেন্ট ফি ফেরতযোগ্য?',
                'No fee is collected online. You pay at the chamber counter on the day of your visit.',
                'অনলাইনে কোনো ফি নেওয়া হয় না। ভিজিটের দিন চেম্বারের কাউন্টারে পরিশোধ করবেন।'],
            ['treatment', 'How long does an angiogram take?', 'অ্যাঞ্জিওগ্রামে কতক্ষণ সময় লাগে?',
                'The procedure itself takes about twenty minutes through the wrist. Most patients go home the same evening.',
                'কব্জির মাধ্যমে করা প্রসিডিওরটিতে প্রায় বিশ মিনিট সময় লাগে। বেশিরভাগ রোগী সেই সন্ধ্যাতেই বাড়ি ফিরতে পারেন।'],
            ['treatment', 'Will I need to stop my medicines before a test?', 'পরীক্ষার আগে কি ওষুধ বন্ধ করতে হবে?',
                'Never stop anything on your own. Bring your medicine list and you will be told exactly what to hold and for how long.',
                'নিজে থেকে কোনো ওষুধ বন্ধ করবেন না। ওষুধের তালিকা নিয়ে আসুন, কোনটি কতদিন বন্ধ রাখতে হবে তা জানিয়ে দেওয়া হবে।'],
            ['general', 'Do you see patients from outside Dhaka?', 'ঢাকার বাইরের রোগী দেখা হয়?',
                'Yes. If you are travelling a long distance, book an earlier slot and mention it in the notes so your tests can be arranged on the same day.',
                'হ্যাঁ। দূর থেকে আসলে আগের দিকের একটি সময় বেছে নিন এবং নোটে তা উল্লেখ করুন, যাতে একই দিনে পরীক্ষাগুলোর ব্যবস্থা করা যায়।'],
            ['general', 'Is a video consultation available?', 'ভিডিও কনসালটেশন করা যায়?',
                'Follow-up video consultations can be arranged for patients already seen at the chamber. Call the chamber to request one.',
                'যাঁরা ইতিমধ্যে চেম্বারে দেখিয়েছেন, তাঁদের জন্য ফলো-আপ ভিডিও কনসালটেশনের ব্যবস্থা করা যায়। এর জন্য চেম্বারে কল করুন।'],
        ];

        foreach ($rows as $i => [$group, $qEn, $qBn, $aEn, $aBn]) {
            Faq::updateOrCreate(
                ['question_en' => $qEn],
                [
                    'group' => $group,
                    'question_bn' => $qBn,
                    'answer_en' => $aEn,
                    'answer_bn' => $aBn,
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }

    private function publications(): void
    {
        $rows = [
            ['journal', 'Five-year outcomes after primary percutaneous coronary intervention in a South Asian cohort', 'Rahman A, Chowdhury MK, Islam S', 'Asian Cardiovascular Journal', 2025, '18(2)', '104–113'],
            ['journal', 'Radial versus femoral access in acute myocardial infarction: a Bangladeshi single-centre experience', 'Rahman A, Hossain T', 'Bangladesh Heart Journal', 2023, '38(1)', '22–29'],
            ['journal', 'Prevalence of resistant hypertension in urban outpatient clinics of Dhaka', 'Rahman A, Begum N, Ali MR', 'Journal of Hypertension Research', 2022, '9(4)', '215–223'],
            ['conference', 'Door-to-balloon time reduction through pre-hospital ECG transmission', 'Rahman A', 'SAARC Cardiac Congress, Colombo', 2024, null, null],
            ['conference', 'Statin adherence and cultural beliefs: a qualitative study', 'Rahman A, Sultana F', 'Bangladesh Cardiac Society Annual Scientific Session', 2023, null, null],
            ['chapter', 'Managing coronary disease in low-resource settings', 'Rahman A', 'Textbook of Cardiology for South Asia, 2nd ed.', 2021, null, '431–458'],
            ['journal', 'Echocardiographic predictors of recovery in dilated cardiomyopathy', 'Rahman A, Karim R', 'Cardiology Today', 2020, '15(3)', '77–84'],
            ['thesis', 'Outcomes of thrombolysis versus primary angioplasty at a tertiary centre', 'Rahman A', 'National Institute of Cardiovascular Diseases', 2010, null, null],
        ];

        foreach ($rows as $i => [$type, $title, $authors, $venue, $year, $volume, $pages]) {
            Publication::updateOrCreate(
                ['title_en' => $title],
                [
                    'type' => $type,
                    'authors' => $authors,
                    'venue_en' => $venue,
                    'year' => $year,
                    'volume' => $volume,
                    'pages' => $pages,
                    'is_featured' => $i < 3,
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }

    private function gallery(): void
    {
        $albums = [
            ['chamber-and-facilities', 'Chamber & Facilities', 'চেম্বার ও সুবিধাসমূহ', 6],
            ['camps-and-outreach', 'Camps & Outreach', 'স্বাস্থ্য শিবির ও জনসেবা', 8],
            ['conferences-and-teaching', 'Conferences & Teaching', 'সম্মেলন ও শিক্ষাদান', 5],
        ];

        foreach ($albums as $i => [$slug, $en, $bn, $count]) {
            $album = GalleryAlbum::updateOrCreate(
                ['slug' => $slug],
                [
                    'title_en' => $en,
                    'title_bn' => $bn,
                    'event_date' => Carbon::today()->subMonths($i + 1),
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );

            $album->items()->delete();

            for ($n = 1; $n <= $count; $n++) {
                GalleryItem::create([
                    'gallery_album_id' => $album->id,
                    'type' => 'image',
                    'title_en' => $en.' — photo '.$n,
                    'title_bn' => $bn.' — ছবি '.$n,
                    'sort_order' => $n,
                    'is_active' => true,
                ]);
            }
        }

        $videos = GalleryAlbum::updateOrCreate(
            ['slug' => 'video-library'],
            ['title_en' => 'Video Library', 'title_bn' => 'ভিডিও লাইব্রেরি', 'sort_order' => 3, 'is_active' => true]
        );

        $videos->items()->delete();

        foreach ([
            ['What to do in the first hour of a heart attack', 'হার্ট অ্যাটাকের প্রথম এক ঘণ্টায় করণীয়'],
            ['How to measure blood pressure correctly at home', 'বাড়িতে সঠিকভাবে রক্তচাপ মাপার নিয়ম'],
            ['Life after a stent: the first three months', 'স্টেন্ট বসানোর পর: প্রথম তিন মাস'],
        ] as $n => [$en, $bn]) {
            GalleryItem::create([
                'gallery_album_id' => $videos->id,
                'type' => 'video',
                'title_en' => $en,
                'title_bn' => $bn,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sort_order' => $n,
                'is_active' => true,
            ]);
        }
    }
}
