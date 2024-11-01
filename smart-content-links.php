<?php
/*
Plugin Name: Smart Content Links
Plugin URI: https://tooltips.org/
Description: Smart Content Links
Version: 1.2.0
Author: Tomas
Author URI: https://tooltips.org/
License: GPLv3
*/
/*  Copyright 2011 - 2023 Tomas Zhu https://tooltips.org/
    This program comes with ABSOLUTELY NO WARRANTY;
    https://www.gnu.org/licenses/gpl-3.0.html
    https://www.gnu.org/licenses/quick-guide-gplv3.html
*/
if ( ! defined( 'ABSPATH' ) ) exit;

function sclf_add_hello_world($content) {
	$sclfo_information_bar_data = get_option('sclfo_information_bar_data');
	if (empty($sclfo_information_bar_data))
	{
		$sclfo_information_bar_data['position'] = 'add to head of post'; 
		$sclfo_information_bar_data['width'] = '100%';
		$sclfo_information_bar_data['bg_color'] = '#eeeeee';
	}

    $text = get_option('sclfo_smart_content_link_data');
    $bg_color = '#cccccc';
    $bg_color = isset($sclfo_information_bar_data['bg_color']) ? $sclfo_information_bar_data['bg_color'] : '#cccccc';
    $width = isset($sclfo_information_bar_data['width']) ? $sclfo_information_bar_data['width'] : '100%';
    $output = "<div style='background-color: ".esc_attr($bg_color)."; width: ".esc_attr($width)."; max-width: ".esc_attr($width)."'>";
        
    foreach ($text as $data) {
        $output .= "<span style='margin: ".esc_attr($data['margin'])."; padding: ".esc_attr($data['padding']).";'><a href='".esc_attr($data['link'])."' style='color: ".esc_attr($data['color'])." !important; visited: ".esc_attr($data['color'])." !important;'>".esc_attr($data['item'])."</a></span>";
    }
    $output .= "</div>";
	
	if (!(empty($sclfo_information_bar_data['position'])))
	{
		if('add to head of post' == $sclfo_information_bar_data['position'])
		{
			$content = $output.$content;
		}
		
		if('add to bottom of post' == $sclfo_information_bar_data['position'])
		{
			$content = $content.$output;
		}
		
		if('add to head and bottom of post' == $sclfo_information_bar_data['position'])
		{
			$content = $output.$content.$output;
		}
	}
    

    return $content;
     
}

add_filter('the_content', 'sclf_add_hello_world',10,1);

function sclf_add_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('custom-script', plugins_url('/js/custom-script.js', __FILE__), array('jquery', 'wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'sclf_add_custom_scripts');

function sclf_add_custom_menu_item() {
    add_menu_page(
        'Smart Content Links', 
        'Smart Content Links', 
        'manage_options', 
        'customize-header-info', 
        'sclf_customize_header_info_page', 
        'dashicons-admin-generic', 
        30 
    );
}

add_action('admin_menu', 'sclf_add_custom_menu_item');

function sclf_customize_header_info_page() {
    

    if(isset($_POST['item']) && isset($_POST['link']) && isset($_POST['color']) && isset($_POST['margin']) && isset($_POST['padding']) ) 
    {
        check_admin_referer ( 'information_bar_nonce' );	
        $items = array_map('sanitize_text_field', $_POST ['item']);
        $links = array_map('sanitize_url', $_POST ['link']);
        $colors = array_map('sanitize_text_field', $_POST ['color']);
        $margins = array_map('sanitize_text_field', $_POST ['margin']);
        $paddings = array_map('sanitize_text_field', $_POST ['padding']);

        $data = array();
        for($i = 0; $i < count($items); $i++) {
            $data[] = array(
                'item' => sanitize_text_field( $items[$i] ),
                //'link' => esc_url_raw( $links[$i] ),
                'link' => sanitize_url( $links[$i] ),
                'color' => sanitize_hex_color( $colors[$i] ),
                'margin' => sanitize_text_field( $margins[$i] ),
                'padding' => sanitize_text_field( $paddings[$i] ),
            );
        }
        update_option('sclfo_smart_content_link_data', $data);
    }
    $sclfo_smart_content_link_data = get_option('sclfo_smart_content_link_data');    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form id="my-form" method="post" action="">
            <?php
                wp_nonce_field( 'information_bar_nonce'); 
            ?>
            
    
            <div class="wrap">
                <h3>Item Settings Panel</h3>
                <div style="border: 1px solid #ccc; padding: 10px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Text</th>
                                <th>Item Link</th>
                                <th>Color</th>
                                <th>Margin</th>
                                <th>Padding</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <?php
                            	if ((isset($sclfo_smart_content_link_data)) && (!(empty($sclfo_smart_content_link_data))))
                            	{
                                foreach ($sclfo_smart_content_link_data as $data) {
                            ?>                        
                            <tr class="item">
                                <td><input type="text" name="item[]" placeholder="Item Text" value="<?php echo esc_attr( $data['item'] ); ?>" style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="link[]" placeholder="Item Link" value="<?php echo esc_attr( $data['link'] ); ?>" style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="color[]" class="color-field" value="<?php echo esc_attr( $data['color'] ); ?>" style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="margin[]" placeholder="Margin" value="<?php echo esc_attr( $data['margin'] ); ?>" style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="padding[]" placeholder="Padding" value="<?php echo esc_attr( $data['padding'] ); ?>" style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><button class="remove-item" style="background-color: #ccc; border: none; padding: 5px 10px; color: #fff; border-radius: 5px;">Remove</button></td>
                            </tr>
                            <?php
                                }
                            	}
                            	else 
                            	{
                            		?>
                            <tr class="item">
                                <td><input type="text" name="item[]" placeholder="Item Text"  style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="link[]" placeholder="Item Link"  style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="color[]" class="color-field"  style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="margin[]" placeholder="Margin"  style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><input type="text" name="padding[]" placeholder="Padding"  style="border: 1px solid #ccc; padding: 5px;"></td>
                                <td><button class="remove-item" style="background-color: #ccc; border: none; padding: 5px 10px; color: #fff; border-radius: 5px;">Remove</button></td>
                            </tr>     
                            <?php                        		
                            	}
                            ?>                                    
                        </tbody>
                    </table>
                    <button id="add-item" style="background-color: #4CAF50; border: none; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; border-radius: 5px;">Add Item</button>
                    <?php 
                    submit_button( 'Save Settings', 'primary', 'submit', false, array( 'style' => 'background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; border-radius: 5px; height: 40px;' ) );
                    ?>

                </div>
            </div>
        </form>

<?php
if(isset($_POST['width']) && isset($_POST['bg_color']) && isset($_POST['position'])) 
{
    check_admin_referer ( 'information_bar_nonce' );	
    $width = sanitize_text_field( $_POST['width'] );
    $bg_color = sanitize_hex_color( $_POST['bg_color'] );
    $position = sanitize_text_field( $_POST['position'] );
    $data = array(
        'width' => $width,
        'bg_color' => $bg_color,
        'position' => $position
    );
    update_option('sclfo_information_bar_data', $data);
}
?>

<form method="post" action="">
<hr style="margin-top:30px;" />
	<h3 style="margin-top:30px;">Information Bar Settings</h3>
    <div class="wrap" style="border: 1px solid #ccc; padding: 10px;">
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="width">Width</label></th>
                    
                    
                        <?php
                            $sclfo_information_bar_data = get_option('sclfo_information_bar_data');
                            
                            $width = isset($sclfo_information_bar_data['width']) ? $sclfo_information_bar_data['width'] : '100%';
                            $bg_color = isset($sclfo_information_bar_data['bg_color']) ? $sclfo_information_bar_data['bg_color'] : '#cccccc';
                            $position = isset($sclfo_information_bar_data['position']) ? $sclfo_information_bar_data['position'] : 'add to head of post';
                        ?>
                    <td><input name="width" type="text" id="width" value="<?php echo esc_attr($width); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bg_color">Background Color</label></th>
                    
                    <td>
                    <input name="bg_color" type="text" id="bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-field-bar">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="position">Position</label></th>
                    <td>

                        <select name="position" id="position">
                            <option value="add to head of post" <?php selected(esc_attr($position), 'add to head of post'); ?>>Add to Head of Post</option>
                            <option value="add to bottom of post" <?php selected(esc_attr($position), 'add to bottom of post'); ?>>Add to Bottom of Post</option>
                            <option value="add to head and bottom of post" <?php selected(esc_attr($position), 'add to head and bottom of post'); ?>>Add to Head and Bottom of Post</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php 
            wp_nonce_field( 'information_bar_nonce'); 
        ?>
        <?php 
		submit_button( 'Save Settings', 'primary', 'submit', false, array( 'style' => 'background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; border-radius: 5px; height: 40px;' ) );        
        ?>
        
    </div>
</form>


    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#add-item').click(function(e) {
                e.preventDefault();

                                
                $('#items-container').append(`
                    <tr class="item">
                        <td><input type="text" name="item[]" placeholder="Item Text" style="border: 1px solid #ccc; padding: 5px;"></td>
                        <td><input type="text" name="link[]" placeholder="Item Link" style="border: 1px solid #ccc; padding: 5px;"></td>
                        <td><input type="text" name="color[]" class="color-field" value="#000000" style="border: 1px solid #ccc; padding: 5px;"></td>
                        <td><input type="text" name="margin[]" placeholder="Margin" style="border: 1px solid #ccc; padding: 5px;"></td>
                        <td><input type="text" name="padding[]" placeholder="Padding" style="border: 1px solid #ccc; padding: 5px;"></td>
                        <td><button class="remove-item" style="background-color: #ccc; border: none; padding: 5px 10px; color: #fff; border-radius: 5px;">Remove</button></td>
                    </tr>
                `);
                $('.color-field').wpColorPicker();
            });
            $(document).on('click', '.remove-item', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
            $('.color-field').wpColorPicker();
            $('.color-field-bar').wpColorPicker();
        });
    </script>
    <?php
}

