# Anything Calculator

A flexible WordPress calculator plugin that allows users to create custom formulas and display them as Gutenberg blocks.

## Description

Anything Calculator is a versatile tool that enables website owners to create customized calculators with their own formulas. Whether you're calculating BMI, percentages, tip amounts, or any other mathematical formula, this plugin makes it easy to implement and display interactive calculators on your WordPress site.

### Features

- Create unlimited custom calculators
- Define your own mathematical formulas
- Create multiple input fields with validation
- Customize the appearance with light and dark themes
- Add calculators anywhere using Gutenberg blocks
- Responsive design for all devices
- Clean, modern interface

## Installation

1. Upload the `anything-calculator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Anything Calculator' in the admin menu
4. Create your first formula
5. Add the Calculator block to any post or page

## Usage

### Creating a Formula

1. Go to Anything Calculator > Add New Formula
2. Enter a title and description for your formula
3. Define the formula expression using variable names
4. Add input fields and set their properties
5. Configure the output settings
6. Save your formula

### Adding a Calculator to a Page

1. Edit the page where you want to display the calculator
2. Add a new block and search for "Calculator"
3. Select the Anything Calculator block
4. Choose your formula from the block settings
5. Customize the title, description, and theme
6. Save the page

## Default Calculators

The plugin comes with three pre-configured calculators:

### BMI Calculator

```
BMI = Weight / (Height * Height)
```

Where:

- Weight = Weight in kilograms
- Height = Height in meters

### Percentage Calculator

```
Result = Value * (Percentage / 100)
```

Where:

- Value = The base value
- Percentage = Percentage value

### Tip Calculator

```
Tip Amount = BillAmount * (TipPercentage / 100)
```

Where:

- BillAmount = Total bill amount
- TipPercentage = Percentage you want to tip

## Building from Source

For developers who want to modify the plugin:

1. Clone the repository
2. Run `npm install` to install dependencies
3. Run `npm run start` for development
4. Run `npm run build` for production build

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by House of Giants.

## Support

For support or feature requests, please visit [House of Giants](https://houseofgiants.com).
