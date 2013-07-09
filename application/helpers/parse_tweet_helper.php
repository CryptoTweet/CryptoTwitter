<?php
if(!function_exists("parse_tweet")){
    
    function parse_tweet($tweet = ""){
        $regex = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:']))#";
        
        //Fetch some links and make them clickable
        @preg_match($regex, $tweet, $links);
        if(is_array($links) && count($links)> 0){
            $links = array_unique($links);            
            foreach($links as $link){
                $proto = substr($link,0,strpos($link,"://"));
                if($proto == ""){
                    $newlink = "http://".$link;
                }else{
                    $newlink = $link;
                }
                $tweet = str_replace($link, '<a href="'.$newlink.'" target="_blank">'.$link.'</a>', $tweet);
            }
        }
        return $tweet;
    }
    
}
?>
