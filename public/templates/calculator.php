<?php
/**
 * Template for displaying the calculator on the frontend
 *
 * @var array $formula The formula data
 * @var string $title The calculator title
 * @var string $description The calculator description
 * @var string $theme The calculator theme
 */

// Get settings to check if branding should be displayed
$settings = get_option('calcifer_settings', array());
$show_branding = isset($settings['show_branding']) ? (bool) $settings['show_branding'] : true;
?>
<div class="calcifer-container <?php echo esc_attr("theme-{$theme}"); ?>"
	data-formula-id="<?php echo esc_attr($formula['id']); ?>">
	<div class="calcifer-header">
		<h3 class="calcifer-title"><?php echo esc_html($title); ?></h3>
		<?php if (!empty($description)): ?>
			<div class="calcifer-description"><?php echo wp_kses_post($description); ?></div>
		<?php endif; ?>
	</div>

	<div class="calcifer-body">
		<form class="calcifer-form" id="calculator-form-<?php echo esc_attr($formula['id']); ?>">
			<?php if (!empty($formula['inputs'])): ?>
				<div class="calcifer-inputs">
					<?php foreach ($formula['inputs'] as $input): ?>
						<div class="calcifer-input-group">
							<label for="<?php echo esc_attr("input-{$formula['id']}-{$input['name']}"); ?>" class="calcifer-label">
								<?php echo esc_html($input['label']); ?>
								<?php if (!empty($input['required'])): ?>
									<span class="required">*</span>
								<?php endif; ?>
							</label>

							<?php if (!empty($input['description'])): ?>
								<div class="calcifer-input-description"><?php echo esc_html($input['description']); ?></div>
							<?php endif; ?>

							<input type="<?php echo esc_attr($input['type']); ?>"
								id="<?php echo esc_attr("input-{$formula['id']}-{$input['name']}"); ?>"
								name="<?php echo esc_attr($input['name']); ?>" class="calcifer-input"
								value="<?php echo esc_attr($input['default']); ?>" <?php echo (!empty($input['required'])) ? 'required' : ''; ?> step="any">
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="calcifer-actions">
				<button type="submit" class="calcifer-button calculate-button">Calculate</button>
				<button type="reset" class="calcifer-button reset-button">Reset</button>
			</div>
		</form>

		<div class="calcifer-result" id="calculator-result-<?php echo esc_attr($formula['id']); ?>">
			<div class="calcifer-result-inner">
				<div class="calcifer-result-label"><?php echo esc_html($formula['output']['label']); ?>:</div>
				<div class="calcifer-result-value">
					<span class="result-number"></span>
					<?php if (!empty($formula['output']['unit'])): ?>
						<span class="result-unit"><?php echo esc_html($formula['output']['unit']); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="calcifer-error" id="calculator-error-<?php echo esc_attr($formula['id']); ?>" style="display: none;">
		</div>
	</div>

	<?php if ($show_branding): ?>
		<div class="calcifer-footer">
			<div class="calcifer-branding">
				<span>Powered by</span>
				<a href="https://houseofgiants.com" target="_blank" rel="noopener noreferrer">House of Giants</a>
			</div>
		</div>
	<?php endif; ?>
</div>