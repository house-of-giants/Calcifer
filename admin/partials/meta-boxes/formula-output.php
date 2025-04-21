<?php
/**
 * Formula output meta box template
 * 
 * @var array $output The formula output settings
 */
?>
<div class="ac-formula-output">
  <p>
    <label for="ac_output_label">Result Label:</label>
    <input type="text" id="ac_output_label" name="ac_output_label" value="<?php echo esc_attr($output['label']); ?>"
      class="regular-text" required>
    <span class="description">The label for the result (e.g., "Weight per Foot")</span>
  </p>

  <p>
    <label for="ac_output_unit">Unit (optional):</label>
    <input type="text" id="ac_output_unit" name="ac_output_unit" value="<?php echo esc_attr($output['unit']); ?>"
      class="regular-text">
    <span class="description">The unit of measurement for the result (e.g., "lbs/ft")</span>
  </p>

  <p>
    <label for="ac_output_precision">Decimal Precision:</label>
    <input type="number" id="ac_output_precision" name="ac_output_precision"
      value="<?php echo esc_attr($output['precision']); ?>" min="0" max="10">
    <span class="description">Number of decimal places to display</span>
  </p>
</div>