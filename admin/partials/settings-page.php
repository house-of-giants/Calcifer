<?php
/**
 * Settings page template
 */

// Get theme color palette from WordPress
$theme_colors = array();
if (function_exists('wp_get_global_settings')) {
  $settings = wp_get_global_settings();
  if (!empty($settings['color']['palette'])) {
    $theme_colors = $settings['color']['palette'];
  }
}

// Flatten the theme colors if they're nested in a 'default' or other palette groups
$flattened_colors = array();
if (!empty($theme_colors)) {
  // Check if it's a nested structure with array keys like 'default', 'theme', etc.
  if (isset($theme_colors['default']) || isset($theme_colors['theme']) || isset($theme_colors['custom'])) {
    // It's a nested structure, so flatten it
    foreach ($theme_colors as $palette_group => $colors) {
      if (is_array($colors)) {
        foreach ($colors as $color) {
          if (isset($color['slug']) && isset($color['color']) && isset($color['name'])) {
            $flattened_colors[] = $color;
          }
        }
      }
    }
  } else {
    // It's already a flat structure
    foreach ($theme_colors as $color) {
      if (isset($color['slug']) && isset($color['color']) && isset($color['name'])) {
        $flattened_colors[] = $color;
      }
    }
  }
}

$theme_colors = $flattened_colors;

// Get current color mappings from Calcifer settings
$calcifer_settings = get_option('calcifer_settings', array());
$color_mappings = isset($calcifer_settings['color_mappings']) ? $calcifer_settings['color_mappings'] : array(
  'primary' => '',
  'secondary' => '',
  'text' => '',
  'background' => '',
);

?>
<div class="wrap calcifer-settings">
  <h1>Calcifer Settings</h1>

  <form method="post" action="options.php">
    <?php settings_fields('calcifer_settings'); ?>
    <?php do_settings_sections('calcifer_settings'); ?>

    <div class="card">
      <h2>Theme Integration</h2>
      <p>Calcifer automatically detects colors from your WordPress theme. You can map specific theme colors to
        Calcifer's color variables below.</p>

      <?php if (!empty($theme_colors)): ?>
        <h3>Available Theme Colors</h3>
        <div class="calcifer-theme-colors-preview">
          <?php foreach ($theme_colors as $color): ?>
            <?php if (isset($color['slug']) && isset($color['color']) && isset($color['name'])): ?>
              <div class="calcifer-color-sample"
                title="<?php echo esc_attr($color['name']); ?>: <?php echo esc_attr($color['color']); ?>">
                <div class="color-swatch" style="background-color: <?php echo esc_attr($color['color']); ?>"></div>
                <div class="color-info">
                  <div class="color-name"><?php echo esc_html($color['name']); ?></div>
                  <div class="color-slug">--wp--preset--color--<?php echo esc_html($color['slug']); ?></div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <table class="form-table">
          <tr>
            <th scope="row">
              <label for="calcifer_primary_color_map">Primary Color</label>
            </th>
            <td>
              <select id="calcifer_primary_color_map" name="calcifer_settings[color_mappings][primary]">
                <option value="">Default Calcifer Blue</option>
                <?php foreach ($theme_colors as $color): ?>
                  <?php if (isset($color['slug']) && isset($color['name'])): ?>
                    <option value="<?php echo esc_attr($color['slug']); ?>" <?php selected($color_mappings['primary'], $color['slug']); ?>>
                      <?php echo esc_html($color['name']); ?>
                      (--wp--preset--color--<?php echo esc_html($color['slug']); ?>)
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
                <option value="custom" <?php selected($color_mappings['primary'], 'custom'); ?>>Custom Variable...
                </option>
              </select>
              <div class="custom-var-input" id="custom_primary_container"
                style="<?php echo ($color_mappings['primary'] === 'custom') ? 'display:block;' : 'display:none;'; ?> margin-top: 5px;">
                <input type="text" id="calcifer_primary_color_custom"
                  name="calcifer_settings[color_mappings][primary_custom]"
                  value="<?php echo esc_attr($calcifer_settings['color_mappings']['primary_custom'] ?? ''); ?>"
                  placeholder="--wp--preset--color--brand-primary">
                <p class="description">Enter a custom CSS variable name (include the -- prefix)</p>
              </div>
              <p class="description">Used for buttons, links, and highlights.</p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="calcifer_secondary_color_map">Secondary Color</label>
            </th>
            <td>
              <select id="calcifer_secondary_color_map" name="calcifer_settings[color_mappings][secondary]">
                <option value="">Default Calcifer Dark Blue</option>
                <?php foreach ($theme_colors as $color): ?>
                  <?php if (isset($color['slug']) && isset($color['name'])): ?>
                    <option value="<?php echo esc_attr($color['slug']); ?>" <?php selected($color_mappings['secondary'], $color['slug']); ?>>
                      <?php echo esc_html($color['name']); ?>
                      (--wp--preset--color--<?php echo esc_html($color['slug']); ?>)
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
                <option value="custom" <?php selected($color_mappings['secondary'], 'custom'); ?>>Custom Variable...
                </option>
              </select>
              <div class="custom-var-input" id="custom_secondary_container"
                style="<?php echo ($color_mappings['secondary'] === 'custom') ? 'display:block;' : 'display:none;'; ?> margin-top: 5px;">
                <input type="text" id="calcifer_secondary_color_custom"
                  name="calcifer_settings[color_mappings][secondary_custom]"
                  value="<?php echo esc_attr($calcifer_settings['color_mappings']['secondary_custom'] ?? ''); ?>"
                  placeholder="--wp--preset--color--slate">
                <p class="description">Enter a custom CSS variable name (include the -- prefix)</p>
              </div>
              <p class="description">Used for dark theme backgrounds and accents.</p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="calcifer_text_color_map">Text Color</label>
            </th>
            <td>
              <select id="calcifer_text_color_map" name="calcifer_settings[color_mappings][text]">
                <option value="">Default Calcifer Text</option>
                <?php foreach ($theme_colors as $color): ?>
                  <?php if (isset($color['slug']) && isset($color['name'])): ?>
                    <option value="<?php echo esc_attr($color['slug']); ?>" <?php selected($color_mappings['text'], $color['slug']); ?>>
                      <?php echo esc_html($color['name']); ?>
                      (--wp--preset--color--<?php echo esc_html($color['slug']); ?>)
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
                <option value="custom" <?php selected($color_mappings['text'], 'custom'); ?>>Custom Variable...</option>
              </select>
              <div class="custom-var-input" id="custom_text_container"
                style="<?php echo ($color_mappings['text'] === 'custom') ? 'display:block;' : 'display:none;'; ?> margin-top: 5px;">
                <input type="text" id="calcifer_text_color_custom" name="calcifer_settings[color_mappings][text_custom]"
                  value="<?php echo esc_attr($calcifer_settings['color_mappings']['text_custom'] ?? ''); ?>"
                  placeholder="--wp--preset--color--foreground">
                <p class="description">Enter a custom CSS variable name (include the -- prefix)</p>
              </div>
              <p class="description">Used for text elements in light mode.</p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="calcifer_background_color_map">Background Color</label>
            </th>
            <td>
              <select id="calcifer_background_color_map" name="calcifer_settings[color_mappings][background]">
                <option value="">Default Calcifer Background</option>
                <?php foreach ($theme_colors as $color): ?>
                  <?php if (isset($color['slug']) && isset($color['name'])): ?>
                    <option value="<?php echo esc_attr($color['slug']); ?>" <?php selected($color_mappings['background'], $color['slug']); ?>>
                      <?php echo esc_html($color['name']); ?>
                      (--wp--preset--color--<?php echo esc_html($color['slug']); ?>)
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
                <option value="custom" <?php selected($color_mappings['background'], 'custom'); ?>>Custom Variable...
                </option>
              </select>
              <div class="custom-var-input" id="custom_background_container"
                style="<?php echo ($color_mappings['background'] === 'custom') ? 'display:block;' : 'display:none;'; ?> margin-top: 5px;">
                <input type="text" id="calcifer_background_color_custom"
                  name="calcifer_settings[color_mappings][background_custom]"
                  value="<?php echo esc_attr($calcifer_settings['color_mappings']['background_custom'] ?? ''); ?>"
                  placeholder="--wp--preset--color--background">
                <p class="description">Enter a custom CSS variable name (include the -- prefix)</p>
              </div>
              <p class="description">Used for calculator backgrounds in light mode.</p>
            </td>
          </tr>
        </table>
      <?php else: ?>
        <div class="notice notice-warning inline">
          <p>Your current theme doesn't appear to define WordPress preset colors, or you're using an older WordPress
            version. You can still use custom CSS variables below:</p>
        </div>

        <table class="form-table">
          <tr>
            <th scope="row">
              <label for="calcifer_primary_color_custom">Primary Color Variable</label>
            </th>
            <td>
              <input type="text" id="calcifer_primary_color_custom"
                name="calcifer_settings[color_mappings][primary_custom]"
                value="<?php echo esc_attr($calcifer_settings['color_mappings']['primary_custom'] ?? ''); ?>"
                placeholder="--wp--preset--color--brand-primary" class="regular-text">
              <p class="description">Custom CSS variable for primary color (include the -- prefix)</p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="calcifer_secondary_color_custom">Secondary Color Variable</label>
            </th>
            <td>
              <input type="text" id="calcifer_secondary_color_custom"
                name="calcifer_settings[color_mappings][secondary_custom]"
                value="<?php echo esc_attr($calcifer_settings['color_mappings']['secondary_custom'] ?? ''); ?>"
                placeholder="--wp--preset--color--slate" class="regular-text">
              <p class="description">Custom CSS variable for secondary color (include the -- prefix)</p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="calcifer_text_color_custom">Text Color Variable</label>
            </th>
            <td>
              <input type="text" id="calcifer_text_color_custom" name="calcifer_settings[color_mappings][text_custom]"
                value="<?php echo esc_attr($calcifer_settings['color_mappings']['text_custom'] ?? ''); ?>"
                placeholder="--wp--preset--color--foreground" class="regular-text">
              <p class="description">Custom CSS variable for text color (include the -- prefix)</p>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="calcifer_background_color_custom">Background Color Variable</label>
            </th>
            <td>
              <input type="text" id="calcifer_background_color_custom"
                name="calcifer_settings[color_mappings][background_custom]"
                value="<?php echo esc_attr($calcifer_settings['color_mappings']['background_custom'] ?? ''); ?>"
                placeholder="--wp--preset--color--background" class="regular-text">
              <p class="description">Custom CSS variable for background color (include the -- prefix)</p>
            </td>
          </tr>
        </table>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2>Appearance Settings</h2>

      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="calcifer_primary_color">Primary Color</label>
          </th>
          <td>
            <input type="color" id="calcifer_primary_color" name="calcifer_settings[primary_color]"
              value="<?php echo esc_attr(get_option('calcifer_settings')['primary_color'] ?? '#0073aa'); ?>">
            <p class="description">The primary color used for buttons and accents if theme integration is disabled.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="calcifer_button_style">Button Style</label>
          </th>
          <td>
            <select id="calcifer_button_style" name="calcifer_settings[button_style]">
              <option value="rounded" <?php selected((get_option('calcifer_settings')['button_style'] ?? 'rounded'), 'rounded'); ?>>Rounded</option>
              <option value="square" <?php selected((get_option('calcifer_settings')['button_style'] ?? 'rounded'), 'square'); ?>>Square</option>
              <option value="pill" <?php selected((get_option('calcifer_settings')['button_style'] ?? 'rounded'), 'pill'); ?>>Pill</option>
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
            <label for="calcifer_show_branding">Show Branding</label>
          </th>
          <td>
            <!-- Hidden field ensures the value is sent even when unchecked -->
            <input type="hidden" name="calcifer_settings[show_branding]" value="0">
            <input type="checkbox" id="calcifer_show_branding" name="calcifer_settings[show_branding]" value="1" <?php checked((get_option('calcifer_settings')['show_branding'] ?? true), true); ?>>
            <span class="description">Show "Powered by House of Giants" in the footer of calculators.</span>
          </td>
        </tr>
      </table>
    </div>

    <div class="card">
      <h2>Advanced Settings</h2>

      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="calcifer_allow_custom_css">Allow Custom CSS</label>
          </th>
          <td>
            <input type="checkbox" id="calcifer_allow_custom_css" name="calcifer_settings[allow_custom_css]" value="1"
              <?php checked((get_option('calcifer_settings')['allow_custom_css'] ?? false), true); ?>>
            <span class="description">Allow users to add custom CSS to calculator blocks.</span>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="calcifer_custom_css">Global Custom CSS</label>
          </th>
          <td>
            <textarea id="calcifer_custom_css" name="calcifer_settings[custom_css]" rows="10"
              class="large-text code"><?php echo esc_textarea(get_option('calcifer_settings')['custom_css'] ?? ''); ?></textarea>
            <p class="description">Custom CSS to apply to all calculators.</p>
          </td>
        </tr>
      </table>
    </div>

    <?php submit_button('Save Settings'); ?>
  </form>
</div>

<style>
  .calcifer-theme-colors-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
  }

  .calcifer-color-sample {
    display: flex;
    align-items: center;
    width: 250px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
  }

  .color-swatch {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
  }

  .color-info {
    padding: 8px;
    font-size: 12px;
  }

  .color-name {
    font-weight: bold;
  }

  .color-slug {
    color: #666;
    font-family: monospace;
  }
</style>

<script type="text/javascript">
  jQuery(document).ready(function ($) {
    // Show/hide custom variable inputs based on select value
    $('#calcifer_primary_color_map, #calcifer_secondary_color_map, #calcifer_text_color_map, #calcifer_background_color_map').on('change', function () {
      var id = $(this).attr('id');
      var containerId = '';

      if (id === 'calcifer_primary_color_map') {
        containerId = 'custom_primary_container';
      } else if (id === 'calcifer_secondary_color_map') {
        containerId = 'custom_secondary_container';
      } else if (id === 'calcifer_text_color_map') {
        containerId = 'custom_text_container';
      } else if (id === 'calcifer_background_color_map') {
        containerId = 'custom_background_container';
      }

      if ($(this).val() === 'custom') {
        $('#' + containerId).show();
      } else {
        $('#' + containerId).hide();
      }
    });
  });
</script>