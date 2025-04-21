/**
 * Admin JavaScript for Calcifer
 */
(function ($) {
	'use strict';

	/**
	 * Handle formula inputs
	 */
	function setupFormulaInputs() {
		const inputsContainer = $('#calcifer-inputs-container');
		const template = $('#calcifer-input-template').html();

		// Add new input
		$('.calcifer-add-input').on('click', function (e) {
			e.preventDefault();

			// Count existing inputs
			const count = inputsContainer.find('.calcifer-input-item').length;
			const index = count;

			// Add new input
			let newInput = template
				.replace(/{index}/g, index)
				.replace(/{number}/g, index + 1);

			inputsContainer.append(newInput);
		});

		// Remove input
		$(document).on('click', '.calcifer-remove-input', function () {
			$(this).closest('.calcifer-input-item').remove();

			// Update indices
			updateInputIndices();
		});

		// Add at least one input if there are none
		if (inputsContainer.find('.calcifer-input-item').length === 0) {
			$('.calcifer-add-input').trigger('click');
		}
	}

	/**
	 * Update input indices after removing an input
	 */
	function updateInputIndices() {
		const inputs = $('.calcifer-input-item');

		inputs.each(function (index) {
			// Update index attribute
			$(this).attr('data-index', index);

			// Update heading
			$(this)
				.find('h4')
				.first()
				.text('Input #' + (index + 1) + ' ')
				.append(
					$(
						'<span class="calcifer-remove-input dashicons dashicons-no-alt"></span>',
					),
				);

			// Update input IDs and names
			$(this)
				.find('[id^="calcifer_inputs_"]')
				.each(function () {
					const oldId = $(this).attr('id');
					const newId = oldId.replace(
						/calcifer_inputs_\d+/,
						'calcifer_inputs_' + index,
					);
					$(this).attr('id', newId);
				});

			$(this)
				.find('[name^="calcifer_inputs["]')
				.each(function () {
					const oldName = $(this).attr('name');
					const newName = oldName.replace(
						/calcifer_inputs\[\d+\]/,
						'calcifer_inputs[' + index + ']',
					);
					$(this).attr('name', newName);
				});
		});
	}

	/**
	 * Handle formula preview
	 */
	function setupFormulaPreview() {
		$('.calcifer-preview-formula').on('click', function (e) {
			e.preventDefault();

			const formulaId = $(this).data('formula-id');
			const modal = $('#calcifer-formula-preview-modal');
			const modalContent = modal.find(
				'.calcifer-formula-preview-content',
			);

			// Show modal
			modal.show();

			// Load formula preview
			$.ajax({
				url: calciferAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'calcifer_preview_formula',
					formula_id: formulaId,
					_wpnonce: calciferAdmin.nonce,
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
		$('#calcifer-formula-preview-modal').on('click', function (e) {
			if ($(e.target).is(this)) {
				$(this).hide();
			}
		});

		// Add close button to modal
		const closeButton = $(
			'<span class="calcifer-formula-preview-close dashicons dashicons-no-alt"></span>',
		);
		$('.calcifer-formula-preview-container h2').append(closeButton);

		// Close modal when clicking close button
		$(document).on('click', '.calcifer-formula-preview-close', function () {
			$('#calcifer-formula-preview-modal').hide();
		});

		// Close modal when pressing ESC
		$(document).on('keydown', function (e) {
			if (e.keyCode === 27) {
				$('#calcifer-formula-preview-modal').hide();
			}
		});
	}

	// Initialize when document is ready
	$(document).ready(function () {
		setupFormulaInputs();
		setupFormulaPreview();
	});

	// Also try with window.onload to ensure all resources are loaded
	$(window).on('load', function () {
		setupFormulaInputs();
	});
})(jQuery);
