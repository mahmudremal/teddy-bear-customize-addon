<?php
if (!class_exists('CR_Local_Forms')) {wp_die(__('Something error happening', 'teddybearsprompts'));}
if ($plain_text) :
  echo esc_url($review_link);
else:
  $html_button = '<a href="'.esc_url($review_link).'" target="_blank" rel="noopener noreferrer" style="'.esc_attr(apply_filters('teddybear/project/system/getoption', 'cusrev-completedorder-css', 'font-weight:normal;background:#0085ba;border-color:#0073aa;color:#fff;text-decoration:none;padding:10px;border-radius:10px;')).'">'.esc_html(
    apply_filters(
      'teddybear/project/system/translate', 
      apply_filters('teddybear/project/system/getoption', 'cusrev-completedorder-text', 'Write a Review'),
      'teddybearsprompts',
      'Write a Review - input field'
    )
  ).'</a>';
  
  $html_template = apply_filters('teddybear/project/system/getoption', 'cusrev-completedorder-template', '{{button}}');
  $html_template = str_replace([
    '{{button}}', '{{link}}'
  ], [
    $html_button, $review_link
  ], $html_template);

  echo $html_template;
endif;
?>