/**
 * Public JavaScript for Calcifer
 */
(function($) {
	'use strict';
	
	// Initialize all calculators on the page
	function initCalculators() {
		$('.calcifer-container').each(function() {
			initCalculator($(this));
		});
	}
	
	// Initialize single calculator
	function initCalculator(container) {
		const formulaId = container.data('formula-id');
		const form = container.find('.calcifer-form');
		const result = container.find('.calcifer-result');
		const resultNumber = result.find('.result-number');
		const error = container.find('.calcifer-error');
		
		// Set initial placeholder state
		setPlaceholderState(resultNumber);
		
		// Remove any loading indicators
		container.find('.calcifer-loading, .calcifer-container-loader').remove();
		
		// Submit form
		form.on('submit', function(e) {
			e.preventDefault();
			
			// Hide error, set calculating state
			error.hide();
			setCalculatingState(resultNumber);
			
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
			calculateFormula(formulaId, inputValues, resultNumber, error);
		});
		
		// Reset form
		form.find('.reset-button').on('click', function() {
			// Hide error and reset result to placeholder
			error.hide();
			setPlaceholderState(resultNumber);
			
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
	
	// Set placeholder state
	function setPlaceholderState(resultElement) {
		resultElement.text('—');
		resultElement.addClass('result-placeholder');
	}
	
	// Set calculating state
	function setCalculatingState(resultElement) {
		resultElement.text('Calculating...');
		resultElement.addClass('result-calculating');
	}
	
	// Calculate formula
	function calculateFormula(formulaId, inputValues, resultElement, errorContainer) {
		// Modern fetch API request
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
			if (response.success) {
				// Update result and remove placeholder/calculating classes
				resultElement.removeClass('result-placeholder result-calculating');
				resultElement.text(response.formatted_result);
				
				// Animate number effect
				animateResult(resultElement);
			} else {
				// Show error, set placeholder state
				setPlaceholderState(resultElement);
				errorContainer.text(response.message).fadeIn(300);
			}
		})
		.catch(error => {
			// Show error, set placeholder state
			setPlaceholderState(resultElement);
			errorContainer.text('An error occurred. Please try again.').fadeIn(300);
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