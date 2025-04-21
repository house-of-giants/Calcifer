/**
 * Admin JavaScript for Anything Calculator
 */
(function ($) {
	'use strict';

	/**
	 * Handle formula inputs
	 */
	function setupFormulaInputs() {
		const inputsContainer = $('#ac-inputs-container');
		const template = $('#ac-input-template').html();

		console.log('Setting up formula inputs');
		console.log('Inputs container found:', inputsContainer.length > 0);
		console.log('Template found:', template !== undefined);

		// Add new input
		$('.ac-add-input').on('click', function (e) {
			e.preventDefault();
			console.log('Add input button clicked');

			// Count existing inputs
			const count = inputsContainer.find('.ac-input-item').length;
			const index = count;
			console.log('Current input count:', count);

			// Add new input
			let newInput = template
				.replace(/{index}/g, index)
				.replace(/{number}/g, index + 1);

			inputsContainer.append(newInput);
			console.log('New input added');
		});

		// Log if add input button exists
		console.log('Add input button found:', $('.ac-add-input').length > 0);

		// Remove input
		$(document).on('click', '.ac-remove-input', function () {
			console.log('Remove input button clicked');
			$(this).closest('.ac-input-item').remove();

			// Update indices
			updateInputIndices();
		});

		// Add at least one input if there are none
		if (inputsContainer.find('.ac-input-item').length === 0) {
			console.log('No inputs found, adding one automatically');
			$('.ac-add-input').trigger('click');
		}
	}

	/**
	 * Update input indices after removing an input
	 */
	function updateInputIndices() {
		const inputs = $('.ac-input-item');

		inputs.each(function (index) {
			// Update index attribute
			$(this).attr('data-index', index);

			// Update heading
			$(this)
				.find('h4')
				.first()
				.text('Input #' + (index + 1) + ' ')
				.append(
					$('<span class="ac-remove-input dashicons dashicons-no-alt"></span>'),
				);

			// Update input IDs and names
			$(this)
				.find('[id^="ac_inputs_"]')
				.each(function () {
					const oldId = $(this).attr('id');
					const newId = oldId.replace(/ac_inputs_\d+/, 'ac_inputs_' + index);
					$(this).attr('id', newId);
				});

			$(this)
				.find('[name^="ac_inputs["]')
				.each(function () {
					const oldName = $(this).attr('name');
					const newName = oldName.replace(
						/ac_inputs\[\d+\]/,
						'ac_inputs[' + index + ']',
					);
					$(this).attr('name', newName);
				});
		});
	}

	/**
	 * Handle formula preview
	 */
	function setupFormulaPreview() {
		$('.ac-preview-formula').on('click', function (e) {
			e.preventDefault();

			const formulaId = $(this).data('formula-id');
			const modal = $('#ac-formula-preview-modal');
			const modalContent = modal.find('.ac-formula-preview-content');

			// Show modal
			modal.show();

			// Load formula preview
			$.ajax({
				url: anythingCalculatorAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ac_preview_formula',
					formula_id: formulaId,
					_wpnonce: anythingCalculatorAdmin.nonce,
				},
				success: function (response) {
					if (response.success) {
						modalContent.html(response.data);
					} else {
						modalContent.html(
							'<div class="notice notice-error"><p>' +
								response.data +
								'</p></div>',
						);
					}
				},
				error: function () {
					modalContent.html(
						'<div class="notice notice-error"><p>An error occurred while loading the preview.</p></div>',
					);
				},
			});
		});

		// Close modal when clicking outside content
		$('#ac-formula-preview-modal').on('click', function (e) {
			if ($(e.target).is(this)) {
				$(this).hide();
			}
		});

		// Add close button to modal
		const closeButton = $(
			'<span class="ac-formula-preview-close dashicons dashicons-no-alt"></span>',
		);
		$('.ac-formula-preview-container h2').append(closeButton);

		// Close modal when clicking close button
		$(document).on('click', '.ac-formula-preview-close', function () {
			$('#ac-formula-preview-modal').hide();
		});

		// Close modal when pressing ESC
		$(document).on('keydown', function (e) {
			if (e.keyCode === 27) {
				$('#ac-formula-preview-modal').hide();
			}
		});
	}

	// Initialize when document is ready
	$(document).ready(function () {
		console.log('Document ready');
		setupFormulaInputs();
		setupFormulaPreview();
	});

	// Also try with window.onload to ensure all resources are loaded
	$(window).on('load', function () {
		console.log('Window loaded');
		setupFormulaInputs();
	});
})(jQuery);
