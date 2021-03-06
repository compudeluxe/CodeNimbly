<?php defined('PATH_ACCESS') or exit("NO DICE! Don't Play Too Smart.");
 /**
 * AutoLink Class
 * 
 * credits to Cal Henderson <cal@iamcal.com> - http://appliedthinking.org/autolinking/
 *
 * @package		CodeNimbly
 * @subpackage  Libraries
 * @category	Libraries
 * @since       Version 1.0
 */
       
class AutoLink
{     
    /**
    * @desc Auto Link URL
    */
	public function url($text, $limit=100, $tagfill='', $add_url_redirector=''){
    
       // $add_url_redirector = BASE_URL.'goto/url/'; //my url that redirect external links
        
		$text = $this->urlExec($text, 'https://',	$limit, $tagfill, $add_url_redirector);
		$text = $this->urlExec($text, 'http://',	$limit, $tagfill, $add_url_redirector);
		$text = $this->urlExec($text, 'ftp://',	$limit, $tagfill, $add_url_redirector);
        $text = $this->urlExec($text, 'ftps://',	$limit, $tagfill, $add_url_redirector);
		$text = $this->urlExec($text, 'www.',	$limit, $tagfill, $add_url_redirector);  
		return $text;
	}

	####################################################################
    /**
    * @desc Auto Link URL Backend Execution
    */
	public function urlExec($text, $sub, $limit, $tagfill, $add_url_redirector){
        
		$sub_len = strlen($sub);
		$text_l = StrToLower($text);
		$cursor = 0;
		$loop = 1;
		$buffer = '';
		while (($cursor < strlen($text)) && $loop){
			$ok = 1;
			$pos = strpos($text_l, $sub, $cursor);
			if ($pos === false){
				$loop = 0;
				$ok = 0;
			} else {
				$pre_hit = substr($text, $cursor, $pos-$cursor);
				$hit = substr($text, $pos, $sub_len);
				$pre = substr($text, 0, $pos);
				$question = substr($text, $pos + $sub_len);

				$fail_text = $pre_hit.$hit;
				$fail_len = strlen($fail_text);

				#
				# substring found - first check to see if we're inside a link tag already...
				#
				$bits = preg_split("!</a>!i", $pre);
				$last_bit = array_pop($bits);
				if (preg_match("!<a\s!i", $last_bit)){

					#echo "fail 1 at $cursor<br />\n";
					$ok = 0;
					$cursor += $fail_len;
					$buffer .= $fail_text;
				}
			}

			#
			# looks like a nice spot to autolink from - check the pre
			# to see if there was whitespace before this match
			# 
			if ($ok){
				if ($pre){
					if (!preg_match('![\s\(\[\{]$!s', $pre)){
						#echo "fail 2 at $cursor ($pre)<br />\n";
						$ok = 0;
						$cursor += $fail_len;
						$buffer .= $fail_text;
					}
				}
			}

			#
			# we want to autolink here - find the extent of the url
			#
			if ($ok){
				if (preg_match('/^([a-z0-9\-\.\/\-_%~!?=,:;&+*#@\(\)\$]+)/i', $question, $matches)){

					$url = $hit.$matches[1];

					#
					# remove trailing punctuation from url
					#                            
					if (preg_match('|[.,!;:?]$|', $url)){
						$url = substr($url, 0, strlen($url)-1);
					}
					foreach (array('()', '[]', '{}') as $pair){
						$o = substr($pair, 0, 1);
						$c = substr($pair, 1, 1);
						if (preg_match("!^(\\$c|^)[^\\$o]+\\$c$!", $url)){
							$url = substr($url, 0, strlen($url)-1);
						}
					}

					#
					# commit
					#   
					$cursor += strlen($url) + strlen($pre_hit);
					$buffer .= $pre_hit;

					#
					# nice-i-fy url here
					#                      
					$link_url = $url;
					$display_url = $url;
                    
					if (preg_match('!^([a-z]+)://!i', $url, $m)){
						$display_url = substr($display_url, strlen($m[1])+3);
					} else {
						$link_url = "http://$link_url";
					}

					$display_url = $this->label($display_url, $limit);

					#
					# add the url
					#          
                    
                    
                    //Add URL redirector .... Added by Victory Osayi
                    $link_url = $add_url_redirector . $link_url;
                                       
					
                    $buffer .= "<a href=\"$link_url\"$tagfill>$display_url</a>";
                    				
				} else {
					#echo "fail 3 at $cursor<br />\n";
					$ok = 0;
					$cursor += $fail_len;
					$buffer .= $fail_text;
				}
			}                
		}

		#
		# add everything from the cursor to the end onto the buffer.
		#    
		$buffer .= substr($text, $cursor); 
		return $buffer;
	}

	####################################################################
    /**
    * @desc Auto Link LABEL
    */
	public function label($text, $limit, $fill_with_text='...'){

		if (!$limit){ return $text; }

		if (strlen($text) > $limit){
			return substr($text, 0, $limit-3).$fill_with_text;
		}

		return $text;
	}

	####################################################################
    /**
    * @desc Auto link EMAIL
    */
	public function email($text, $tagfill=''){

		$atom = '[^()<>@,;:\\\\".\\[\\]\\x00-\\x20\\x7f]+'; # from RFC822

		#die($atom);
		$text_l = StrToLower($text);
		$cursor = 0;
		$loop = 1;
		$buffer = '';

		while(($cursor < strlen($text)) && $loop){
			#
			# find an '@' symbol
			# 
			$ok = 1;
			$pos = strpos($text_l, '@', $cursor); 
			if ($pos === false){
				$loop = 0;
				$ok = 0; 
			} else {

				$pre = substr($text, $cursor, $pos-$cursor);
				$hit = substr($text, $pos, 1);
				$question = substr($text, $pos + 1);

				$fail_text = $pre.$hit;
				$fail_len = strlen($fail_text);

				#die("$pre::$hit::$question::$fail_text");

				#
				# substring found - first check to see if we're inside a link tag already...
				#      
				$bits = preg_split("!</a>!i", $pre);
				$last_bit = array_pop($bits);
				if (preg_match("!<a\s!i", $last_bit)){

					#echo "fail 1 at $cursor<br />\n"; 
					$ok = 0;
					$cursor += $fail_len;
					$buffer .= $fail_text;
				}
			}

			#
			# check backwards
			#                           
			if ($ok){
				if (preg_match("!($atom(\.$atom)*)\$!", $pre, $matches)){ 
					# move matched part of address into $hit  
					$len = strlen($matches[1]);
					$plen = strlen($pre);

					$hit = substr($pre, $plen-$len).$hit;
					$pre = substr($pre, 0, $plen-$len);

				} else {

					#echo "fail 2 at $cursor ($pre)<br />\n";
					$ok = 0;
					$cursor += $fail_len;
					$buffer .= $fail_text;
				}
			}

			#
			# check forwards
			#                     
			if ($ok){
				if (preg_match("!^($atom(\.$atom)*)!", $question, $matches)){ 
                    
					# move matched part of address into $hit

					$len = strlen($matches[1]); 
                    
					$hit .= substr($question, 0, $len);
					$question = substr($question, $len);

				} else {
					#echo "fail 3 at $cursor ($question)<br />\n"; 
					$ok = 0;
					$cursor += $fail_len;
					$buffer .= $fail_text;
				}
			}

			#
			# commit
			#          
			if ($ok) { 
				$cursor += strlen($pre) + strlen($hit);
				$buffer .= $pre;
				$buffer .= "<a href=\"mailto:$hit\"$tagfill>$hit</a>";  
			}                                                                                                                         
		}

		#
		# add everything from the cursor to the end onto the buffer.
		#                                           
		$buffer .= substr($text, $cursor);
		return $buffer;
	}
}//end of class
?>