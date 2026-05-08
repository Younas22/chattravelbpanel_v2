<?php

namespace Database\Seeders;

use App\Models\CannedReply;
use App\Models\QuickFaq;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@travelbookingpanel.com')],
            [
                'name'     => 'Admin',
                'email'    => env('ADMIN_EMAIL', 'admin@travelbookingpanel.com'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@12345')),
                'is_admin' => true,
            ]
        );

        // Default widget settings
        foreach (WidgetSetting::defaults() as $key => $value) {
            WidgetSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Sample FAQs
        $faqs = [
            ['question' => 'What is TravelBookingPanel?', 'answer' => 'TravelBookingPanel is a comprehensive travel management solution for booking flights, hotels, and tours.', 'sort_order' => 1],
            ['question' => 'How do I get started?', 'answer' => 'Simply sign up for a free trial and follow our setup guide. You can be up and running in minutes!', 'sort_order' => 2],
            ['question' => 'What are your pricing plans?', 'answer' => 'We offer flexible pricing starting from $29/month. Contact us for enterprise pricing.', 'sort_order' => 3],
            ['question' => 'Is there an API available?', 'answer' => 'Yes! We provide a full REST API with documentation at docs.travelbookingpanel.com.', 'sort_order' => 4],
            ['question' => 'Do you offer white-label options?', 'answer' => 'Yes, we offer white-label solutions. Please contact us for more details.', 'sort_order' => 5],
            ['question' => 'How long does setup take?', 'answer' => 'Basic setup takes around 30 minutes. Full integration can take 1-2 hours depending on your requirements.', 'sort_order' => 6],
        ];

        foreach ($faqs as $faq) {
            QuickFaq::updateOrCreate(['question' => $faq['question']], $faq + ['is_active' => true, 'show_chat_button' => true]);
        }

        // Sample canned replies
        $replies = [
            ['title' => 'Greeting', 'body' => 'Hello! Thank you for reaching out to TravelBookingPanel support. How can I help you today?', 'shortcut' => 'hi'],
            ['title' => 'Be Right Back', 'body' => 'Thank you for your patience. Let me look into this for you. I\'ll be right back with an answer!', 'shortcut' => 'brb'],
            ['title' => 'Closing', 'body' => 'Thank you for contacting TravelBookingPanel support! Is there anything else I can help you with?', 'shortcut' => 'bye'],
            ['title' => 'Pricing Info', 'body' => 'Our pricing starts from $29/month for the starter plan. You can view all plans at travelbookingpanel.com/pricing', 'shortcut' => 'price'],
            ['title' => 'Demo Request', 'body' => 'I\'d be happy to schedule a demo for you! Please share your preferred date/time and I\'ll set it up.', 'shortcut' => 'demo'],
        ];

        foreach ($replies as $reply) {
            CannedReply::updateOrCreate(['shortcut' => $reply['shortcut']], $reply);
        }
    }
}
