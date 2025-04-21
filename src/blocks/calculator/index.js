/**
 * Calcifer Block
 */

import './style.scss';
import './editor.scss';
import metadata from './block.json';

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	RadioControl,
	Placeholder,
	Spinner,
	Card,
	CardBody,
	CardHeader,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

// Register the block using metadata from block.json
registerBlockType(metadata.name, {
	...metadata,

	// Edit component
	edit: ({ attributes, setAttributes }) => {
		const blockProps = useBlockProps();
		const { formulaId, title, description, theme } = attributes;

		// Load formulas from the localized data
		const [formulas, setFormulas] = useState([]);
		const [isLoading, setIsLoading] = useState(true);

		useEffect(() => {
			if (window.calciferData && window.calciferData.formulas) {
				setFormulas(window.calciferData.formulas);
				setIsLoading(false);
			} else {
				// Fetch formulas from API if not available in localized data
				fetch(
					`${window.calciferData?.restUrl || '/wp-json/'}calcifer/v1/formulas`,
					{
						headers: {
							'X-WP-Nonce': window.calciferData?.nonce || '',
							'Content-Type': 'application/json',
						},
						credentials: 'same-origin',
					},
				)
					.then((response) => {
						if (!response.ok) {
							console.error(
								'REST API Error:',
								response.status,
								response.statusText,
							);
							return response.json().then((err) => {
								console.error('Error details:', err);
								throw err;
							});
						}
						return response.json();
					})
					.then((data) => {
						setFormulas(data);
						setIsLoading(false);
					})
					.catch((error) => {
						console.error('Error fetching formulas:', error);
						setIsLoading(false);
					});
			}
		}, []);

		// Prepare formula options for select control
		const formulaOptions = [
			{ label: __('-- Select Formula --', 'calcifer'), value: 0 },
			...(Array.isArray(formulas)
				? formulas.map((formula) => ({
						label: formula.title,
						value: formula.id,
					}))
				: []),
		];

		// Theme options
		const themeOptions = [
			{ label: __('Light', 'calcifer'), value: 'light' },
			{ label: __('Dark', 'calcifer'), value: 'dark' },
		];

		return (
			<>
				<InspectorControls>
					<PanelBody
						title={__('Calculator Settings', 'calcifer')}
						initialOpen={true}
					>
						<SelectControl
							label={__('Formula', 'calcifer')}
							value={formulaId}
							options={formulaOptions}
							onChange={(value) =>
								setAttributes({ formulaId: parseInt(value, 10) })
							}
							help={__(
								'Select the formula to use for calculation.',
								'calcifer',
							)}
						/>

						<TextControl
							label={__('Title', 'calcifer')}
							value={title}
							onChange={(value) => setAttributes({ title: value })}
							help={__('Title displayed above the calculator.', 'calcifer')}
						/>

						<TextareaControl
							label={__('Description', 'calcifer')}
							value={description}
							onChange={(value) => setAttributes({ description: value })}
							help={__('Description displayed below the title.', 'calcifer')}
						/>

						<RadioControl
							label={__('Theme', 'calcifer')}
							selected={theme}
							options={themeOptions}
							onChange={(value) => setAttributes({ theme: value })}
							help={__(
								'Choose the visual style for the calculator.',
								'calcifer',
							)}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					{isLoading ? (
						<Placeholder icon="calculator" label={__('Calculator', 'calcifer')}>
							<Spinner />
							{__('Loading formulas...', 'calcifer')}
						</Placeholder>
					) : formulaId > 0 ? (
						<div className={`calcifer-preview theme-${theme}`}>
							<Card>
								<CardHeader>
									<h3>{title || __('Calculator', 'calcifer')}</h3>
									{description && <p>{description}</p>}
								</CardHeader>
								<CardBody>
									{Array.isArray(formulas) && (
										<div className="calcifer-editor-preview">
											<p className="preview-note">
												{__(
													'Calculator Preview - The actual calculator will appear here on the frontend.',
													'calcifer',
												)}
											</p>
											<hr />
											{formulas.map((formula) => {
												if (formula.id === formulaId) {
													return (
														<div key={formula.id} className="selected-formula">
															<h4>
																{__('Selected Formula:', 'calcifer')}{' '}
																{formula.title}
															</h4>
															<p>
																{__('Inputs:', 'calcifer')}{' '}
																{formula.inputs?.length || 0}
															</p>
															<p>
																{__('Output:', 'calcifer')}{' '}
																{formula.output?.label}
															</p>
														</div>
													);
												}
												return null;
											})}
										</div>
									)}
								</CardBody>
							</Card>
						</div>
					) : (
						<Placeholder
							icon="calculator"
							label={__('Calculator', 'calcifer')}
							instructions={__(
								'Select a formula in the block settings sidebar.',
								'calcifer',
							)}
						>
							<SelectControl
								value={formulaId}
								options={formulaOptions}
								onChange={(value) =>
									setAttributes({ formulaId: parseInt(value, 10) })
								}
							/>
						</Placeholder>
					)}
				</div>
			</>
		);
	},

	// Save component (using dynamic rendering)
	save: () => {
		return null;
	},
});
