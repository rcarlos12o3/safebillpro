<?php

namespace App\Models\Tenant;

class ConfigurationEcommerce extends ModelTenant
{
    protected $table = "configuration_ecommerce";

    protected $fillable = [
        'information_contact_name',
        'information_contact_email',
        'information_contact_phone',
        'information_contact_address',
        'script_paypal',
        'token_private_culqui',
        'token_public_culqui',
        'link_youtube',
        'link_twitter',
        'link_facebook',
        'link_tiktok',
        'link_instagram',
        'tag_shipping',
        'tag_dollar',
        'tag_support',
        'phone_whatsapp',
        'title_one_customised_link',
        'title_two_customised_link',
        'title_three_customised_link',
        'customised_link_one',
        'customised_link_two',
        'customised_link_three',
    ];

}
