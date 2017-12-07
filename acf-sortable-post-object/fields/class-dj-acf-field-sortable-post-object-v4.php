<?php

if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('dj_acf_field_sortable_post_object') ) :
class dj_acf_field_sortable_post_object extends acf_field {
  var $settings,
    $defaults;
  
  function __construct( $settings )
  {
    $this->name = 'sortable_post_object';
    $this->label = __('Sortable Post Object');
    $this->category = __("Relational",'acf-sortable-post-object');
    $this->defaults = array();
    parent::__construct();
    $this->settings = $settings;
  }
  
  function create_options( $field )
  {
    // vars
    $key = $field['name'];
    
    ?>
    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label for=""><?php _e("Post Type",'acf-sortable-post-object'); ?></label>
      </td>
      <td>
        <?php 
        
        $choices = array(
          'all' =>  __("All",'acf-sortable-post-object')
        );
        $choices = apply_filters('acf/get_post_types', $choices);
        
        
        do_action('acf/create_field', array(
          'type'  =>  'select',
          'name'  =>  'fields['.$key.'][post_type]',
          'value' =>  $field['post_type'],
          'choices' =>  $choices,
          'multiple'  =>  1,
        ));
        
        ?>
      </td>
    </tr>
    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e("Filter from Taxonomy",'acf-sortable-post-object'); ?></label>
      </td>
      <td>
        <?php 
        $choices = array(
          '' => array(
            'all' => __("All",'acf-sortable-post-object')
          )
        );
        $simple_value = false;
        $choices = apply_filters('acf/get_taxonomies_for_select', $choices, $simple_value);
        
        do_action('acf/create_field', array(
          'type'  =>  'select',
          'name'  =>  'fields['.$key.'][taxonomy]',
          'value' =>  $field['taxonomy'],
          'choices' => $choices,
          'multiple'  =>  1,
        ));
        
        ?>
      </td>
    </tr>
    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e("Allow Null?",'acf-sortable-post-object'); ?></label>
      </td>
      <td>
        <?php
        
        do_action('acf/create_field', array(
          'type'  =>  'radio',
          'name'  =>  'fields['.$key.'][allow_null]',
          'value' =>  $field['allow_null'],
          'choices' =>  array(
            1 =>  __("Yes",'acf-sortable-post-object'),
            0 =>  __("No",'acf-sortable-post-object'),
          ),
          'layout'  =>  'horizontal',
        ));
        
        ?>
      </td>
    </tr>
    <?php
  }
  
  function create_field( $field )
  {
    global $post;
    
    $field = array_merge($this->defaults, $field);

    $args = array(
      'numberposts' => -1,
      'post_type' => null,
      'orderby' => 'title',
      'order' => 'ASC',
      'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
      'suppress_filters' => false,
    );
    
    if( in_array('all', $field['post_type']) )
    {
      $field['post_type'] = apply_filters('acf/get_post_types', array());
    }
    
    if( ! in_array('all', $field['taxonomy']) )
    {
      $taxonomies = array();
      $args['tax_query'] = array();
      foreach( $field['taxonomy'] as $v )
      {
        $term = explode(':', $v); 
        if( !is_array($term) || !isset($term[1]) )
        {
          continue;
        }
        $taxonomies[ $term[0] ][] = $term[1];
      }
      
      foreach( $taxonomies as $k => $v )
      {
        $args['tax_query'][] = array(
          'taxonomy' => $k,
          'field' => 'id',
          'terms' => $v,
        );
      }
    }
    
    $field['type'] = 'text';
    $choices = array();

    foreach( $field['post_type'] as $post_type )
    {
      $args['post_type'] = $post_type;
      $get_pages = false;
      if( is_post_type_hierarchical($post_type) && !isset($args['tax_query']) )
      {
        $args['sort_column'] = 'menu_order, post_title';
        $args['sort_order'] = 'ASC';
        
        $get_pages = true;
      }
      
      $args = apply_filters('acf/fields/post_object/query', $args, $field, $post);
      $args = apply_filters('acf/fields/post_object/query/name=' . $field['_name'], $args, $field, $post );
      $args = apply_filters('acf/fields/post_object/query/key=' . $field['key'], $args, $field, $post );
      
      
      if( $get_pages )
      {
        $posts = get_pages( $args );
      }
      else
      {
        $posts = get_posts( $args );
      }
      
      if($posts) {
        foreach( $posts as $p ) {
          $title = get_the_title( $p->ID );
          if( $title === '' ) {
            $title = __('(no title)', 'acf-sortable-post-object');
          }
          
          if( $p->post_type != 'attachment' ) {
            $ancestors = get_ancestors( $p->ID, $p->post_type );
            $title = str_repeat('- ', count($ancestors)) . $title;
          }

          if( get_post_status( $p->ID ) != "publish" ) {
            $title .= ' (' . get_post_status( $p->ID ) . ')';
          }
          
          if( defined('ICL_LANGUAGE_CODE') ) {
            $title .= ' (' . ICL_LANGUAGE_CODE . ')';
          }
          
          $title = apply_filters('acf/fields/post_object/result', $title, $p, $field, $post);
          $title = apply_filters('acf/fields/post_object/result/name=' . $field['_name'] , $title, $p, $field, $post);
          $title = apply_filters('acf/fields/post_object/result/key=' . $field['key'], $title, $p, $field, $post);
          
          $choices[ $p->ID ] = array( 'title' => $title, 'post_type' => get_post_type_object( $p->post_type )->labels->name );
        }
      }
    }

    $valid_values = array();
    foreach ($field['value'] as $v) {
      if( array_key_exists($v, $choices) ) {
        $valid_values[$v] = $choices[$v];
      }
    }
    
    $available_choices = array();
    foreach ($choices as $key => $value) {
      if( array_key_exists($key, $valid_values) === FALSE ) {
        $available_choices[$key] = $value;
      }
    }

    $field['value'] = join(',', $field['value']);
    ?>
      <div id="<?php echo uniqid('acf_spo_'); ?>" class="acf-field-sortable-post-object-wrap">
        <?php 
          do_action('acf/create_field', $field );
        ?>
        <ul class="acf-field-sortable-post-object-available">
          <?php
            foreach ($available_choices as $key => $value) : 
          ?>
          <li data-id="<?php echo $key;?>">
            <label><?php echo $value['title']; ?></label>
          </li>
          <?php
            endforeach;
          ?>
        </ul>
        <ul class="acf-field-sortable-post-object-chosen">
          <?php
            foreach ($valid_values as $key => $value) : 
          ?>
          <li data-id="<?php echo $key;?>">
            <label><?php echo $value['title']; ?></label>
          </li>
          <?php
            endforeach;
          ?>
        </ul>

      </div>
    <?php
  }

  function input_admin_enqueue_scripts()
  {
    $url = $this->settings['url'];
    $version = $this->settings['version'].time();

    wp_enqueue_script('acf-sortable-post-object-lib', "{$url}assets/js/jquery-ui-sortable.min.js", array('jquery'));
    wp_register_script('acf-sortable-post-object', "{$url}assets/js/input.js", array('acf-input','acf-sortable-post-object-lib'), $version);
    wp_enqueue_script('acf-sortable-post-object');
    
    wp_register_style('acf-sortable-post-object', "{$url}assets/css/input.css", array('acf-input'), $version);
    wp_enqueue_style('acf-sortable-post-object');
  }
  
  function update_value( $value, $post_id, $field )
  {
    if( empty($value) ) {
      return $value;
    }
    return explode(',', $value);
  }
  
  function format_value( $value, $post_id, $field )
  {
    if( !empty($value) )
    {
      if( is_array($value) )
      {
        $value = array_map('intval', $value);
      }
      else
      {
        $value = intval($value);
      }
    }
    return $value;  
  }

  function format_value_for_api( $value, $post_id, $field )
  {
    if( !$value || $value == 'null' )
    {
      return false;
    }
    if( is_array($value) )
    {
      $posts = get_posts(array(
        'numberposts' => -1,
        'post__in' => $value,
        'post_type' =>  apply_filters('acf/get_post_types', array()),
        'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
      ));
      $ordered_posts = array();
      foreach( $posts as $post )
      {
        $ordered_posts[ $post->ID ] = $post;
      }
      foreach( $value as $k => $v)
      {
        if( !isset($ordered_posts[ $v ]) )
        {
          unset( $value[ $k ] );
        }
        else
        {
          $value[ $k ] = $ordered_posts[ $v ];
        }
      }
    }
    else
    {
      $value = get_post($value);
    }
    return $value;
  }

  function load_field( $field )
  {
    if( !$field['post_type'] || !is_array($field['post_type']) || in_array('', $field['post_type']) )
    {
      $field['post_type'] = array( 'all' );
    }
    if( !$field['taxonomy'] || !is_array($field['taxonomy']) || in_array('', $field['taxonomy']) )
    {
      $field['taxonomy'] = array( 'all' );
    }
    return $field;
  }
}

new dj_acf_field_sortable_post_object( $this->settings );

endif;

?>