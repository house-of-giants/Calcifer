/**
 * Public JavaScript for Anything Calculator
 */
(function($) {
  'use strict';
  
  // Initialize all calculators on the page
  function initCalculators() {
    $('.anything-calculator-container').each(function() {
      initCalculator($(this));
    });
  }
  
  // Initialize single calculator
  function initCalculator(container) {
    const formulaId = container.data('formula-id');
    const form = container.find('.anything-calculator-form');
    const result = container.find('.anything-calculator-result');
    const error = container.find('.anything-calculator-error');
    
    // Submit form
    form.on('submit', function(e) {
      e.preventDefault();
      
      // Hide result and error
      result.hide();
      error.hide();
      
      // Collect input values
      const inputValues = {};
      form.find('input').each(function() {
        const name = $(this).attr('name');
        const value = $(this).val();
        
        if (value) {
          inputValues[name] = value;
        }
      });
      
      // Calculate
      calculateFormula(formulaId, inputValues, result, error);
    });
    
    // Reset form
    form.find('.reset-button').on('click', function() {
      // Hide result and error
      result.hide();
      error.hide();
      
      // Reset inputs to default values
      form.find('input').each(function() {
        const defaultValue = $(this).data('default');
        if (defaultValue) {
          $(this).val(defaultValue);
        }
      });
    });
    
    // Set input defaults
    form.find('input').each(function() {
      $(this).data('default', $(this).val());
    });
  }
  
  // Calculate formula
  function calculateFormula(formulaId, inputValues, resultContainer, errorContainer) {
    // Show loading state
    resultContainer.find('.result-number').html('<span class="calculating">Calculating...</span>');
    resultContainer.slideDown();
    
    // Modern fetch API request
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
        // Update result
        resultContainer.find('.result-number').text(response.formatted_result);
        
        // Animate number effect
        animateResult(resultContainer.find('.result-number'));
        
        // Show result
        resultContainer.slideDown();
      } else {
        // Show error
        errorContainer.text(response.message).slideDown();
        resultContainer.hide();
      }
    })
    .catch(error => {
      // Show error
      errorContainer.text('An error occurred. Please try again.').slideDown();
      resultContainer.hide();
      console.error('Calculation error:', error);
    });
  }
  
  // Animate result with a subtle highlighting effect
  function animateResult(element) {
    element.css({
      backgroundColor: 'rgba(52, 152, 219, 0.2)'
    }).animate({
      backgroundColor: 'transparent'
    }, 1000);
  }
  
  // Initialize when document is ready
  $(document).ready(function() {
    initCalculators();
  });
  
})(jQuery); 