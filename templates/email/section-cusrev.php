<?php
// if (!class_exists('CR_Local_Forms')) {wp_die(__('Something error happening', 'teddybearsprompts'));}
if ($plain_text) :
  echo esc_url($review_link);
else:
  // apply_filters(
  //   'teddybear/project/system/translate/string', 
  //   apply_filters('teddybear/project/system/getoption', 'cusrev-completedorder-text', 'Write a Review'),
  //   'teddybearsprompts',
  //   'Write a Review - input field'
  // )
  $html_button = '<a href="'.esc_url($review_link).'" target="_blank" rel="noopener noreferrer" style="'.esc_attr(apply_filters('teddybear/project/system/getoption', 'cusrev-completedorder-css', 'font-weight:normal;background:#0085ba;border-color:#0073aa;color:#fff;text-decoration:none;padding:10px;border-radius:10px;')).'">'.esc_html(
    __('Write a Review', 'teddybearsprompts')
  ).'</a>';
  
  $html_template = apply_filters('teddybear/project/system/getoption', 'cusrev-completedorder-template', '{{button}}');
  $html_template = str_replace([
    '{{button}}', '{{link}}'
  ], [
    $html_button, $review_link
  ], $html_template);

  ?>
  <table style="width: 100%;">
    <tbody>
      <tr>
        <td><?php echo esc_html(__('Please let us know how you feel with your order (only 4 clicks)', 'teddybearsprompts')); ?></td>
        <!-- colspan="2" -->
      <tr>
        <!-- <td></td> -->
        <td>
          <?php echo $html_template; ?>
        </td>
      </tr>
    </tbody>
  </table>
  <?php
endif;
?>