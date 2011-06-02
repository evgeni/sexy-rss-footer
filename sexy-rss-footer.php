<?php
/*
Plugin Name: Sexy RSS Footer
Plugin URI: http://github.com/evgeni/sexy-rss-footer
Description: append user-defined information to the end of every feed etry
Version: 0.1
Author: Evgeni Golov
Author URI: http://www.die-welt.net
License: GPL2
Text Domain: sexy-rss-footer
Domain Path: /languages
*/

/*  Copyright 2011 Evgeni Golov <sargentd@die-welt.net>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Add a new options page for sexy-rss-footer
*/
add_action('admin_menu', 'srf_add_options_page');

function srf_add_options_page() {
    $page = add_options_page('Sexy RSS Footer Options', 'Sexy RSS Footer', 'administrator', __FILE__, 'srf_options_page');
    add_action('admin_print_styles-'.$page, 'srf_admin_styles');
    add_action('admin_print_scripts-'.$page, 'srf_admin_scripts');
}

function srf_admin_styles(){
    wp_enqueue_style( 'sexy-rss-footer-style');
}
function srf_admin_scripts(){
    wp_enqueue_script( 'sexy-rss-footer-js');
}

/**
* Print the options page for sexy-rss-footer
*/
function srf_options_page() {
    echo '<div class="wrap">
    <div class="icon32" id="icon-options-general"><br></div>
    <h2>'.__('Sexy RSS Footer Options', 'sexy-rss-footer').'</h2>
    <p>'.__('Sexy RSS Footer enables you to add any possible content at the end of every feed entry.', 'sexy-rss-footer').'<br/>
    '.__('You can do something like:', 'sexy-rss-footer').'
    <blockquote>'.__('5 comment(s) | a post from your nice site by author', 'sexy-rss-footer').'</blockquote>
    '.__('with:', 'sexy-rss-footer').'
    <blockquote><pre>'.__('{COMMENTS_COUNT} comments | a post from your nice site by {POST_AUTHOR}', 'sexy-rss-footer').'</pre></blockquote>
    </p>
    <form action="options.php" method="post">';
    settings_fields('sexy-rss-footer');
    echo '<table class="form-table"> ';
    do_settings_fields(__FILE__, 'default');
    echo '</table>
    <p class="submit">
    <input name="Submit" type="submit" class="button-primary" value="Save Changes" />
    </p>
    </form>
    </div>';
}

/**
* Register options for sexy-rss-footer
*/
add_action('admin_init', 'srf_admin_init' );

function srf_admin_init(){
    register_setting('sexy-rss-footer', 'sexy-rss-footer');
    add_settings_field('srf_footer_template', __('Footer Template', 'sexy-rss-footer'), 'srf_footer_template_input', __FILE__);
    add_settings_field('srf_flattr_link', __('Flattr Link', 'sexy-rss-footer'), 'srf_flattr_link_input', __FILE__);
    $srf_style_url  = WP_PLUGIN_URL . '/sexy-rss-footer/sexy-rss-footer.css';
    $srf_style_file = WP_PLUGIN_DIR . '/sexy-rss-footer/sexy-rss-footer.css';
    if ( file_exists($srf_style_file) ) {
        wp_register_style('sexy-rss-footer-style', $srf_style_url);
    }
    wp_register_script('sexy-rss-footer-js', WP_PLUGIN_URL . '/sexy-rss-footer/sexy-rss-footer.js', array('jquery'), false, true);
}

/**
* Print input fields for the options page
*/
function srf_footer_template_input(){
    $options = get_option('sexy-rss-footer');
    $current_user = wp_get_current_user();
    echo '<textarea id="srf_footer_template" name="sexy-rss-footer[srf_footer_template]" rows="10">'.$options['srf_footer_template'].'</textarea>
    <span class="description srf_description">
'.__('The following variables will get replaced:', 'sexy-rss-footer').'
<table class="srf_help_table">
<tr><th style="width:20%">'.__('variable', 'sexy-rss-footer').'</th><th style="width:20%">'.__('replacement', 'sexy-rss-footer').'</th><th>'.__('example', 'sexy-rss-footer').'</th></tr>
<tr><td><span title="click to use">{POST_TITLE}</span></td><td>get_the_title_rss()</td><td>Sexy RSS Footer is SEXY!</td></tr>
<tr><td><span title="click to use">{POST_LINK}</span></td><td>get_permalink()</td><td>'.site_url().'?p=1</td></tr>
<tr><td><span title="click to use">{POST_AUTHOR}</span></td><td>get_the_author()</td><td>'.$current_user->display_name.'</td></tr>
<tr><td><span title="click to use">{COMMENTS_LINK}</span></td><td>get_comments_link()</td><td>'.site_url().'?p=1#comments</td></tr>
<tr><td><span title="click to use">{COMMENTS_COUNT}</span></td><td>get_comments_number()</td><td>1000</td></tr>
<tr><td><span title="click to use">{FLATTR_LINK}</span></td><td>the link below</td><td>http://flattr.com/profile/evgeni</td></tr>
</table>
</span>';
}
function srf_flattr_link_input(){
    $options = get_option('sexy-rss-footer');
    echo '<input id="srf_flattr_link" name="sexy-rss-footer[srf_flattr_link]" value="'.$options['srf_flattr_link'].'"/>
    <span class="description srf_description">
    '.__('The link to the flattr thing for this site.', 'sexy-rss-footer').'
    </span>';
}


/**
* Set default options for sexy-rss-footer
*/
register_activation_hook(__FILE__, 'srf_set_defaults');

function srf_set_defaults(){
    $arr = array('srf_footer_template'=>'<a href="{COMMENTS_LINK}">{COMMENTS_COUNT} comment(s)</a>', 'srf_flattr_link' => '');
    update_option('sexy-rss-footer', $arr);
}


/**
* Append the user-set footer to the content of the feed, replacing some "variables"
* @param  $content Content of post
* @return string
*/
add_filter( "the_content_feed", "srf_extend_feed");

function srf_extend_feed($content)
{
    $options = get_option('sexy-rss-footer');
    $appendstring = $options['srf_footer_template'];
    $appendstring = str_ireplace('{POST_TITLE}', get_the_title_rss(), $appendstring);
    $appendstring = str_ireplace('{POST_LINK}', get_permalink(), $appendstring);
    $appendstring = str_ireplace('{POST_AUTHOR}', get_the_author(), $appendstring);
    $appendstring = str_ireplace('{COMMENTS_LINK}', get_comments_link(), $appendstring);
    $appendstring = str_ireplace('{COMMENTS_COUNT}', get_comments_number(), $appendstring);
    $appendstring = str_ireplace('{FLATTR_LINK}', $options['srf_flattr_link'], $appendstring);
    $content .= '<p class="sexy-rss-footer">'.$appendstring.'</p>';
    return $content;
}

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain('sexy-rss-footer', null, $plugin_dir.'/languages' );

?>
