<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationEcommerceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'information_contact_name' => $this->information_contact_name,
            'information_contact_email' =>  $this->information_contact_email,
            'information_contact_phone' =>  $this->information_contact_phone,
            'information_contact_address' =>  $this->information_contact_address,
            'script_paypal' => $this->script_paypal,
            'token_private_culqui' => $this->token_private_culqui,
            'token_public_culqui' => $this->token_public_culqui,
            'logo' => $this->logo,
            'link_youtube' => $this->link_youtube,
            'link_twitter' => $this->link_twitter,
            'link_facebook' => $this->link_facebook,
            'link_tiktok' => $this->link_tiktok,
            'link_instagram' => $this->link_instagram,
            'tag_shipping' => $this->tag_shipping,
            'tag_dollar' => $this->tag_dollar,
            'tag_support' => $this->tag_support,
            'phone_whatsapp' => $this->phone_whatsapp,
            'title_one_customised_link' => $this->title_one_customised_link,
            'title_two_customised_link' => $this->title_two_customised_link,
            'title_three_customised_link' => $this->title_three_customised_link,
            'customised_link_one' => $this->customised_link_one,
            'customised_link_two' => $this->customised_link_two,
            'customised_link_three' => $this->customised_link_three,
        ];
    }
}
