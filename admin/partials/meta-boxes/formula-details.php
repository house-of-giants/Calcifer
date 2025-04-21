<?php
/**
 * Formula details meta box template
 *
 * @var string $formula The formula expression
 */
?>
<div class="calcifer-formula-details">
	<p>
	<label for="calcifer_formula">Formula Expression:</label>
	<input type="text" id="calcifer_formula" name="calcifer_formula" value="<?php echo esc_attr( $formula ); ?>"
		class="large-text" required placeholder="Example: Weight / (Height * Height)">
	<span class="formula-validation-message"></span>
	</p>

	<div class="calcifer-formula-help">
	<p class="description">
		Enter the mathematical formula using variable names that will be defined as inputs below.
		<a href="#" class="formula-help-toggle">Show Formula Help</a>
	</p>

	<div class="formula-help-content"
		style="display: none; background: #f9f9f9; padding: 10px; border-left: 4px solid #0073aa; margin-top: 10px;">
		<h4>Formula Syntax Guide</h4>
		<p><strong>Variable Names:</strong> Use simple names without spaces (e.g., <code>Weight</code>,
		<code>Height</code>, <code>Price</code>)
		</p>
		<p><strong>Supported Operations:</strong></p>
		<ul style="list-style-type: disc; margin-left: 20px;">
		<li><code>+</code> Addition (e.g., <code>Value1 + Value2</code>)</li>
		<li><code>-</code> Subtraction (e.g., <code>Total - Discount</code>)</li>
		<li><code>*</code> Multiplication (e.g., <code>Price * Quantity</code>)</li>
		<li><code>/</code> Division (e.g., <code>Total / People</code>)</li>
		<li><code>( )</code> Grouping (e.g., <code>(Value1 + Value2) * Multiplier</code>)</li>
		</ul>

		<h4>Example Formulas</h4>
		<ul style="list-style-type: disc; margin-left: 20px;">
		<li><strong>BMI Calculator:</strong> <code>Weight / (Height * Height)</code></li>
		<li><strong>Percentage Calculator:</strong> <code>Value * (Percentage / 100)</code></li>
		<li><strong>Tip Calculator:</strong> <code>BillAmount * (TipPercentage / 100)</code></li>
		<li><strong>Area of Rectangle:</strong> <code>Length * Width</code></li>
		</ul>

		<p><strong>Important Notes:</strong></p>
		<ul style="list-style-type: disc; margin-left: 20px;">
		<li>Variable names are case-insensitive (<code>weight</code> and <code>Weight</code> are treated the same)</li>
		<li>Each variable name used must be defined in the Inputs section below</li>
		<li>Keep formulas as simple as possible for best reliability</li>
		</ul>
	</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
	// Toggle formula help
	$('.formula-help-toggle').on('click', function (e) {
		e.preventDefault();
		$('.formula-help-content').slideToggle();
		$(this).text($(this).text() === 'Show Formula Help' ? 'Hide Formula Help' : 'Show Formula Help');
	});

	// Basic formula validation
	$('#calcifer_formula').on('input', function () {
		const formula = $(this).val();
		const validationMsg = $('.formula-validation-message');

		// Perform basic validation
		if (formula.trim() === '') {
		validationMsg.text('Formula cannot be empty').css('color', 'red');
		} else if (!/^[a-zA-Z0-9\s\+\-\*\/\(\)\.]+$/.test(formula)) {
		validationMsg.text('Formula contains invalid characters').css('color', 'red');
		} else if (
		(formula.match(/\(/g) || []).length !== (formula.match(/\)/g) || []).length
		) {
		validationMsg.text('Mismatched parentheses').css('color', 'red');
		} else if (/[\+\-\*\/]{2,}/.test(formula)) {
		validationMsg.text('Invalid operator sequence').css('color', 'red');
		} else {
		validationMsg.text('Formula syntax looks good!').css('color', 'green');
		}
	});
	});
</script>