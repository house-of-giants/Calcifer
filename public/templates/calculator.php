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

		<!-- Loading Indicator for calculations -->
		<div class="calcifer-loading" id="calculator-loading-<?php echo esc_attr($formula['id']); ?>"
			style="display: none;">
			<div class="calcifer-spinner"></div>
			<div class="calcifer-loading-text">Calculating...</div>
		</div>

		<div class="calcifer-result" id="calculator-result-<?php echo esc_attr($formula['id']); ?>" style="display: none;">
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

	<!-- Initial loading overlay will be inserted here by JavaScript -->
</div>

<script>
	(function ($) {
		$(document).ready(function () {
			const calculatorForm = $('#calculator-form-<?php echo esc_js($formula['id']); ?>');
			const calculatorResult = $('#calculator-result-<?php echo esc_js($formula['id']); ?>');
			const calculatorError = $('#calculator-error-<?php echo esc_js($formula['id']); ?>');
			const calculatorLoading = $('#calculator-loading-<?php echo esc_js($formula['id']); ?>');
			const calculatorContainer = $('.calcifer-container[data-formula-id="<?php echo esc_js($formula['id']); ?>"]');
			const formulaId = <?php echo esc_js($formula['id']); ?>;

			// Ensure container has relative positioning for overlay positioning
			calculatorContainer.css('position', 'relative');

			// Add initial loading state to show calculator is initializing
			calculatorLoading.show();

			// Hide loading state after a short delay or when page is fully loaded
			$(window).on('load', function () {
				calculatorLoading.fadeOut(300);
				calculatorContainer.find('.calcifer-container-loader').fadeOut(300, function () {
					$(this).remove();
				});
			});

			// If window.load takes too long, hide loading after 1 second anyway
			setTimeout(function () {
				calculatorLoading.fadeOut(300);
				calculatorContainer.find('.calcifer-container-loader').fadeOut(300, function () {
					$(this).remove();
				});
			}, 1500);

			calculatorForm.on('submit', function (e) {
				e.preventDefault();

				// Hide results and errors, show loading
				calculatorResult.hide();
				calculatorError.hide();
				calculatorLoading.fadeIn(300);

				// Get input values
				const inputValues = {};
				$(this).find('input').each(function () {
					const name = $(this).attr('name');
					const value = $(this).val();

					if (value) {
						inputValues[name] = value;
					}
				});

				// Calculate formula with modern fetch API
				fetch(calciferPublic.restUrl + 'calcifer/v1/calculate/' + formulaId, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': calciferPublic.nonce
					},
					credentials: 'same-origin',
					body: JSON.stringify(inputValues)
				})
					.then(response => {
						if (!response.ok) {
							return response.json().then(err => { throw err; });
						}
						return response.json();
					})
					.then(response => {
						// Hide loading indicator
						calculatorLoading.fadeOut(300);

						if (response.success) {
							// Show result
							calculatorResult.find('.result-number').text(response.formatted_result);
							calculatorResult.fadeIn(300);

							// Smooth scroll to result if needed
							if (calculatorResult.offset().top + calculatorResult.height() > $(window).scrollTop() + $(window).height()) {
								$('html, body').animate({
									scrollTop: calculatorResult.offset().top - 100
								}, 500);
							}
						} else {
							// Show error
							calculatorError.text(response.message).fadeIn(300);
						}
					})
					.catch(error => {
						// Hide loading indicator
						calculatorLoading.fadeOut(300);

						calculatorError.text('An error occurred. Please try again.').fadeIn(300);
						console.error('Calculation error:', error);
					});
			});

			// Reset button
			calculatorForm.find('.reset-button').on('click', function () {
				calculatorResult.hide();
				calculatorError.hide();
				calculatorLoading.hide();

				// Reset form to default values
				calculatorForm.find('input').each(function () {
					const name = $(this).attr('name');
					const defaultValue = <?php echo json_encode(wp_list_pluck(wp_list_pluck($formula['inputs'], 'default', 'name'), 'default')); ?>[name] || '';
					$(this).val(defaultValue);
				});
			});
		});
	})(jQuery);
</script>