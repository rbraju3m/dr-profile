<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines — বাংলা
    |--------------------------------------------------------------------------
    |
    | The framework resolves these by rule name, so nothing in this repository
    | names them and TranslationUsageTest excludes the file for that reason.
    |
    | Every :placeholder is part of the message and is substituted at runtime —
    | translating one, or dropping it, leaves a sentence with a hole in it.
    | :attribute is filled from the attributes() method on the form request, or
    | from the `attributes` array at the foot of this file.
    |
    */

    'accepted' => ':attribute গ্রহণ করা আবশ্যক।',
    'accepted_if' => ':other যখন :value হয়, তখন :attribute গ্রহণ করা আবশ্যক।',
    'active_url' => ':attribute একটি বৈধ URL হতে হবে।',
    'after' => ':attribute :date-এর পরের একটি তারিখ হতে হবে।',
    'after_or_equal' => ':attribute :date বা তার পরের একটি তারিখ হতে হবে।',
    'alpha' => ':attribute-এ কেবল অক্ষর থাকতে পারবে।',
    'alpha_dash' => ':attribute-এ কেবল অক্ষর, সংখ্যা, ড্যাশ ও আন্ডারস্কোর থাকতে পারবে।',
    'alpha_num' => ':attribute-এ কেবল অক্ষর ও সংখ্যা থাকতে পারবে।',
    'any_of' => ':attribute সঠিক নয়।',
    'array' => ':attribute একটি অ্যারে হতে হবে।',
    'array_keys' => ':attribute-এ কেবল এই কী-গুলো থাকতে পারবে: :values।',
    'ascii' => ':attribute-এ কেবল একক-বাইটের অক্ষর, সংখ্যা ও চিহ্ন থাকতে পারবে।',
    'base64' => ':attribute একটি বৈধ Base64 স্ট্রিং হতে হবে।',
    'before' => ':attribute :date-এর আগের একটি তারিখ হতে হবে।',
    'before_or_equal' => ':attribute :date বা তার আগের একটি তারিখ হতে হবে।',
    'between' => [
        'array' => ':attribute-এ :min থেকে :max টি আইটেম থাকতে হবে।',
        'file' => ':attribute :min থেকে :max কিলোবাইটের মধ্যে হতে হবে।',
        'numeric' => ':attribute :min থেকে :max-এর মধ্যে হতে হবে।',
        'string' => ':attribute :min থেকে :max অক্ষরের মধ্যে হতে হবে।',
    ],
    'boolean' => ':attribute সত্য বা মিথ্যা হতে হবে।',
    'can' => ':attribute-এ অননুমোদিত মান রয়েছে।',
    'confirmed' => ':attribute নিশ্চিতকরণের সঙ্গে মিলছে না।',
    'contains' => ':attribute-এ একটি আবশ্যক মান নেই।',
    'current_password' => 'পাসওয়ার্ডটি সঠিক নয়।',
    'date' => ':attribute একটি বৈধ তারিখ হতে হবে।',
    'date_equals' => ':attribute :date তারিখের সমান হতে হবে।',
    'date_format' => ':attribute :format ফরম্যাটের সঙ্গে মিলতে হবে।',
    'decimal' => ':attribute-এ :decimal ঘর দশমিক থাকতে হবে।',
    'declined' => ':attribute প্রত্যাখ্যান করা আবশ্যক।',
    'declined_if' => ':other যখন :value হয়, তখন :attribute প্রত্যাখ্যান করা আবশ্যক।',
    'different' => ':attribute এবং :other আলাদা হতে হবে।',
    'digits' => ':attribute :digits অঙ্কের হতে হবে।',
    'digits_between' => ':attribute :min থেকে :max অঙ্কের মধ্যে হতে হবে।',
    'dimensions' => ':attribute ছবির মাপ সঠিক নয়।',
    'distinct' => ':attribute-এ একই মান একাধিকবার রয়েছে।',
    'doesnt_contain' => ':attribute-এ এগুলোর কোনোটি থাকতে পারবে না: :values।',
    'doesnt_end_with' => ':attribute এগুলোর কোনোটি দিয়ে শেষ হতে পারবে না: :values।',
    'doesnt_start_with' => ':attribute এগুলোর কোনোটি দিয়ে শুরু হতে পারবে না: :values।',
    'email' => ':attribute একটি বৈধ ইমেইল ঠিকানা হতে হবে।',
    'encoding' => ':attribute :encoding-এ এনকোড করা হতে হবে।',
    'ends_with' => ':attribute এগুলোর কোনো একটি দিয়ে শেষ হতে হবে: :values।',
    'enum' => 'নির্বাচিত :attribute সঠিক নয়।',
    'exists' => 'নির্বাচিত :attribute সঠিক নয়।',
    'extensions' => ':attribute-এর এক্সটেনশন এগুলোর একটি হতে হবে: :values।',
    'file' => ':attribute একটি ফাইল হতে হবে।',
    'filled' => ':attribute পূরণ করা আবশ্যক।',
    'gt' => [
        'array' => ':attribute-এ :value টির বেশি আইটেম থাকতে হবে।',
        'file' => ':attribute :value কিলোবাইটের বেশি হতে হবে।',
        'numeric' => ':attribute :value-এর বেশি হতে হবে।',
        'string' => ':attribute :value অক্ষরের বেশি হতে হবে।',
    ],
    'gte' => [
        'array' => ':attribute-এ :value টি বা তার বেশি আইটেম থাকতে হবে।',
        'file' => ':attribute :value কিলোবাইট বা তার বেশি হতে হবে।',
        'numeric' => ':attribute :value বা তার বেশি হতে হবে।',
        'string' => ':attribute :value অক্ষর বা তার বেশি হতে হবে।',
    ],
    'hex_color' => ':attribute একটি বৈধ হেক্সাডেসিমেল রঙ হতে হবে।',
    'image' => ':attribute একটি ছবি হতে হবে।',
    'in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'in_array' => ':attribute :other-এর মধ্যে থাকতে হবে।',
    'in_array_keys' => ':attribute-এ এগুলোর অন্তত একটি কী থাকতে হবে: :values।',
    'integer' => ':attribute একটি পূর্ণসংখ্যা হতে হবে।',
    'ip' => ':attribute একটি বৈধ IP ঠিকানা হতে হবে।',
    'ipv4' => ':attribute একটি বৈধ IPv4 ঠিকানা হতে হবে।',
    'ipv6' => ':attribute একটি বৈধ IPv6 ঠিকানা হতে হবে।',
    'json' => ':attribute একটি বৈধ JSON স্ট্রিং হতে হবে।',
    'list' => ':attribute একটি তালিকা হতে হবে।',
    'lowercase' => ':attribute ছোট হাতের অক্ষরে হতে হবে।',
    'lt' => [
        'array' => ':attribute-এ :value টির কম আইটেম থাকতে হবে।',
        'file' => ':attribute :value কিলোবাইটের কম হতে হবে।',
        'numeric' => ':attribute :value-এর কম হতে হবে।',
        'string' => ':attribute :value অক্ষরের কম হতে হবে।',
    ],
    'lte' => [
        'array' => ':attribute-এ :value টির বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute :value কিলোবাইট বা তার কম হতে হবে।',
        'numeric' => ':attribute :value বা তার কম হতে হবে।',
        'string' => ':attribute :value অক্ষর বা তার কম হতে হবে।',
    ],
    'mac_address' => ':attribute একটি বৈধ MAC ঠিকানা হতে হবে।',
    'max' => [
        'array' => ':attribute-এ :max টির বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute :max কিলোবাইটের বেশি হতে পারবে না।',
        'numeric' => ':attribute :max-এর বেশি হতে পারবে না।',
        'string' => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
    ],
    'max_digits' => ':attribute :max অঙ্কের বেশি হতে পারবে না।',
    'mimes' => ':attribute এই ধরনের ফাইল হতে হবে: :values।',
    'mimetypes' => ':attribute এই ধরনের ফাইল হতে হবে: :values।',
    'min' => [
        'array' => ':attribute-এ অন্তত :min টি আইটেম থাকতে হবে।',
        'file' => ':attribute অন্তত :min কিলোবাইট হতে হবে।',
        'numeric' => ':attribute অন্তত :min হতে হবে।',
        'string' => ':attribute অন্তত :min অক্ষরের হতে হবে।',
    ],
    'min_digits' => ':attribute অন্তত :min অঙ্কের হতে হবে।',
    'missing' => ':attribute থাকা যাবে না।',
    'missing_if' => ':other যখন :value হয়, তখন :attribute থাকা যাবে না।',
    'missing_unless' => ':other :value না হলে :attribute থাকা যাবে না।',
    'missing_with' => ':values থাকলে :attribute থাকা যাবে না।',
    'missing_with_all' => ':values থাকলে :attribute থাকা যাবে না।',
    'multiple_of' => ':attribute :value-এর গুণিতক হতে হবে।',
    'not_in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'not_regex' => ':attribute-এর গঠন সঠিক নয়।',
    'numeric' => ':attribute একটি সংখ্যা হতে হবে।',
    'password' => [
        'letters' => ':attribute-এ অন্তত একটি অক্ষর থাকতে হবে।',
        'mixed' => ':attribute-এ অন্তত একটি বড় হাতের ও একটি ছোট হাতের অক্ষর থাকতে হবে।',
        'numbers' => ':attribute-এ অন্তত একটি সংখ্যা থাকতে হবে।',
        'symbols' => ':attribute-এ অন্তত একটি বিশেষ চিহ্ন থাকতে হবে।',
        'uncompromised' => 'এই :attribute একটি তথ্য ফাঁসে পাওয়া গেছে। অনুগ্রহ করে অন্য একটি :attribute বেছে নিন।',
    ],
    'present' => ':attribute থাকা আবশ্যক।',
    'present_if' => ':other যখন :value হয়, তখন :attribute থাকা আবশ্যক।',
    'present_unless' => ':other :value না হলে :attribute থাকা আবশ্যক।',
    'present_with' => ':values থাকলে :attribute থাকা আবশ্যক।',
    'present_with_all' => ':values থাকলে :attribute থাকা আবশ্যক।',
    'prohibited' => ':attribute দেওয়া যাবে না।',
    'prohibited_if' => ':other যখন :value হয়, তখন :attribute দেওয়া যাবে না।',
    'prohibited_if_accepted' => ':other গ্রহণ করা হলে :attribute দেওয়া যাবে না।',
    'prohibited_if_declined' => ':other প্রত্যাখ্যান করা হলে :attribute দেওয়া যাবে না।',
    'prohibited_unless' => ':other :values-এর মধ্যে না থাকলে :attribute দেওয়া যাবে না।',
    'prohibits' => ':attribute থাকলে :other দেওয়া যাবে না।',
    'regex' => ':attribute-এর গঠন সঠিক নয়।',
    'required' => ':attribute পূরণ করা আবশ্যক।',
    'required_array_keys' => ':attribute-এ এগুলোর জন্য মান থাকতে হবে: :values।',
    'required_if' => ':other যখন :value হয়, তখন :attribute পূরণ করা আবশ্যক।',
    'required_if_accepted' => ':other গ্রহণ করা হলে :attribute পূরণ করা আবশ্যক।',
    'required_if_declined' => ':other প্রত্যাখ্যান করা হলে :attribute পূরণ করা আবশ্যক।',
    'required_unless' => ':other :values-এর মধ্যে না থাকলে :attribute পূরণ করা আবশ্যক।',
    'required_with' => ':values থাকলে :attribute পূরণ করা আবশ্যক।',
    'required_with_all' => ':values থাকলে :attribute পূরণ করা আবশ্যক।',
    'required_without' => ':values না থাকলে :attribute পূরণ করা আবশ্যক।',
    'required_without_all' => ':values-এর কোনোটিই না থাকলে :attribute পূরণ করা আবশ্যক।',
    'same' => ':attribute এবং :other একই হতে হবে।',
    'size' => [
        'array' => ':attribute-এ :size টি আইটেম থাকতে হবে।',
        'file' => ':attribute :size কিলোবাইট হতে হবে।',
        'numeric' => ':attribute :size হতে হবে।',
        'string' => ':attribute :size অক্ষরের হতে হবে।',
    ],
    'starts_with' => ':attribute এগুলোর কোনো একটি দিয়ে শুরু হতে হবে: :values।',
    'string' => ':attribute একটি লেখা হতে হবে।',
    'timezone' => ':attribute একটি বৈধ টাইমজোন হতে হবে।',
    'unique' => 'এই :attribute ইতিমধ্যে ব্যবহার করা হয়েছে।',
    'uploaded' => ':attribute আপলোড করা যায়নি।',
    'uppercase' => ':attribute বড় হাতের অক্ষরে হতে হবে।',
    'url' => ':attribute একটি বৈধ URL হতে হবে।',
    'ulid' => ':attribute একটি বৈধ ULID হতে হবে।',
    'uuid' => ':attribute একটি বৈধ UUID হতে হবে।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | This project keeps its own wording in validation_custom.php, under each
    | locale, and names those keys explicitly — so this stays empty.
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
