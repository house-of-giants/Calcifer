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
		const error = container.find('.calcifer-error');
		const loading = container.find('.calcifer-loading');
		
		// Initially show loading to indicate initialization
		loading.show();
		
		// Hide loading when page is fully loaded
		$(window).on('load', function() {
			loading.fadeOut(300);
			container.find('.calcifer-container-loader').fadeOut(300, function() {
				$(this).remove();
			});
		});
		
		// In case window.load event doesn't fire, hide after timeout
		setTimeout(function() {
			loading.fadeOut(300);
			container.find('.calcifer-container-loader').fadeOut(300, function() {
				$(this).remove();
			});
		}, 1500);
		
		// Submit form
		form.on('submit', function(e) {
			e.preventDefault();
			
			// Hide result and error, show loading
			result.hide();
			error.hide();
			loading.fadeIn(300);
			
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
			calculateFormula(formulaId, inputValues, result, error, loading);
		});
		
		// Reset form
		form.find('.reset-button').on('click', function() {
			// Hide result, error, and loading
			result.hide();
			error.hide();
			loading.hide();
			
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
	function calculateFormula(formulaId, inputValues, resultContainer, errorContainer, loadingContainer) {
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
			// Hide loading
			loadingContainer.fadeOut(300);
			
			if (response.success) {
				// Update result
				resultContainer.find('.result-number').text(response.formatted_result);
				
				// Animate number effect
				animateResult(resultContainer.find('.result-number'));
				
				// Show result
				resultContainer.fadeIn(300);
				
				// Smooth scroll to result if needed
				if (resultContainer.offset().top + resultContainer.height() > $(window).scrollTop() + $(window).height()) {
					$('html, body').animate({
						scrollTop: resultContainer.offset().top - 100
					}, 500);
				}
			} else {
				// Show error
				errorContainer.text(response.message).fadeIn(300);
			}
		})
		.catch(error => {
			// Hide loading
			loadingContainer.fadeOut(300);
			
			// Show error
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