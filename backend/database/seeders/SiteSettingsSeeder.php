<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SiteSettingsSeeder extends Seeder
{
    
    public function run(): void
    {
        $now = Carbon::now();

        $settings = [

            [
                'key' => 'site_name',
                'group' => 'general',
                'value' => 'Casa Vera Furniture',
                'type' => 'text',
                'label' => 'Site Name',
                'description' => 'The name of your website',
                'is_public' => true,
            ],
            [
                'key' => 'site_tagline',
                'group' => 'general',
                'value' => 'Luxury Furniture for Modern Living',
                'type' => 'text',
                'label' => 'Site Tagline',
                'description' => 'A short description of your business',
                'is_public' => true,
            ],
            [
                'key' => 'site_logo',
                'group' => 'general',
                'value' => '/images/logo.svg',
                'type' => 'image',
                'label' => 'Site Logo',
                'description' => 'Main logo displayed in header',
                'is_public' => true,
            ],
            [
                'key' => 'site_favicon',
                'group' => 'general',
                'value' => '/favicon.ico',
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'Small icon displayed in browser tab',
                'is_public' => true,
            ],
            [
                'key' => 'currency',
                'group' => 'general',
                'value' => 'PHP',
                'type' => 'text',
                'label' => 'Currency',
                'description' => 'Default currency code',
                'is_public' => true,
            ],
            [
                'key' => 'currency_symbol',
                'group' => 'general',
                'value' => '₱',
                'type' => 'text',
                'label' => 'Currency Symbol',
                'description' => 'Currency symbol for display',
                'is_public' => true,
            ],

            [
                'key' => 'business_payment_gcash_name',
                'group' => 'payment',
                'value' => 'Casa Vera Furniture',
                'type' => 'text',
                'label' => 'GCash Account Name',
                'description' => 'Account name for GCash payments',
                'is_public' => true,
            ],
            [
                'key' => 'business_payment_gcash_number',
                'group' => 'payment',
                'value' => '0917-155-2913',
                'type' => 'text',
                'label' => 'GCash Account Number',
                'description' => 'Account number for GCash payments',
                'is_public' => true,
            ],

            [
                'key' => 'contact_email',
                'group' => 'contact',
                'value' => 'info@casavera.com',
                'type' => 'text',
                'label' => 'Contact Email',
                'description' => 'Primary contact email',
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'group' => 'contact',
                'value' => '0917-155-2913',
                'type' => 'text',
                'label' => 'Contact Phone',
                'description' => 'Primary contact phone number',
                'is_public' => true,
            ],
            [
                'key' => 'contact_address',
                'group' => 'contact',
                'value' => '123 Furniture Street, Makati City, Metro Manila, Philippines 1234',
                'type' => 'textarea',
                'label' => 'Business Address',
                'description' => 'Physical store address',
                'is_public' => true,
            ],
            [
                'key' => 'business_hours',
                'group' => 'contact',
                'value' => json_encode([
                    'monday' => '9:00 AM - 6:00 PM',
                    'tuesday' => '9:00 AM - 6:00 PM',
                    'wednesday' => '9:00 AM - 6:00 PM',
                    'thursday' => '9:00 AM - 6:00 PM',
                    'friday' => '9:00 AM - 6:00 PM',
                    'saturday' => '10:00 AM - 5:00 PM',
                    'sunday' => 'Closed',
                ]),
                'type' => 'json',
                'label' => 'Business Hours',
                'description' => 'Store operating hours',
                'is_public' => true,
            ],
            [
                'key' => 'google_maps_embed',
                'group' => 'contact',
                'value' => '',
                'type' => 'textarea',
                'label' => 'Google Maps Embed URL',
                'description' => 'Google Maps embed URL for location',
                'is_public' => true,
            ],

            [
                'key' => 'social_facebook',
                'group' => 'social',
                'value' => 'https://facebook.com/casaverafurniture',
                'type' => 'text',
                'label' => 'Facebook URL',
                'description' => 'Facebook page URL',
                'is_public' => true,
            ],
            [
                'key' => 'social_instagram',
                'group' => 'social',
                'value' => 'https://instagram.com/casaverafurniture',
                'type' => 'text',
                'label' => 'Instagram URL',
                'description' => 'Instagram profile URL',
                'is_public' => true,
            ],
            [
                'key' => 'social_twitter',
                'group' => 'social',
                'value' => '',
                'type' => 'text',
                'label' => 'Twitter URL',
                'description' => 'Twitter/X profile URL',
                'is_public' => true,
            ],
            [
                'key' => 'social_pinterest',
                'group' => 'social',
                'value' => '',
                'type' => 'text',
                'label' => 'Pinterest URL',
                'description' => 'Pinterest profile URL',
                'is_public' => true,
            ],
            [
                'key' => 'social_youtube',
                'group' => 'social',
                'value' => '',
                'type' => 'text',
                'label' => 'YouTube URL',
                'description' => 'YouTube channel URL',
                'is_public' => true,
            ],

            [
                'key' => 'seo_title',
                'group' => 'seo',
                'value' => 'Casa Vera Furniture | Luxury Furniture Philippines',
                'type' => 'text',
                'label' => 'Default SEO Title',
                'description' => 'Default title for search engines',
                'is_public' => true,
            ],
            [
                'key' => 'seo_description',
                'group' => 'seo',
                'value' => 'Discover luxury furniture for your home at Casa Vera. Premium sofas, dining sets, bedroom furniture, and more. Free delivery in Metro Manila.',
                'type' => 'textarea',
                'label' => 'Default Meta Description',
                'description' => 'Default description for search engines',
                'is_public' => true,
            ],
            [
                'key' => 'seo_keywords',
                'group' => 'seo',
                'value' => 'furniture, luxury furniture, Philippines, sofa, dining table, bedroom, home decor, Casa Vera',
                'type' => 'text',
                'label' => 'Default Meta Keywords',
                'description' => 'Default keywords for search engines',
                'is_public' => true,
            ],
            [
                'key' => 'og_image',
                'group' => 'seo',
                'value' => '/images/og-image.jpg',
                'type' => 'image',
                'label' => 'Default OG Image',
                'description' => 'Default image for social media sharing',
                'is_public' => true,
            ],

            [
                'key' => 'order_prefix',
                'group' => 'orders',
                'value' => 'CV',
                'type' => 'text',
                'label' => 'Order Number Prefix',
                'description' => 'Prefix for order numbers (e.g., CV-001234)',
                'is_public' => false,
            ],
            [
                'key' => 'min_order_amount',
                'group' => 'orders',
                'value' => '1000',
                'type' => 'number',
                'label' => 'Minimum Order Amount',
                'description' => 'Minimum order amount in PHP',
                'is_public' => true,
            ],
            [
                'key' => 'free_shipping_threshold',
                'group' => 'orders',
                'value' => '5000',
                'type' => 'number',
                'label' => 'Free Shipping Threshold',
                'description' => 'Order amount for free shipping',
                'is_public' => true,
            ],
            [
                'key' => 'tax_rate',
                'group' => 'orders',
                'value' => '12',
                'type' => 'number',
                'label' => 'Tax Rate (%)',
                'description' => 'VAT rate percentage',
                'is_public' => false,
            ],
            [
                'key' => 'tax_included',
                'group' => 'orders',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Tax Included in Prices',
                'description' => 'Whether prices already include tax',
                'is_public' => false,
            ],

            [
                'key' => 'notify_new_order',
                'group' => 'notifications',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'New Order Notifications',
                'description' => 'Email admins on new orders',
                'is_public' => false,
            ],
            [
                'key' => 'notify_low_stock',
                'group' => 'notifications',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Low Stock Notifications',
                'description' => 'Email admins when stock is low',
                'is_public' => false,
            ],
            [
                'key' => 'low_stock_threshold',
                'group' => 'notifications',
                'value' => '5',
                'type' => 'number',
                'label' => 'Low Stock Threshold',
                'description' => 'Quantity that triggers low stock alert',
                'is_public' => false,
            ],
            [
                'key' => 'admin_notification_email',
                'group' => 'notifications',
                'value' => 'orders@casavera.com',
                'type' => 'text',
                'label' => 'Admin Notification Email',
                'description' => 'Email address for admin notifications',
                'is_public' => false,
            ],

            [
                'key' => 'maintenance_mode',
                'group' => 'maintenance',
                'value' => 'false',
                'type' => 'boolean',
                'label' => 'Maintenance Mode',
                'description' => 'Enable to show maintenance page',
                'is_public' => false,
            ],
            [
                'key' => 'maintenance_message',
                'group' => 'maintenance',
                'value' => 'We are currently performing scheduled maintenance. Please check back soon.',
                'type' => 'textarea',
                'label' => 'Maintenance Message',
                'description' => 'Message to display during maintenance',
                'is_public' => false,
            ],
        ];

        foreach ($settings as &$setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
        }

        DB::table('site_settings')->insert($settings);
    }
}
