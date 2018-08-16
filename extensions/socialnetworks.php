<?php
class MaatExtension_socialnetworks implements Maat\Extension
{
    private $maat;
    function __construct($maat)
    {
        $this->maat = $maat;
        $maat->define_trigger(
            'socialnetworks',
            'twitter',
            '^https?:\/\/.*twitter\.com\/(.*)\/status\/(.*)',
            false
        );
        $maat->define_trigger(
            'socialnetworks',
            'instagram',
            '^https?:\/\/.*instagram\.com\/p\/.*',
            false
        );
    }
    function render(array $group) : string
    {
        $post = '';
        switch ($group['class']) {
            case 'twitter':
                $json = json_decode(file_get_contents('https://publish.twitter.com/oembed?url=' . $group['class-data'][0]), true);
                return $json['html'];
                break;
            case 'instagram':
                $json = json_decode(file_get_contents('https://api.instagram.com/oembed?maxwidth=500&url=' . $group['class-data'][0]), true);
                return $json['html'];
                break;
        }
        return $post;
    }
}