<?php

// Create custom plugin settings menu
add_action('admin_menu', function() {
	// Create new top-level menu
	add_menu_page('Default Thumbnail Settings', 'Default thumbnail', 'administrator', __FILE__, 'default_thumbnail_settings_page' , 'dashicons-chart-pie' );

	// Call register settings function
	add_action( 'admin_init', function() {
  	//register our settings
  	register_setting( 'default-thumbnail-settings-group', 'default_thumbnail_options', array(
      'default' => array(
        'empty' => 1,
        'service' => 'https://placeimg.com/%d/%d/any',
        'min_height' => 230,
        'max_height' => 800
      )
    ));
  } );
});


function default_thumbnail_settings_page() {
	$options = get_option('default_thumbnail_options');
?>
<div class="wrap">
  <h1>Default thumbnail</h1>

  <form method="post" action="options.php">
      <?php settings_fields( 'default-thumbnail-settings-group' ); ?>
      <?php do_settings_sections( 'default-thumbnail-settings-group' ); ?>
      <div class="form-table">

        <fieldset>
          <legend><h3>Parameters</h3><legend>
          <table>
            <tr>
              <th>
                <label>
                  <?= __('Image service'); ?>
                </label>
              </th>
              <td>
                <input type="text" name="default_thumbnail_options[service]" value="<?= $options['service']; ?>" placeholder="http://example/%s/%s" style="width: 320px"/>
              </td>
            </tr>
            <tr>
              <th>
                <label>
                  <?= __('Min height'); ?>
                </label>
              </th>
              <td>
                <input type="number" name="default_thumbnail_options[min_height]" value="<?= $options['min_height']; ?>" style="width: 120px"/>
              </td>
            </tr>
            <tr>
              <th>
                <label>
                  <?= __('Max height'); ?>
                </label>
              </th>
              <td>
                <input type="number" name="default_thumbnail_options[max_height]" value="<?= $options['max_height']; ?>" style="width: 120px"/>
              </td>
            </tr>
          </table>
        </fieldset>

        <fieldset>
          <legend><h3>Featured image</h3><legend>
          <div>
            <label>
              <input type="checkbox" name="default_thumbnail_options[empty]" value="1" <?php checked( $options['empty'], 1 ); ?>/>
              <?= __('Empty thumbnails'); ?>
            </label>
          </div>
          <div>
            <label>
              <input type="checkbox" name="default_thumbnail_options[broken]" value="1" <?php checked( $options['broken'], 1 ); ?>/>
              <?= __('Broken thumbnails'); ?>
            </label>
          </div>
          <div>
            <label>
              <input type="checkbox" name="default_thumbnail_options[regular]" value="1" <?php checked( $options['regular'], 1 ); ?>/>
              <?= __('Regular thumbnails'); ?>
            </label>
          </div>
      </fieldset>

      <?php submit_button(); ?>
    </div>
  </form>
</div>
<?php } ?>
