<?php
/**
 * Formula inputs meta box template
 *
 * @var array $inputs The formula inputs
 */
?>
<div class="calcifer-formula-inputs">
	<div class="input-help-tip">
	<p class="description">
		Define the variables used in your formula. Each variable in your formula needs a corresponding input field.
		<a href="#" class="input-help-toggle">Input Field Help</a>
	</p>

	<div class="input-help-content"
		style="display: none; background: #f9f9f9; padding: 10px; border-left: 4px solid #0073aa; margin: 10px 0;">
		<h4>Input Field Guide</h4>
		<p><strong>Variable Name:</strong> The exact name you used in your formula (like "Weight"). Must be a simple name
		without spaces or special characters.</p>
		<p><strong>Display Label:</strong> What users will see as the field label (like "Weight in kg").</p>
		<p><strong>Input Type:</strong> Choose how users will input values:</p>
		<ul style="list-style-type: disc; margin-left: 20px;">
		<li><strong>Number:</strong> Standard number input with optional step controls</li>
		<li><strong>Range:</strong> Slider for choosing a number within a range</li>
		<li><strong>Dropdown:</strong> Select from predefined options</li>
		</ul>
		<p><strong>Default Value:</strong> Pre-populated value when calculator loads</p>
		<p><strong>Description:</strong> Explanatory text shown below the input field</p>
		<p><strong>Required:</strong> Whether users must fill in this field before calculating</p>
	</div>
	</div>

	<div class="calcifer-inputs-container" id="calcifer-inputs-container">
	<?php if ( ! empty( $inputs ) ) : ?>
		<?php foreach ( $inputs as $index => $input ) : ?>
			<div class="calcifer-input-item" data-index="<?php echo esc_attr( $index ); ?>">
				<h4>Input #<?php echo esc_html( $index + 1 ); ?> <span
					class="calcifer-remove-input dashicons dashicons-no-alt"></span>
				</h4>

				<p>
				<label for="calcifer_inputs_<?php echo esc_attr( $index ); ?>_name">Variable Name:</label>
				<input type="text" id="calcifer_inputs_<?php echo esc_attr( $index ); ?>_name"
					name="calcifer_inputs[<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $input['name'] ); ?>"
					class="variable-name-input" required pattern="[a-zA-Z][a-zA-Z0-9]*">
				<span class="description">Must match a variable in your formula (e.g., "Weight")</span>
				<span class="validation-message"></span>
				</p>

				<p>
				<label for="calcifer_inputs_<?php echo esc_attr( $index ); ?>_label">Display Label:</label>
				<input type="text" id="calcifer_inputs_<?php echo esc_attr( $index ); ?>_label"
					name="calcifer_inputs[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $input['label'] ); ?>"
					required>
				<span class="description">Label shown to users (e.g., "Weight in kg")</span>
				</p>

				<p>
				<label for="calcifer_inputs_<?php echo esc_attr( $index ); ?>_type">Input Type:</label>
				<select id="calcifer_inputs_<?php echo esc_attr( $index ); ?>_type"
					name="calcifer_inputs[<?php echo esc_attr( $index ); ?>][type]">
					<option value="number" <?php selected( $input['type'], 'number' ); ?>>Number</option>
					<option value="range" <?php selected( $input['type'], 'range' ); ?>>Range (Slider)</option>
					<option value="select" <?php selected( $input['type'], 'select' ); ?>>Dropdown</option>
				</select>
				</p>

				<p>
				<label for="calcifer_inputs_<?php echo esc_attr( $index ); ?>_default">Default Value:</label>
				<input type="text" id="calcifer_inputs_<?php echo esc_attr( $index ); ?>_default"
					name="calcifer_inputs[<?php echo esc_attr( $index ); ?>][default]" value="<?php echo esc_attr( $input['default'] ); ?>">
				<span class="description">Initial value when calculator loads</span>
				</p>

				<p>
				<label for="calcifer_inputs_<?php echo esc_attr( $index ); ?>_description">Description:</label>
				<textarea id="calcifer_inputs_<?php echo esc_attr( $index ); ?>_description"
					name="calcifer_inputs[<?php echo esc_attr( $index ); ?>][description]" rows="2"
					class="large-text"><?php echo esc_textarea( $input['description'] ); ?></textarea>
				<span class="description">Help text shown to users (e.g., "Enter your weight in kilograms")</span>
				</p>

				<p>
				<label for="calcifer_inputs_<?php echo esc_attr( $index ); ?>_required">
					<input type="checkbox" id="calcifer_inputs_<?php echo esc_attr( $index ); ?>_required"
					name="calcifer_inputs[<?php echo esc_attr( $index ); ?>][required]" value="1" <?php checked( ! empty( $input['required'] ) ); ?>>
					Required
				</label>
				</p>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	</div>

	<p class="calcifer-actions">
	<button type="button" class="button button-secondary calcifer-add-input">Add Input</button>
	<span class="description" style="margin-left: 10px;">Add all variables used in your formula</span>
	</p>
</div>

<script type="text/template" id="calcifer-input-template">
	<div class="calcifer-input-item" data-index="{index}">
		<h4>Input #{number} <span class="calcifer-remove-input dashicons dashicons-no-alt"></span></h4>
		
		<p>
			<label for="calcifer_inputs_{index}_name">Variable Name:</label>
			<input type="text" id="calcifer_inputs_{index}_name" name="calcifer_inputs[{index}][name]" value="" 
				class="variable-name-input" required pattern="[a-zA-Z][a-zA-Z0-9]*">
			<span class="description">Must match a variable in your formula (e.g., "Weight")</span>
			<span class="validation-message"></span>
		</p>
		
		<p>
			<label for="calcifer_inputs_{index}_label">Display Label:</label>
			<input type="text" id="calcifer_inputs_{index}_label" name="calcifer_inputs[{index}][label]" value="" required>
			<span class="description">Label shown to users (e.g., "Weight in kg")</span>
		</p>
		
		<p>
			<label for="calcifer_inputs_{index}_type">Input Type:</label>
			<select id="calcifer_inputs_{index}_type" name="calcifer_inputs[{index}][type]">
				<option value="number" selected>Number</option>
				<option value="range">Range (Slider)</option>
				<option value="select">Dropdown</option>
			</select>
		</p>
		
		<p>
			<label for="calcifer_inputs_{index}_default">Default Value:</label>
			<input type="text" id="calcifer_inputs_{index}_default" name="calcifer_inputs[{index}][default]" value="">
			<span class="description">Initial value when calculator loads</span>
		</p>
		
		<p>
			<label for="calcifer_inputs_{index}_description">Description:</label>
			<textarea id="calcifer_inputs_{index}_description" name="calcifer_inputs[{index}][description]" rows="2" class="large-text"></textarea>
			<span class="description">Help text shown to users (e.g., "Enter your weight in kilograms")</span>
		</p>
		
		<p>
			<label for="calcifer_inputs_{index}_required">
				<input type="checkbox" id="calcifer_inputs_{index}_required" name="calcifer_inputs[{index}][required]" value="1" checked>
				Required
			</label>
		</p>
	</div>
</script>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
	// Toggle input help
	$('.input-help-toggle').on('click', function (e) {
		e.preventDefault();
		$('.input-help-content').slideToggle();
		$(this).text($(this).text() === 'Input Field Help' ? 'Hide Help' : 'Input Field Help');
	});

	// Validate variable names
	$(document).on('input', '.variable-name-input', function () {
		const input = $(this);
		const value = input.val().trim();
		const validationMsg = input.siblings('.validation-message');

		if (value === '') {
		validationMsg.text('Variable name is required').css('color', 'red');
		} else if (!/^[a-zA-Z][a-zA-Z0-9]*$/.test(value)) {
		validationMsg.text('Variable name must start with a letter and contain only letters and numbers').css('color', 'red');
		} else {
		validationMsg.text('✓').css('color', 'green');
		}
	});

	// Check for duplicate variable names
	function checkDuplicateVariables() {
		const names = {};
		let hasDuplicates = false;

		$('.variable-name-input').each(function () {
		const value = $(this).val().trim().toLowerCase();
		if (value && names[value]) {
			$(this).siblings('.validation-message').text('Duplicate variable name!').css('color', 'red');
			hasDuplicates = true;
		} else if (value) {
			names[value] = true;
		}
		});

		return !hasDuplicates;
	}

	// Add event listener to check for duplicates
	$(document).on('change', '.variable-name-input', checkDuplicateVariables);

	// Add validation check before form submission
	$('form#post').on('submit', function (e) {
		if (!checkDuplicateVariables()) {
		e.preventDefault();
		alert('Please fix duplicate variable names before saving.');
		return false;
		}
		return true;
	});
	});
</script>