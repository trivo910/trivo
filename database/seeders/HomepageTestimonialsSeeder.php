<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class HomepageTestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'title' => 'Parwez Alam',
                'role' => '',
                'quote' => 'Working with Trivo IT Solution transformed our business! Their tailored strategy boosted our online presence, SEO, and social media engagement. We saw a significant rise in website traffic and lead generation within months. Highly professional and detail-oriented team!',
            ],
            [
                'title' => 'Muhammad Imran',
                'role' => 'CEO & Founder',
                'quote' => 'We are happy with Trivo IT Solution. They are doing a great job for us far beyond our expectations. We contracted them with a goal to become an online supplier for travel agencies and they fulfilled it very nicely.',
            ],
            [
                'title' => 'Ronak Patel',
                'role' => 'Director, United Air Travel & Tours London UK',
                'quote' => 'I am very grateful to the Trivo IT Solution team and mostly to Mr. Kashif Khan He help us to select proper product as per our need . Proud to say my portal was developed and designed by Trivo IT Solution.',
            ],
            [
                'title' => 'Mustak Panwar',
                'role' => '',
                'quote' => 'I had a fantastic experience with Trivo. Their team was professional, creative, and responsive, tailoring a marketing strategy that boosted our engagement and sales. They provided clear communication and valuable insights throughout the process. Highly recommend them for any business looking to enhance their marketing efforts!',
            ],
        ];

        foreach ($testimonials as $index => $testimonial) {
            Testimonial::query()->updateOrCreate(
                [
                    'legacy_wp_id' => null,
                    'title' => $testimonial['title'],
                    'path' => null,
                ],
                [
                    'blocks' => [['type' => 'paragraph', 'text' => $testimonial['quote']]],
                    'details' => ['role' => $testimonial['role']],
                    'status' => 'published',
                    'sort' => $index + 1,
                    'published_at' => now(),
                    'sitemap_include' => false,
                ]
            );
        }
    }
}
