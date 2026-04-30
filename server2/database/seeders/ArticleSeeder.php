<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Article::query()->each(function ($article) {
            $article->clearMediaCollection('featured_image');
            $article->delete();
        });

        $articles = [
            [
                'title' => [
                    'ar' => 'كيفية الحفاظ على مكيف الهواء في الصيف',
                    'en' => 'How to Maintain Your AC During Summer',
                ],
                'excerpt' => [
                    'ar' => 'نصائح هامة وبسيطة للحفاظ على كفاءة مكيف الهواء الخاص بك خلال أشهر الصيف الحارة وتقليل استهلاك الكهرباء.',
                    'en' => 'Important and simple tips to maintain the efficiency of your AC during hot summer months and reduce electricity consumption.',
                ],
                'body' => [
                    'ar' => '<h3>تنظيف الفلاتر بانتظام</h3><p>يعد تنظيف فلاتر المكيف بانتظام من أهم الخطوات لضمان تبريد فعال وتقليل استهلاك الطاقة.</p><h3>ضبط درجة الحرارة المناسبة</h3><p>ينصح بضبط المكيف على درجة حرارة 24 مئوية للحصول على أفضل تبريد مع استهلاك معقول للكهرباء.</p>',
                    'en' => '<h3>Clean Filters Regularly</h3><p>Cleaning AC filters regularly is one of the most important steps to ensure efficient cooling and reduce energy consumption.</p><h3>Set the Right Temperature</h3><p>It is recommended to set the AC to 24 degrees Celsius for optimal cooling with reasonable electricity consumption.</p>',
                ],
                'meta_title' => [
                    'ar' => 'أفضل النصائح لصيانة المكيف في الصيف',
                    'en' => 'Best Tips for AC Maintenance in Summer',
                ],
                'meta_description' => [
                    'ar' => 'تعرف على أفضل الطرق للحفاظ على مكيف الهواء وزيادة كفاءته في فصل الصيف.',
                    'en' => 'Learn the best ways to maintain your AC and increase its efficiency during summer.',
                ],
                'status' => 1,
                'is_featured' => true,
                'published_at' => now(),
            ],
            [
                'title' => [
                    'ar' => 'أخطاء شائعة في السباكة المنزلية وكيفية تجنبها',
                    'en' => 'Common Home Plumbing Mistakes and How to Avoid Them',
                ],
                'excerpt' => [
                    'ar' => 'تجنب هذه الأخطاء الشائعة في السباكة التي قد تكلفك الكثير من المال والوقت في إصلاحها.',
                    'en' => 'Avoid these common plumbing mistakes that can cost you a lot of time and money to fix.',
                ],
                'body' => [
                    'ar' => '<p>العديد من أصحاب المنازل يقعون في أخطاء بسيطة تؤدي إلى مشاكل كبيرة في السباكة. من أهم هذه الأخطاء إلقاء بقايا الطعام في الأحواض وعدم معالجة التسربات الصغيرة فور اكتشافها.</p>',
                    'en' => '<p>Many homeowners make simple mistakes that lead to major plumbing issues. Key mistakes include disposing of food waste in sinks and ignoring small leaks when first discovered.</p>',
                ],
                'meta_title' => [
                    'ar' => 'أخطاء السباكة وكيف تتجنبها',
                    'en' => 'Plumbing Mistakes and How to Avoid Them',
                ],
                'meta_description' => [
                    'ar' => 'دليل شامل لأكثر أخطاء السباكة شيوعًا وطرق التعامل معها في المنزل بشكل صحيح.',
                    'en' => 'A comprehensive guide to the most common plumbing mistakes and how to properly deal with them at home.',
                ],
                'status' => 1,
                'is_featured' => false,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => [
                    'ar' => 'دليلك لاختيار الإضاءة المناسبة لكل غرفة',
                    'en' => 'Your Guide to Choosing the Right Lighting for Every Room',
                ],
                'excerpt' => [
                    'ar' => 'الإضاءة تلعب دورًا محوريًا في إبراز جمال المنزل وتحسين الحالة المزاجية. إليك كيف تختارها بشكل صحيح.',
                    'en' => 'Lighting plays a central role in highlighting home beauty and improving mood. Here is how to choose it correctly.',
                ],
                'body' => [
                    'ar' => '<p>تختلف احتياجات الإضاءة باختلاف الغرفة. فغرفة النوم تحتاج لضوء دافئ ومريح، بينما المطبخ وأماكن العمل تتطلب إضاءة بيضاء وواضحة لتسهيل أداء المهام.</p>',
                    'en' => '<p>Lighting needs differ by room. A bedroom needs warm, relaxing light, while kitchens and workspaces require bright, clear lighting for tasks.</p>',
                ],
                'meta_title' => [
                    'ar' => 'دليل إضاءة المنزل',
                    'en' => 'Home Lighting Guide',
                ],
                'meta_description' => [
                    'ar' => 'نصائح وأفكار لاختيار وتوزيع الإضاءة في غرف منزلك المختلفة.',
                    'en' => 'Tips and ideas for choosing and distributing lighting in different rooms of your home.',
                ],
                'status' => 1,
                'is_featured' => true,
                'published_at' => now()->subDays(5),
            ]
        ];

        for ($i = 4; $i <= 20; $i++) {
            $articles[] = [
                'title' => [
                    'ar' => 'مقال تجريبي رقم ' . $i,
                    'en' => 'Demo Article Number ' . $i,
                ],
                'excerpt' => [
                    'ar' => 'هذا نص تجريبي لمقتطف المقال رقم ' . $i . ' يمكن استبداله لاحقاً بنص حقيقي يعبر عن محتوى المقال.',
                    'en' => 'This is a demo excerpt for article number ' . $i . '. It can be replaced later with real text that expresses the article content.',
                ],
                'body' => [
                    'ar' => '<p>هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربى، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى إضافة إلى زيادة عدد الحروف التى يولدها التطبيق.</p>',
                    'en' => '<p>This text is an example of text that can be replaced in the same space. This text was generated from the Arabic text generator, where you can generate such text or many other texts in addition to increasing the number of characters generated by the application.</p>',
                ],
                'meta_title' => [
                    'ar' => 'مقال تجريبي ' . $i . ' - عنوان اختباري',
                    'en' => 'Demo Article ' . $i . ' - Test Title',
                ],
                'meta_description' => [
                    'ar' => 'وصف تجريبي لميتا المقال رقم ' . $i . ' لتعبئة الحقول اللازمة بشكل سليم.',
                    'en' => 'Demo meta description for article ' . $i . ' to fill required fields properly.',
                ],
                'status' => 1,
                'is_featured' => fake()->boolean(20),
                'published_at' => now()->subDays($i),
            ];
        }

        $imagePath = public_path('WhatsApp Image 2026-04-01 at 9.32.04 PM.jpeg');

        foreach ($articles as $articleData) {
            $article = Article::create($articleData);

            if (file_exists($imagePath)) {
                $article->addMedia($imagePath)->preservingOriginal()->toMediaCollection('featured_image');
            }
        }
    }
}
