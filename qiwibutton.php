<?php
/**
 * @package QiwiButton
 * @version 0.5.3
 */
 /*
Plugin Name: QIWI Button
Plagin URI: http://polzo.ru/qiwi-button
Description: Insert QIWI buy button in your post
Author: Alexander Kurganov
Version: 0.5.2
Author URI: http://akurganow.ru
*/

if (!defined("WP_CONTENT_URL")) define("WP_CONTENT_URL", get_option("siteurl") . "/wp-content");
if (!defined("WP_PLUGIN_URL"))  define("WP_PLUGIN_URL",  WP_CONTENT_URL        . "/plugins");

function qiwibutton_add_admin_page()
{
	add_options_page('QIWI Button Settings', 'QIWI Button', 8, 'qiwibutton', 'qiwibutton_option_page');
}
function qiwibutton_option_page()
{
	$com_url=get_bloginfo('url');
	
	echo "<h2>Настройка плагина QIWI Button</h2>";
	
	//Значения по умолчанию для настроек магазина
	add_option('qiwibutton_shop_id', 'Не задано');
	add_option('qiwibutton_lifetime', '24');
	add_option('qiwibutton_check_agt', true);
	add_option('qiwibutton_comment', 'Оплата на сайте '.$com_url.'');
	
	//Изменение данных магазина
	echo "<h3>Настройки магазина</h3>";
	qiwibutton_change_shop();
}

//Изменение данных магазина
function qiwibutton_change_shop()
{
	//Если форма была отправлена, то применить изменения магазина
	if (isset($_POST['qiwibutton_base_setup_btn'])) 
	{   
	   if ( function_exists('current_user_can') && 
			!current_user_can('manage_options') )
				die ( _e('Hacker?', 'qiwibutton') );

		if (function_exists ('check_admin_referer') )
		{
			check_admin_referer('qb_shop_setup_form');
		}

		$qiwibutton_shop_id = $_POST['qiwibutton_shop_id'];
		$qiwibutton_lifetime = $_POST['qiwibutton_lifetime'];
		$qiwibutton_check_agt = $_POST['qiwibutton_check_agt'];
		$qiwibutton_comment = $_POST['qiwibutton_comment'];

		update_option('qiwibutton_shop_id', $qiwibutton_shop_id);
		update_option('qiwibutton_lifetime', $qiwibutton_lifetime);
		update_option('qiwibutton_check_agt', $qiwibutton_check_agt);
		update_option('qiwibutton_comment', $qiwibutton_comment);
	}

	//Форма информации о магазине
	echo "<form name='qiwibutton_base_setup' method='post' action='".$_SERVER['PHP_SELF']."?page=qiwibutton&amp;updated=true'>";
	
	if (function_exists ('wp_nonce_field') )
	{
		wp_nonce_field('qb_shop_setup_form'); 
	}
	echo
	"
		<table class='form-table'>
			<tr valign='top'>
				<th scope='row'><label for='qiwibutton_shop_id'>Ваш id в системе:</label></th>
				<td><input type='text' name='qiwibutton_shop_id' value='".get_option('qiwibutton_shop_id')."' style='width:300px;'/>
					<span class='description'>id можно узнать в <a href='http://ishopnew.qiwi.ru/'>личном кабинете</a></span></td>
			</tr>
			<tr valign='top'>
				<th scope='row'><label for='qiwibutton_lifetime'>Время действия счёта:</label></th>
				<td><input type='number' min='1' max='1080' name='qiwibutton_lifetime' value='".get_option('qiwibutton_lifetime')."' style='width:292px;'/>
					<span class='description'>В часах, не может превышать 45 суток, т.е 1080 часов</span></td>
			</tr>
			<tr valign='top'>
				<th scope='row'><label for='qiwibutton_comment'>Комментарий по умолчанию:</label></th>
				<td><input type='text' name='qiwibutton_comment' value='".get_option('qiwibutton_comment')."' style='width:300px;'/>
					<span class='description'>Комментарий отображается в счете</span></td>
			</tr>
			<tr valign='top'>
				<th scope='row'>Только зарегистрированным:</th>
				<td>					
					<label for='qiwibutton_check_agt'>
					<input type='checkbox' name='qiwibutton_check_agt' ";if(get_option('qiwibutton_check_agt'))echo"checked";echo "/>
					Проверять, зарегистрирован ли пользователь в «QIWI Кошельке»					
					</label>
				</td>
			</tr>
		</table>
		<div style='margin:25px 0 0'>
			<input type='submit' name='qiwibutton_base_setup_btn' value='Сохранить изменения' class='button-primary'/>
		</div>	
	</form>
	";
}

function qiwibutton_frame_script() {
	echo "<script type='text/javascript'>
		$(document).ready(function() {
			
			$('.qiwiframe_link').fancybox({
				width    : 560,
				minHeight   : 570,
				scrolling: 'no',
				type	 : 'iframe',
				iframe   : {scrolling:'no'},
			});
		});
	</script>";
}
function qiwibutton_shortcode($atts) {
	
	$img_url= WP_PLUGIN_URL . "/qiwi-button/images";
	$dir= WP_PLUGIN_URL . "/qiwi-button";
	$check_agt_option=get_option('qiwibutton_check_agt');
	$com=single_post_title('',false);
	$blog_url=get_bloginfo('url');
	if ($check_agt_option) {
		$check_agt='true'; 
	}
	else {
		$check_agt='false'; 
	}
	
	extract(shortcode_atts(array(
     "rub" => '1000',
	 "kop" => '00',
	 "title" => 'Купить',
	 "descr" => 'Описание'
     ), $atts));
	 
	return '
	<table class="qiwibtn_table">
	<tr>
	<td class="qiwibtn_description">
	<p>'.$descr.'</p>
	</td>
	<td class="qiwibtn_container">
		<a id="qiwiframe_link" class="qiwibtn qiwiframe_link" href="'.$dir.'/qiwiframe.php?from='.get_option('qiwibutton_shop_id').'&summ='.$rub.'.'.$kop.'&com='.get_option('qiwibutton_comment').'&lifetime='.get_option('qiwibutton_lifetime').'&check_agt='.$check_agt.'&iframe=true&url='.$blog_url.'">
			<span class="qiwibtn_icon"><span></span></span>
			<span class="qiwibtn_hidden">'.$rub.' р.</span>
			<span class="qiwibtn_text">'.$title.'</span>
		</a>
	</td>
	</tr>
	</table>';		
}

function qiwibutton_reg_css() {
	wp_register_style( 'fancybox', WP_PLUGIN_URL . '/qiwi-button/fancybox/jquery.fancybox.css' );
	wp_register_style( 'qiwibutton', WP_PLUGIN_URL . '/qiwi-button/btnstyle.css' );
	
	wp_enqueue_style( 'fancybox' );
	wp_enqueue_style( 'qiwibutton' );
}

function qiwibutton_reg_js() {
    if (is_admin()) {
	} 
	else {	
	wp_deregister_script('fancybox');
	wp_register_script('fancybox', WP_PLUGIN_URL . '/qiwi-button/fancybox/jquery.fancybox.pack.js', 'jquery');
	
	wp_deregister_script('easing');
	wp_register_script('easing', "http://yandex.st/jquery/easing/1.3/jquery.easing.min.js", 'jquery');
	
	wp_enqueue_script('fancybox');
	wp_enqueue_script('easing');
	}
}

add_action('wp_print_scripts', 'qiwibutton_reg_js');
add_action('wp_print_styles', 'qiwibutton_reg_css');
add_action('wp_head', 'qiwibutton_frame_script');
add_action('admin_menu', 'qiwibutton_add_admin_page');
add_shortcode('qiwibtn', 'qiwibutton_shortcode');
?>