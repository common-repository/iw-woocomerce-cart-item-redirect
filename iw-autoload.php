<?php
namespace IW\WooCommerce;

// change cart item link
add_filter(
    'woocommerce_cart_item_name',
    array('IW\WooCommerce\RedirectCartLink', 'SetCartItemLink' ), 10, 3
);
// set fields
add_action(
    'woocommerce_product_options_general_product_data',
    array('IW\WooCommerce\RedirectCartLink', 'AddCartItemLink')
);
// Save Fields
add_action(
    'woocommerce_process_product_meta',
    array('IW\WooCommerce\RedirectCartLink', 'SaveCartItemLink')
);



class RedirectCartLink
{
    /**
     * redirect product link to a page if user specify it, otherwise use default
     */
    public static function SetCartItemLink($link, $item, $key){
      $page_id = get_post_meta( $item['product_id'], '_iw_page_id', true );
      if ($page_id) {
        $page_title = get_post_field('post_title', $item['product_id']) ;
        $mylink = '<a href=' . get_permalink( $page_id) . ' class="iw-cart-name" >' .
        $page_title . '</a>';
        $link = $mylink;
      }
      return $link;
    }


    /**
     * Set the Cart Page Item Link Redirection
     */
    public static function AddCartItemLink()
    {
        global $woocommerce, $post;

        $option = '';
        $default_product = '';
        $id = '_iw_page_id';
        $selected_id = get_post_meta($post->ID, $id, true);

        /// product has not been assigned to new page, use the default post ID
        if (! $selected_id) {
          $selected_id = $post->ID;
        } else {
          $default_product = '<option value="' . $post->ID . '">' .
            esc_attr(__(get_the_title($post->ID))) . ' - default</option>';
        }
        $page_title = get_the_title($selected_id);

        $pages = get_pages(['exclude' => $selected_id]);
        foreach ($pages as $page) {
          $option .= '<option value="'.$page->ID.'">';
          $option .= $page->post_title;
          $option .= '</option>';
        }

        $param = [
            'numberposts' => -1,
            'exclude' => $selected_id
        ];
        $posts = get_posts($param);
        foreach ($posts as $post) {
          $option .= '<option value="'.$post->ID.'">';
          $option .= $post->post_title;
          $option .= '</option>';
        }

        /// append the default_prodct if has been set to other page
        $option .= $default_product;

?>

  <div class="options_group">
    <p class="form-field iw-page-id-input">
      <label for="iw-page-id">
        <?php echo __('Cart Item Link', 'woocommerce');?>
      </label>
      <span class="wrap">
        <select name="_iw_page_id">
         <option value="<?php echo $selected_id;?>">
           <?php echo esc_attr(__($page_title));?>
         </option>
         <?php echo $option; ?>
        </select>
      </span>
      <span class="description">
        <?php _e('Page to load when product link in cart page is clicked', 'woocommerce');
        ?></span>
      </p>
    </div>

<?php

    }

    /**
     * save the cart item link set inSetCartItemLink
     */
    function SaveCartItemLink($post_id){

      $woocommerce_text_field = $_POST['_iw_page_id'];
      if( !empty( $woocommerce_text_field ) )
      update_post_meta( $post_id, '_iw_page_id',
      esc_attr( $woocommerce_text_field ) );

    }
}
