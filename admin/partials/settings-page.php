<?php
/**
 * Settings page template
 */
?>
<div class="wrap anything-calculator-settings">
  <h1>Anything Calculator Settings</h1>

  <form method="post" action="options.php">
    <?php settings_fields('anything_calculator_settings'); ?>
    <?php do_settings_sections('anything_calculator_settings'); ?>

    <div class="card">
      <h2>Appearance Settings</h2>

      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="anything_calculator_primary_color">Primary Color</label>
          </th>
          <td>
            <input type="color" id="anything_calculator_primary_color"
              name="anything_calculator_settings[primary_color]"
              value="<?php echo esc_attr(get_option('anything_calculator_settings')['primary_color'] ?? '#0073aa'); ?>">
            <p class="description">The primary color used for buttons and accents.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="anything_calculator_button_style">Button Style</label>
          </th>
          <td>
            <select id="anything_calculator_button_style" name="anything_calculator_settings[button_style]">
              <option value="rounded" <?php selected((get_option('anything_calculator_settings')['button_style'] ?? 'rounded'), 'rounded'); ?>>Rounded</option>
              <option value="square" <?php selected((get_option('anything_calculator_settings')['button_style'] ?? 'rounded'), 'square'); ?>>Square</option>
              <option value="pill" <?php selected((get_option('anything_calculator_settings')['button_style'] ?? 'rounded'), 'pill'); ?>>Pill</option>
            </select>
            <p class="description">The style of buttons in the calculator.</p>
          </td>
        </tr>
      </table>
    </div>

    <div class="card">
      <h2>Branding Settings</h2>

      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="anything_calculator_show_branding">Show Branding</label>
          </th>
          <td>
            <input type="checkbox" id="anything_calculator_show_branding"
              name="anything_calculator_settings[show_branding]" value="1" <?php checked((get_option('anything_calculator_settings')['show_branding'] ?? true), true); ?>>
            <span class="description">Show "Powered by Anything Calculator" in the footer of calculators.</span>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="anything_calculator_branding_text">Branding Text</label>
          </th>
          <td>
            <input type="text" id="anything_calculator_branding_text" name="anything_calculator_settings[branding_text]"
              value="<?php echo esc_attr(get_option('anything_calculator_settings')['branding_text'] ?? 'Anything Calculator'); ?>"
              class="regular-text">
            <p class="description">Custom text to use in the "Powered by" message.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="anything_calculator_branding_url">Branding URL</label>
          </th>
          <td>
            <input type="url" id="anything_calculator_branding_url" name="anything_calculator_settings[branding_url]"
              value="<?php echo esc_url(get_option('anything_calculator_settings')['branding_url'] ?? 'https://houseofgiants.com'); ?>"
              class="regular-text">
            <p class="description">URL to link to from the "Powered by" message.</p>
          </td>
        </tr>
      </table>
    </div>

    <div class="card">
      <h2>Advanced Settings</h2>

      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="anything_calculator_allow_custom_css">Allow Custom CSS</label>
          </th>
          <td>
            <input type="checkbox" id="anything_calculator_allow_custom_css"
              name="anything_calculator_settings[allow_custom_css]" value="1" <?php checked((get_option('anything_calculator_settings')['allow_custom_css'] ?? false), true); ?>>
            <span class="description">Allow users to add custom CSS to calculator blocks.</span>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="anything_calculator_custom_css">Global Custom CSS</label>
          </th>
          <td>
            <textarea id="anything_calculator_custom_css" name="anything_calculator_settings[custom_css]" rows="10"
              class="large-text code"><?php echo esc_textarea(get_option('anything_calculator_settings')['custom_css'] ?? ''); ?></textarea>
            <p class="description">Custom CSS to apply to all calculators.</p>
          </td>
        </tr>
      </table>
    </div>

    <?php submit_button('Save Settings'); ?>
  </form>
</div>