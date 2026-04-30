<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::create([
            'name' => [
                'ar' => 'من نحن',
                'en' => 'About us',
            ],
            'body' => [
                'ar' => '
                <p>تأسست شركتنا على أسس الابتكار والالتزام، ونسعى دائمًا إلى تقديم حلول عالية الجودة لعملائنا في مختلف القطاعات. نمتلك فريقًا متكاملاً من الخبراء المتخصصين في مجالات متعددة، ويعملون بكل تفانٍ لتقديم قيمة حقيقية وشاملة لكل مشروع نقوم به.</p>
                <p>نحن نؤمن بأن النجاح يأتي من الشفافية، والنزاهة، والتفاعل المستمر مع عملائنا. رؤيتنا هي أن نكون الشريك الأول في تطوير الأعمال وتقديم الخدمات التي تلبي تطلعات السوق المحلي والدولي.</p>
                <p>تشمل خدماتنا الاستشارات الإدارية، تطوير البرمجيات، التسويق الرقمي، وحلول التجارة الإلكترونية. نحن ملتزمون بالتطوير المستمر ومواكبة أحدث التقنيات لضمان تقديم خدمات تواكب تطلعات المستقبل.</p>
                ',
                'en' => '
                <p>Our company was founded on principles of innovation and commitment, always striving to provide high-quality solutions for clients across various sectors. We have a fully integrated team of specialists who work diligently to deliver comprehensive value in every project.</p>
                <p>We believe that success is built on transparency, integrity, and continuous engagement with our clients. Our vision is to be the number one partner in business development and service delivery that meets the expectations of both local and global markets.</p>
                <p>Our services include business consulting, software development, digital marketing, and e-commerce solutions. We are committed to continuous growth and keeping up with the latest technologies to ensure future-oriented service delivery.</p>
                ',
            ],
            'tag' => 'about-us',
            'status' => Page::ACTIVE_STATUS
        ]);

        Page::create([
            'name' => [
                'ar' => 'الشروط والأحكام',
                'en' => 'Terms & Conditions',
            ],
            'body' => [
                'ar' => '
                <p>يرجى قراءة هذه الشروط بعناية قبل استخدام الموقع. باستخدامك لهذا الموقع، فإنك توافق على الالتزام بهذه الشروط بجميع بنودها. إذا كنت لا توافق على أي من هذه الشروط، يرجى عدم استخدام الموقع.</p>
                <p>يُمنع استخدام الموقع لأي أغراض غير قانونية، أو محاولة الوصول غير المصرح به إلى النظام، أو انتهاك حقوق الملكية الفكرية الخاصة بنا أو بالآخرين. تحتفظ الإدارة بحقها الكامل في تعليق أو إلغاء حساب المستخدم في حال انتهاك أي من الشروط.</p>
                <p>قد يتم تحديث هذه الشروط من وقت لآخر، وسيتم إشعار المستخدمين بأي تغييرات جوهرية. استمرار استخدامك للموقع بعد التعديلات يعتبر موافقة صريحة على الشروط الجديدة.</p>
                ',
                'en' => '
                <p>Please read these terms carefully before using the website. By accessing or using this website, you agree to be bound by these terms in full. If you do not agree with any part of the terms, please do not use the site.</p>
                <p>Using the website for any illegal purposes, unauthorized access, or violation of intellectual property rights—either ours or others’—is strictly prohibited. We reserve the right to suspend or terminate user accounts found in breach of any terms.</p>
                <p>These terms may be updated occasionally, and users will be notified of any significant changes. Continued use of the website after such updates will be considered as acceptance of the revised terms.</p>
                ',
            ],
            'tag' => 'terms-and-conditions',
            'status' => Page::ACTIVE_STATUS
        ]);

        Page::create([
            'name' => [
                'ar' => 'سياسة الخصوصية',
                'en' => 'Privacy Policy',
            ],
            'body' => [
                'ar' => '
                <p>خصوصيتك تهمنا. تلتزم شركتنا بحماية بياناتك الشخصية واستخدامها فقط للأغراض المصرح بها، مثل تحسين تجربة المستخدم، وتقديم الدعم الفني، وتحليل الأداء.</p>
                <p>يتم جمع المعلومات بطرق آمنة تشمل نماذج التسجيل، الكوكيز، وتحليل حركة المرور. نحن لا نشارك معلوماتك الشخصية مع أي أطراف خارجية دون موافقتك المسبقة، إلا إذا كان ذلك مطلوبًا بموجب القانون.</p>
                <p>نستخدم تدابير أمنية تقنية وإدارية للحفاظ على بياناتك من أي استخدام أو وصول غير مصرح به. كما نتيح للمستخدمين حق طلب تعديل أو حذف بياناتهم في أي وقت.</p>
                ',
                'en' => '
                <p>Your privacy matters to us. Our company is committed to protecting your personal information and using it solely for authorized purposes, such as enhancing user experience, providing technical support, and analyzing site performance.</p>
                <p>Information is collected securely through registration forms, cookies, and traffic analysis tools. We do not share your personal data with third parties without your prior consent, unless legally required to do so.</p>
                <p>We implement technical and administrative safeguards to protect your data from unauthorized access or misuse. Users also have the right to request the modification or deletion of their data at any time.</p>
                ',
            ],
            'tag' => 'privacy-policy',
            'status' => Page::ACTIVE_STATUS
        ]);
    }
}
