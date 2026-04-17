<?php

namespace App\Listeners;

use App\Events\NewBankTransfer;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBankTransferNotification
{
    use HasNotifications;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewBankTransfer $event): void
    {
        $title = [
            'ar' => 'تم عمل تحويل بنكي لحسابك.',
            'en' => 'A bank transfer has been made to your account.',
            'hi' => 'आपके खाते में बैंक ट्रांसफर किया गया है।',
            'bn' => 'আপনার অ্যাকাউন্টে ব্যাংক ট্রান্সফার করা হয়েছে।',
            'ur' => 'آپ کے اکاؤنٹ میں بینک ٹرانسفر کر دیا گیا ہے۔',
            'tl' => 'Isinagawa na ang bank transfer sa iyong account.',
            'id' => 'Transfer bank telah dilakukan ke akun Anda.',
            'fr' => 'Un virement bancaire a été effectué sur votre compte.',
        ];

        $description = [
            'ar' => 'تم الموافقة على طلبك لسحب الرصيد وتم تحويل المبلغ إلى حسابك البنكي.',
            'en' => 'Your withdrawal request has been approved and the amount has been transferred to your bank account.',
            'hi' => 'आपका राशि निकासी अनुरोध स्वीकृत कर दिया गया है और राशि आपके बैंक खाते में ट्रांसफर कर दी गई है।',
            'bn' => 'আপনার ব্যালেন্স উত্তোলনের অনুরোধ অনুমোদিত হয়েছে এবং পরিমাণটি আপনার ব্যাংক অ্যাকাউন্টে স্থানান্তর করা হয়েছে।',
            'ur' => 'آپ کی بیلنس نکالنے کی درخواست منظور ہو گئی ہے اور رقم آپ کے بینک اکاؤنٹ میں منتقل کر دی گئی ہے۔',
            'tl' => 'Naaprubahan ang iyong kahilingan sa withdrawal at na-transfer na ang halaga sa iyong bank account.',
            'id' => 'Permintaan penarikan Anda telah disetujui dan jumlahnya telah ditransfer ke akun bank Anda.',
            'fr' => 'Votre demande de retrait a été approuvée et le montant a été transféré sur votre compte bancaire.',
        ];

        $type = 'bank-transfer';

        $this->sendNotification(
            type: $type,
            title: $title,
            description: $description,
            pushNotification: true,
            recipient: $event->serviceProvider,
        );
    }
}
