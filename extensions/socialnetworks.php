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
    }
    function render(array $group) : string
    {
        $post = '';
        switch ($group['class']) {
            case 'twitter':
                $json = json_decode(file_get_contents('https://publish.twitter.com/oembed?url=' . $group['class-data'][0]), true);
                $post = $json['html'];
                break;
        }
        return $post;
    }
}