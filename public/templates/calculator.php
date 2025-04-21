<?php
/**
 * Template for displaying the calculator on the frontend
 *
 * @var array $formula The formula data
 * @var string $title The calculator title
 * @var string $description The calculator description
 * @var string $theme The calculator theme
 */
?>
<div class="anything-calculator-container <?php echo esc_attr("theme-{$theme}"); ?>" data-formula-id="<?php echo esc_attr($formula['id']); ?>">
    <div class="anything-calculator-header">
        <h3 class="anything-calculator-title"><?php echo esc_html($title); ?></h3>
        <?php if (!empty($description)) : ?>
            <div class="anything-calculator-description"><?php echo wp_kses_post($description); ?></div>
        <?php endif; ?>
    </div>

    <div class="anything-calculator-body">
        <form class="anything-calculator-form" id="calculator-form-<?php echo esc_attr($formula['id']); ?>">
            <?php if (!empty($formula['inputs'])) : ?>
                <div class="anything-calculator-inputs">
                    <?php foreach ($formula['inputs'] as $input) : ?>
                        <div class="anything-calculator-input-group">
                            <label for="<?php echo esc_attr("input-{$formula['id']}-{$input['name']}"); ?>" class="anything-calculator-label">
                                <?php echo esc_html($input['label']); ?>
                                <?php if (!empty($input['required'])) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if (!empty($input['description'])) : ?>
                                <div class="anything-calculator-input-description"><?php echo esc_html($input['description']); ?></div>
                            <?php endif; ?>
                            
                            <input 
                                type="<?php echo esc_attr($input['type']); ?>"
                                id="<?php echo esc_attr("input-{$formula['id']}-{$input['name']}"); ?>"
                                name="<?php echo esc_attr($input['name']); ?>"
                                class="anything-calculator-input"
                                value="<?php echo esc_attr($input['default']); ?>"
                                <?php echo (!empty($input['required'])) ? 'required' : ''; ?>
                                step="any"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="anything-calculator-actions">
                <button type="submit" class="anything-calculator-button calculate-button">Calculate</button>
                <button type="reset" class="anything-calculator-button reset-button">Reset</button>
            </div>
        </form>

        <div class="anything-calculator-result" id="calculator-result-<?php echo esc_attr($formula['id']); ?>" style="display: none;">
            <div class="anything-calculator-result-inner">
                <div class="anything-calculator-result-label"><?php echo esc_html($formula['output']['label']); ?>:</div>
                <div class="anything-calculator-result-value">
                    <span class="result-number"></span>
                    <?php if (!empty($formula['output']['unit'])) : ?>
                        <span class="result-unit"><?php echo esc_html($formula['output']['unit']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="anything-calculator-error" id="calculator-error-<?php echo esc_attr($formula['id']); ?>" style="display: none;"></div>
    </div>

    <div class="anything-calculator-footer">
        <div class="anything-calculator-branding">
            <span>Powered by</span>
            <a href="https://houseofgiants.com" target="_blank" rel="noopener noreferrer">Anything Calculator</a>
        </div>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        const calculatorForm = $('#calculator-form-<?php echo esc_js($formula['id']); ?>');
        const calculatorResult = $('#calculator-result-<?php echo esc_js($formula['id']); ?>');
        const calculatorError = $('#calculator-error-<?php echo esc_js($formula['id']); ?>');
        const formulaId = <?php echo esc_js($formula['id']); ?>;
        
        calculatorForm.on('submit', function(e) {
            e.preventDefault();
            
            // Hide results and errors
            calculatorResult.hide();
            calculatorError.hide();
            
            // Get input values
            const inputValues = {};
            $(this).find('input').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                
                if (value) {
                    inputValues[name] = value;
                }
            });
            
            // Calculate formula with modern fetch API
            fetch(anythingCalculatorPublic.restUrl + 'anything-calculator/v1/calculate/' + formulaId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': anythingCalculatorPublic.nonce
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
                if (response.success) {
                    // Show result
                    calculatorResult.find('.result-number').text(response.formatted_result);
                    calculatorResult.slideDown();
                    
                    // Smooth scroll to result if needed
                    if (calculatorResult.offset().top + calculatorResult.height() > $(window).scrollTop() + $(window).height()) {
                        $('html, body').animate({
                            scrollTop: calculatorResult.offset().top - 100
                        }, 500);
                    }
                } else {
                    // Show error
                    calculatorError.text(response.message).slideDown();
                }
            })
            .catch(error => {
                calculatorError.text('An error occurred. Please try again.').slideDown();
                console.error('Calculation error:', error);
            });
        });
        
        // Reset button
        calculatorForm.find('.reset-button').on('click', function() {
            calculatorResult.hide();
            calculatorError.hide();
            
            // Reset form to default values
            calculatorForm.find('input').each(function() {
                const name = $(this).attr('name');
                const defaultValue = <?php echo json_encode(wp_list_pluck(wp_list_pluck($formula['inputs'], 'default', 'name'), 'default')); ?>[name] || '';
                $(this).val(defaultValue);
            });
        });
    });
})(jQuery);
</script> 