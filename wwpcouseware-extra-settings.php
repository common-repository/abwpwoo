<?php
/**
 * Plugin Name: Addon for AB-Inspiration, WooCommerce and WP Courseware
 * Version:     5.4
 * Plugin URI:  https://ab-inspiration.com
 * Description: The official extension AB-Inspiration for add integration for WP Courseware and WooCommmerce.
 * Author:      Anfisa Breus
 * Author URI:  https://anfisabreus.ru
 * Text Domain: wpcoursewarextra
 * Domain Path: /languages
 * License:     GPL v2 or later
 */
/***************Plugin Functions****************/

function my_plugin_load_plugin_textdomain() {
  load_plugin_textdomain('wpcoursewarextra', FALSE, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'my_plugin_load_plugin_textdomain');

function wpcw_custom_script() {
  // Register the script
  wp_register_script('wpcw_custom', plugin_dir_url(__FILE__) . 'js/wpcw-custom.js', array('jquery'), true);

  // Localize the script with new data
  $translation_array = array(
    'course_complete_text' => __(' ', 'wpcoursewarextra')
  );
  wp_localize_script('wpcw_custom', 'course_complete', $translation_array);

  // Enqueued script with localized data.
  wp_enqueue_script('wpcw_custom');
}
add_action('wp_enqueue_scripts', 'wpcw_custom_script');
function ABIWPWOO_register_settings() {
  $defaults = array(
    'sanitize_callback' => null,
    'default'           => null
  );
  add_option('ab_wpcourseware', 'Дополнительные настройки');
  register_setting('abwpwoo_options_group', 'ab_wpcourseware', $defaults);
}
add_action('admin_init', 'ABIWPWOO_register_settings');
function abwpwoo_register_options_page() {
  add_submenu_page('wpcw', __('Доп. настройки', 'wpcoursewarextra'), __('Доп. настройки', 'wpcoursewarextra'), 'manage_options', 'abwpwoo', 'abwpwoo_options_page');
}
add_action('admin_menu', 'abwpwoo_register_options_page', 11);

$ab_wpcourseware = get_option('ab_wpcourseware');
function abwpwoo_options_page() { ?>
  <div>
    <h2><?php _e('Дополнительные настройки для плагина WP Courseware', 'wpcoursewarextra'); ?></h2>
    <form method="post" action="options.php">
      <?php settings_fields('abwpwoo_options_group'); ?>
      <div>
        <table class="form-table">
          <tr valign="top">
            <th scope="row"><?php _e('Исключить курсы из каталога:', 'wpcoursewarextra'); ?></th>
            <td colspan="2">
              <?php function abwpwoo_courses($post_type) { // post_list  
                    global $ab_wpcourseware;

                    $items = get_posts(
                      array(
                        'post_type' => $post_type,
                        'posts_per_page' => -1
                      )
                    );

                    $abwpcourseware = $ab_wpcourseware['id_courses'] ?? '';
                    $a = (array) $abwpcourseware;

                    foreach ($items as $item) { ?>
                  <input type="checkbox" value="<?php echo $item->ID; ?>" name="ab_wpcourseware[id_courses][]"
                    id="<?php echo $item->ID; ?>" <?php echo in_array($item->ID, $a) ? 'checked' : ''; ?> />
                  <label for="<?php echo $item->ID; ?>"><?php echo $item->post_title; ?></label><br />
                  <?php
                    } // end foreach      
                  }
                  echo abwpwoo_courses('wpcw_course'); ?>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Курс + Товар:', 'wpcoursewarextra'); ?></th>
            <td colspan="2">
              <?php function abwpwoo_courses_products() { // post_list  
                    global $ab_wpcourseware;

                    $abwpcourseware = $ab_wpcourseware['id_courses'] ?? '';
                    $a = (array) $abwpcourseware;
                    $abwpcoursewareb = $ab_wpcourseware['id_courses_product'] ?? '';
                    $b = (array) $abwpcoursewareb;

                    $items = get_posts(
                      array(
                        'post_type' => 'wpcw_course',
                        'posts_per_page' => -1,
                        'orderby' => 'ID',
                        'order' => 'ASC',
                      )
                    );
                    $itemsp = get_posts(
                      array(
                        'post_type' => 'product',
                        'posts_per_page' => -1
                      )
                    );

                    foreach ($items as $item => $itm) { ?>

              <tr style="<?php if (in_array($itm->ID, $a)) { ?>display:none;<?php } ?>">

                <td class="<?php echo $itm->ID; ?>">
                  <input type="checkbox" <?php if (!in_array($itm->ID, $a)) {
                    echo 'checked';
                  } ?> value="<?php echo $itm->ID; ?>" name="ab_wpcourseware[id_courses_courses][]" id="<?php echo $itm->ID; ?>"
                    style="display:none" />
                  <label for="<?php echo $itm->ID; ?>"><?php echo $itm->post_title; ?></label><br />

                </td>

                <td class="<?php echo $item; ?>">
                  <select name="ab_wpcourseware[id_courses_product][<?php echo $item; ?>]"
                    style="<?php if (in_array($itm->ID, $a)) { ?>display:none<?php } ?>">
                    <option value=""><?php _e('Курс бесплатный', 'wpcoursewarextra'); ?></option>
                    <?php foreach ($itemsp as $itemp => $it) { ?>

                      <?php if (!in_array($itm->ID, $a)) { ?>
                        <option value="<?php echo $it->ID; ?>" <?php if ($b[$item] == $it->ID) {
                             echo 'selected="selected"';
                           } ?>>
                          <?php echo $it->post_title; ?>
                        </option>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </td>
              </tr>
          <?php }
          }
             echo abwpwoo_courses_products(); ?>
          </td>
          </tr>

          <tr valign="top">
            <th scope="row"><?php _e('Курс + Страницы Лендинги:', 'wpcoursewarextra'); ?></th>
            <td colspan="2">

              <?php
              function abwpwoo_courses_pages() { // post_list  
                global $ab_wpcourseware;
                $abwpcourseware = $ab_wpcourseware['id_courses'] ?? '';
                $a = (array) $abwpcourseware;
                $abwpcoursewareb = $ab_wpcourseware['id_courses_pages'] ?? '';
                $b = (array) $abwpcoursewareb;

                $items = get_posts(
                  array(
                    'post_type' => 'wpcw_course',
                    'posts_per_page' => -1,
                    'orderby' => 'ID',
                    'order' => 'ASC',
                  )
                );

                $itemsp = get_posts(
                  array(
                    'post_type' => 'page',
                    'posts_per_page' => -1
                  )
                );

                $itemsl = get_posts(
                  array(
                    'post_type' => 'e-landing-page',
                    'posts_per_page' => -1
                  )
                );

              foreach ($items as $item => $itm) { ?>
              <tr style="<?php if (in_array($itm->ID, $a)) { ?>display:none;<?php } ?>">
                <td class="<?php echo $itm->ID; ?>"><input type="checkbox" <?php if (!in_array($itm->ID, $a)) {
                     echo 'checked';
                   } ?> value="<?php echo $itm->ID; ?>" name="ab_wpcourseware[id_courses_courses_pages][]"
                    id="<?php echo $itm->ID; ?>" style="display:none" />
                  <label for="<?php echo $itm->ID; ?>"><?php echo $itm->post_title; ?></label><br />

                </td>
                <td class="<?php echo $item; ?>">

                  <select name="ab_wpcourseware[id_courses_pages][<?php echo $item; ?>]"
                    style="<?php if (in_array($itm->ID, $a)) { ?>display:none<?php } ?>">
                    <?php if (!in_array($itm->ID, $a)) { ?>
                      <option value=""><?php _e('Оставить страницу курса', 'wpcoursewarextra'); ?></option>
                    <?php } ?>

                    <optgroup label="Страницы">
                      <?php foreach ($itemsp as $itemp => $it) { ?>
                        <?php if (!in_array($itm->ID, $a)) { ?>
                          <option value="<?php echo $it->ID; ?>" <?php if ($b[$item] == $it->ID) {
                               echo 'selected="selected"';
                             } ?>>
                            <?php echo $it->post_title; ?>
                          </option>
                        <?php } ?>
                      <?php } ?>
                    </optgroup>
                    <optgroup label="Лендинги Elementor">
                      <?php foreach ($itemsl as $itemp => $it) { ?>
                        <?php if (!in_array($itm->ID, $ab_wpcourseware["id_courses"])) { ?>
                          <option value="<?php echo $it->ID; ?>" <?php if ($b[$item] == $it->ID) {
                               echo 'selected="selected"';
                             } ?>>
                            <?php echo $it->post_title; ?>
                          </option>
                        <?php } ?>
                      <?php } ?>
                    </optgroup>
                  </select>
                </td>
              </tr>
            <?php }
              }
              echo abwpwoo_courses_pages(); ?>

          </td>
          </tr>

        </table>
      </div>

      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

function abwpwoo_headline_link() {
}

function abwpwoo_price_wpcourseware_woocommerce() {
}
function abwpwoo_display_gravatar($atts) {
  extract(shortcode_atts(array('wpb_user_email' => '', ), $atts));
  if (is_user_logged_in()) {
    if ($wpb_user_email == '') {
      global $current_user;
      wp_get_current_user();
      $getuseremail = $current_user->user_email;
      $getusername = $current_user->user_firstname;
      $getuserlastname = $current_user->user_lastname;
    } else {
      $getuseremail = $wpb_user_email;
    }
    $usergravatar = '//www.gravatar.com/avatar/' . md5($getuseremail) . '?s=100';

    echo '<div style=""><div style="float:left;margin-bottom:30px; "><img style="border:1px solid #eaeaea; border-radius:50%" src="' . $usergravatar . '" /></div><div style="float:left; font-size:24px; font-weight:bold; margin-top: 20px; margin-left: 20px;">' . $current_user->display_name;
    echo '</div></div> ';
    echo '<div style="clear:both"></div>';
  }
}

add_shortcode('wpb_gravatar', 'abwpwoo_display_gravatar');

add_filter('wpcw_ignore_active_membership_integration', '__return_true');

add_filter('wpcw_course_enrollment_success_message', function ($message, $course_id, $user_id) {
  // Do something with the message. Replace it, append to it, etc...
  $message .= __('Вы успешно зачислены на курс. Выбранные вами курсы смотрите в ЛИЧНОМ КАБИНЕТЕ в разделе КУРСЫ', 'wpcoursewarextra');
  return $message;
}, 10, 3);

function abwpwoo_content_progressBar($percentage, $cssClass = false, $extraHTML = false) {
  return sprintf(
    '
		<span class="wpcw_progress_wrap %s">
			<span class="wpcw_progress">
				<span class="wpcw_progress_bar" style="width: %d%%"></span>
			</span>
			<span class="wpcw_progress_percent">%d%%</span>
			%s
		</span>',
    $cssClass,
    $percentage,
    $percentage,
    $extraHTML
  );
}

/**
 * Course Enroll Shortcode.
 *
 * e.g. [wpcourse_enroll courses="2,3" enroll_text="Enroll Here"]
 *
 * @since 4.3.0
 *
 * @param array $atts The shortcode attributes.
 * @param string $content The shortcode content.
 *
 * @return string The course enroll button string.
 */
function course_enroll_shortcode_link($atts, $content = '') {
  $shortcode_atts = shortcode_atts(
    array(
      'courses' => false,
      'enroll_text' => esc_html__('Enroll Now', 'wp-courseware'),
      'purchase_text' => esc_html__('Purchase', 'wp-courseware'),
      'installments_text' => esc_html__('Installments', 'wp-courseware'),
      'display_messages' => true,
      'display_raw' => false,
      'redirect' => false,
    ), $atts, 'wpcourse_enroll');

  // Check for courses.
  if (!$shortcode_atts['courses'] && !is_null($shortcode_atts['courses'])) {
    return;
  }
  $courses_to_enroll = array();

  $courses = array();

  foreach ($courses as $key => $course) {
    $courses_to_enroll['course_id[0]'] = esc_html($shortcode_atts['courses']);
  }

  $courses_to_enroll['course_id[0]'] = esc_html($shortcode_atts['courses']);
  $enroll_text = esc_html($shortcode_atts['enroll_text']);

  $query_args = array($courses_to_enroll);
  $course_enrollment_url = wp_nonce_url(add_query_arg($query_args, wp_registration_url()), 'wpcw_enroll', '_wp_enroll');
  $shortcode_html .= sprintf(__('%s', 'wp-courseware'), esc_url_raw($course_enrollment_url), esc_html($enroll_text));

  return apply_filters('wpcw_course_enroll_shortcode_html', $shortcode_html);
}

add_shortcode('wpcourse_enroll_link', 'course_enroll_shortcode_link');