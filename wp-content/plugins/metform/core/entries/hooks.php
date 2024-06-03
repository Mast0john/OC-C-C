<?php
namespace MetForm\Core\Entries;

defined('ABSPATH') || exit;

class Hooks
{
    use \MetForm\Traits\Singleton;

    public function __construct()
    {

        add_filter('manage_metform-entry_posts_columns', [$this, 'set_columns']);
        add_action('manage_metform-entry_posts_custom_column', [$this, 'render_column'], 10, 2);
        add_filter('parse_query', [$this, 'query_filter']);
        add_filter('wp_mail_from_name', [$this, 'wp_mail_from']);

    }

    public function set_columns($columns)
    {

        $date_column = $columns['date'];

        unset($columns['date']);

		$columns['form_name'] = esc_html__('Form Name', 'metform');
		
        $columns['referral'] = esc_html__('Referral','metform');

        $columns['date'] = esc_html($date_column);

        

        return $columns;
    }

    public function render_column($column, $post_id)
    {
        switch ($column) {
            case 'form_name':
                $form_id = get_post_meta($post_id, 'metform_entries__form_id', true);
                $form_name = get_post((int) $form_id);
                $post_title = (isset($form_name->post_title) ? $form_name->post_title : '');

                global $wp;
                $current_url = add_query_arg($wp->query_string . "&mf_form_id=" . $form_id, '', home_url($wp->request));

                echo "<a data-metform-form-id=" . esc_attr($form_id) . " class='mf-entry-filter mf-entry-flter-form_id' href=" . esc_url($current_url) . ">" . esc_html($post_title) . "</a>";
                break;

            case 'referral':
                $page_id = get_post_meta( $post_id, 'mf_page_id',true );

                global $wp;
                $current_url = add_query_arg($wp->query_string . "&mf_ref_id=" . $page_id, '', home_url($wp->request));
                
				echo "<a class='mf-entry-filter mf-entry-flter-form_id' href='" . esc_url($current_url) . "'>".get_the_title($page_id)."</a>";
                break;
        }
    }

    public function query_filter($query)
    {
        global $pagenow;
        $current_page = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : '';

        if (
            is_admin()
            && 'metform-entry' == $current_page
            && 'edit.php' == $pagenow
            && $query->query_vars['post_type'] == 'metform-entry'
            && isset($_GET['mf_form_id'])
            && $_GET['mf_form_id'] != 'all'
        ) {
            $form_id = sanitize_key($_GET['mf_form_id']);
            $query->query_vars['meta_key'] = 'metform_entries__form_id';
            $query->query_vars['meta_value'] = $form_id;
            $query->query_vars['meta_compare'] = '=';
        }

        if (
            is_admin()
            && 'metform-entry' == $current_page
            && 'edit.php' == $pagenow
            && $query->query_vars['post_type'] == 'metform-entry'
            && isset($_GET['mf_ref_id'])
            && $_GET['mf_ref_id'] != 'all'
        ) {
            $page_id = sanitize_key($_GET['mf_ref_id']);
            $query->query_vars['meta_key'] = 'mf_page_id';
            $query->query_vars['meta_value'] = $page_id;
            $query->query_vars['meta_compare'] = '=';
        }
    }




    public function wp_mail_from($name)
    {
        return get_bloginfo('name');
    }

}
