<?php
/**
 * Main admin page template
 */

// Get all formulas
$formula_handler = new Formula_Handler();
$formulas = $formula_handler->get_formulas();

// Check if user has dismissed the getting started notice
$user_id = get_current_user_id();
$is_dismissed = get_user_meta($user_id, 'calcifer_getting_started_dismissed', true);
?>
<div class="wrap calcifer-admin">
  <h1 class="wp-heading-inline">Calcifer</h1>
  <a href="<?php echo esc_url(admin_url('post-new.php?post_type=calcifer_formula')); ?>" class="page-title-action">Add
    New
    Formula</a>
  <hr class="wp-header-end">

  <div class="notice notice-info">
    <p>
      Welcome to Calcifer! This plugin allows you to create custom calculators with your own formulas and
      display them as Gutenberg blocks on your site.
    </p>
  </div>

  <?php if (!$is_dismissed): ?>
    <div class="card calcifer-getting-started-card" id="calcifer-getting-started">
      <div class="calcifer-card-header">
        <h2>Getting Started</h2>
        <button type="button" class="notice-dismiss calcifer-dismiss-getting-started" aria-label="Dismiss this notice">
          <span class="screen-reader-text">Dismiss</span>
        </button>
      </div>

      <p>
        To create a calculator, follow these simple steps:
      </p>
      <ol>
        <li>Create a new formula by clicking on "Add New Formula" above.</li>
        <li>Define your formula expression, inputs, and output settings.</li>
        <li>Add the "Calculator" block to any post or page using the Gutenberg editor.</li>
        <li>Select your formula from the block settings and customize as needed.</li>
      </ol>
      <p>
        <?php if (empty($formulas)): ?>
          <a href="<?php echo esc_url(admin_url('post-new.php?post_type=calcifer_formula')); ?>"
            class="button button-primary">Create Your First Formula</a>
        <?php else: ?>
          <a href="<?php echo esc_url(admin_url('post-new.php?post_type=calcifer_formula')); ?>"
            class="button button-primary">Create Another Formula</a>
        <?php endif; ?>
      </p>
    </div>
  <?php endif; ?>

  <?php if (!empty($formulas)): ?>
    <div class="card">
      <h2>Your Formulas</h2>
      <table class="wp-list-table widefat fixed striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Formula</th>
            <th>Inputs</th>
            <th>Output</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($formulas as $formula): ?>
            <tr>
              <td>
                <strong><?php echo esc_html($formula['title']); ?></strong>
                <?php if (!empty($formula['description'])): ?>
                  <div class="row-description"><?php echo wp_trim_words(esc_html($formula['description']), 10); ?></div>
                <?php endif; ?>
              </td>
              <td><?php echo esc_html($formula['formula']); ?></td>
              <td>
                <?php if (!empty($formula['inputs'])): ?>
                  <ul>
                    <?php foreach ($formula['inputs'] as $input): ?>
                      <li><strong><?php echo esc_html($input['name']); ?></strong>: <?php echo esc_html($input['label']); ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($formula['output'])): ?>
                  <strong><?php echo esc_html($formula['output']['label']); ?></strong>
                  <?php if (!empty($formula['output']['unit'])): ?>
                    (<?php echo esc_html($formula['output']['unit']); ?>)
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $formula['id'] . '&action=edit')); ?>"
                  class="button button-small">Edit</a>
                <a href="#" class="button button-small calcifer-preview-formula"
                  data-formula-id="<?php echo esc_attr($formula['id']); ?>">Preview</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="card">
      <h2>No Formulas Yet</h2>
      <p>You haven't created any formulas yet. Get started by clicking the "Add New Formula" button.</p>
      <p>
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=calcifer_formula')); ?>"
          class="button button-primary">Create Your First Formula</a>
      </p>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2>How to Use the Calculator Block</h2>
    <p>
      Once you've created a formula, you can add the Calculator block to any post or page:
    </p>
    <ol>
      <li>Edit the post or page where you want to display the calculator.</li>
      <li>Click the "+" button to add a new block.</li>
      <li>Search for "Calculator" and select the Calcifer block.</li>
      <li>In the block settings sidebar, select the formula you want to use.</li>
      <li>Customize the title, description, and theme as needed.</li>
      <li>Save your post or page.</li>
    </ol>
    <div class="calcifer-block-usage-images">
      <img src="<?php echo esc_url(CALCIFER_URL . 'admin/images/block-usage-1.png'); ?>"
        alt="How to use the Calculator block" style="max-width: 100%; height: auto; border: 1px solid #ddd;">
      <img src="<?php echo esc_url(CALCIFER_URL . 'admin/images/block-usage-2.png'); ?>"
        alt="How to use the Calculator block" style="max-width: 100%; height: auto; border: 1px solid #ddd;">
    </div>
  </div>

  <div class="card" id="calcifer-support">
    <h2>Support Calcifer</h2>
    <p>
      Calcifer is completely free to use, but if you find it valuable, consider supporting its development through our
      "pay what you want" model. Any contribution is appreciated and helps keep the project going.
    </p>
    <p>
      Your support helps with:
    </p>
    <ul>
      <li>✓ Ongoing development</li>
      <li>✓ Bug fixes</li>
      <li>✓ New features</li>
      <li>✓ Documentation updates</li>
      <li>✓ Plugin maintenance</li>
    </ul>
    <p>
      <a href="https://houseofgiants.gumroad.com/l/calcifer" class="button button-primary" target="_blank">Support
        Calcifer</a>
      <a href="https://github.com/house-of-giants/Calcifer/issues" class="button button-secondary" target="_blank">Get
        Help</a>
    </p>
  </div>
</div>

<div id="calcifer-formula-preview-modal" style="display: none;">
  <div class="calcifer-formula-preview-container">
    <h2>Formula Preview</h2>
    <div class="calcifer-formula-preview-content"></div>
  </div>
</div>

<style>
  .calcifer-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .calcifer-dismiss-getting-started {
    position: relative;
    top: 0;
    right: 0;
    border: none;
    margin: 0;
    padding: 9px;
    background: none;
    color: #787c82;
    cursor: pointer;
  }

  .calcifer-dismiss-getting-started:before {
    background: none;
    content: "\f153";
    display: block;
    font: normal 16px/20px dashicons;
    speak: never;
    height: 20px;
    text-align: center;
    width: 20px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  .calcifer-dismiss-getting-started:hover,
  .calcifer-dismiss-getting-started:active,
  .calcifer-dismiss-getting-started:focus {
    color: #d63638;
  }
</style>

<script type="text/javascript">
  jQuery(document).ready(function ($) {
    // Handle dismiss button click
    $('.calcifer-dismiss-getting-started').on('click', function () {
      // Hide the getting started box
      $('#calcifer-getting-started').slideUp();

      // Save dismissal to user preferences via AJAX
      $.post(ajaxurl, {
        action: 'calcifer_dismiss_getting_started',
        nonce: '<?php echo wp_create_nonce('calcifer_dismiss_getting_started'); ?>'
      });
    });
  });
</script>