<?php

/*
Plugin Name: Grimp-PHP
Plugin URI: http://git.grimp.eu
Description: Execute PHP Code inside a post. Do NOT use this plugin if you don't trust your WP users.
Version: 0.1
Author: Fabio Alessandro Locati
Author URI: http://grimp.eu

Inspired by Exec PHP by Priyadi Iman Nurcahyo
Inspired by runphp plugin by Mark Somerville
*/



### mask code before going to the nasty balanceTags ###
function php_exec_pre($text) {
  $textarr = preg_split("/(<phpcode>.*<\\/phpcode>)/Us", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
  $stop = count($textarr);// loop stuff
  for ($phpexec_i = 0; $phpexec_i < $stop; $phpexec_i++) {
    $content = $textarr[$phpexec_i];
    if (preg_match("/^<phpcode>(.*)<\\/phpcode>/Us", $content, $code)) { // If it's a phpcode	
      $content = '[phpcode]' . base64_encode($code[1]) . '[/phpcode]';
    }
    $output .= $content;
  }
  return $output;
}

### unmask code after balanceTags ###
function php_exec_post($text) {
  $textarr = preg_split("/(\\[phpcode\\].*\\[\\/phpcode\\])/Us", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
  $stop = count($textarr);// loop stuff
  for ($phpexec_i = 0; $phpexec_i < $stop; $phpexec_i++) {
    $content = $textarr[$phpexec_i];
    if (preg_match("/^\\[phpcode\\](.*)\\[\\/phpcode\\]/Us", $content, $code)) { // If it's a phpcode
      $content = '<phpcode>' . base64_decode($code[1]) . '</phpcode>';
    }
    $output .= $content;
  }
  return $output;
}

### main routine ###
function php_exec_process($phpexec_text) {
  if(auhor_can(get_the_ID(),"unfiltered_html"))
    $phpexec_doeval = true;

  $phpexec_textarr = preg_split("/(<phpcode>.*<\\/phpcode>)/Us", $phpexec_text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
  $phpexec_stop = count($phpexec_textarr);// loop stuff
  for ($phpexec_i = 0; $phpexec_i < $phpexec_stop; $phpexec_i++) {
    $phpexec_content = $phpexec_textarr[$phpexec_i];
    if (preg_match("/^<phpcode>(.*)<\\/phpcode>/Us", $phpexec_content, $phpexec_code)) { // If it's a phpcode	
      $phpexec_php = $phpexec_code[1];
      if ($phpexec_doeval) {
        ob_start();
        eval("?>". $phpexec_php . "<?php ");
        $phpexec_output .= ob_get_clean();
      } else {
        $phpexec_output .= htmlspecialchars($phpexec_php);
      }
    } else {
      $phpexec_output .= $phpexec_content;
    }
  }
  return $phpexec_output;
}

add_filter('content_save_pre', 'php_exec_pre', 29);
add_filter('content_save_pre', 'php_exec_post', 71);
add_filter('the_content', 'php_exec_process', 2);

add_filter('excerpt_save_pre', 'php_exec_pre', 29);
add_filter('excerpt_save_pre', 'php_exec_post', 71);
add_filter('the_excerpt', 'php_exec_process', 2);
