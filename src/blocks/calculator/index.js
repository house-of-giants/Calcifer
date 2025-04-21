/**
 * Anything Calculator Block
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
			if (
				window.anythingCalculatorData &&
				window.anythingCalculatorData.formulas
			) {
				setFormulas(window.anythingCalculatorData.formulas);
				setIsLoading(false);
			} else {
				// Fetch formulas from API if not available in localized data
				fetch(
					`${window.anythingCalculatorData?.restUrl || '/wp-json/'}anything-calculator/v1/formulas`,
					{
						headers: {
							'X-WP-Nonce': window.anythingCalculatorData?.nonce || '',
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
			{ label: __('-- Select Formula --', 'anything-calculator'), value: 0 },
			...(Array.isArray(formulas)
				? formulas.map((formula) => ({
						label: formula.title,
						value: formula.id,
					}))
				: []),
		];

		// Theme options
		const themeOptions = [
			{ label: __('Light', 'anything-calculator'), value: 'light' },
			{ label: __('Dark', 'anything-calculator'), value: 'dark' },
		];

		return (
			<>
				<InspectorControls>
					<PanelBody
						title={__('Calculator Settings', 'anything-calculator')}
						initialOpen={true}
					>
						<SelectControl
							label={__('Formula', 'anything-calculator')}
							value={formulaId}
							options={formulaOptions}
							onChange={(value) =>
								setAttributes({ formulaId: parseInt(value, 10) })
							}
							help={__(
								'Select the formula to use for calculation.',
								'anything-calculator',
							)}
						/>

						<TextControl
							label={__('Title', 'anything-calculator')}
							value={title}
							onChange={(value) => setAttributes({ title: value })}
							help={__(
								'Title displayed above the calculator.',
								'anything-calculator',
							)}
						/>

						<TextareaControl
							label={__('Description', 'anything-calculator')}
							value={description}
							onChange={(value) => setAttributes({ description: value })}
							help={__(
								'Description displayed below the title.',
								'anything-calculator',
							)}
						/>

						<RadioControl
							label={__('Theme', 'anything-calculator')}
							selected={theme}
							options={themeOptions}
							onChange={(value) => setAttributes({ theme: value })}
							help={__(
								'Choose the visual style for the calculator.',
								'anything-calculator',
							)}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					{isLoading ? (
						<Placeholder
							icon="calculator"
							label={__('Calculator', 'anything-calculator')}
						>
							<Spinner />
							{__('Loading formulas...', 'anything-calculator')}
						</Placeholder>
					) : formulaId > 0 ? (
						<div className={`anything-calculator-preview theme-${theme}`}>
							<Card>
								<CardHeader>
									<h3>{title || __('Calculator', 'anything-calculator')}</h3>
									{description && <p>{description}</p>}
								</CardHeader>
								<CardBody>
									{Array.isArray(formulas) && (
										<div className="anything-calculator-editor-preview">
											<p className="preview-note">
												{__(
													'Calculator Preview - The actual calculator will appear here on the frontend.',
													'anything-calculator',
												)}
											</p>
											<hr />
											{formulas.map((formula) => {
												if (formula.id === formulaId) {
													return (
														<div key={formula.id} className="selected-formula">
															<h4>
																{__('Selected Formula:', 'anything-calculator')}{' '}
																{formula.title}
															</h4>
															<p>
																{__('Inputs:', 'anything-calculator')}{' '}
																{formula.inputs?.length || 0}
															</p>
															<p>
																{__('Output:', 'anything-calculator')}{' '}
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
							label={__('Calculator', 'anything-calculator')}
							instructions={__(
								'Select a formula in the block settings sidebar.',
								'anything-calculator',
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
