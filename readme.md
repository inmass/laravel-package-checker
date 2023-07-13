[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/inmass/laravel-package-checker/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/iinmass/laravel-package-checker.svg)](https://packagist.org/packages/iinmass/laravel-package-checker)


## Features

Retrieves a list of packages required in your project's composer.json file.
Displays the current version, latest version, installation status, and size for each package.
Offers a treemap visualization of the vendor directory, showing the size distribution of packages.
Helps optimize project dependencies by highlighting large packages.
Easy installation and seamless integration with your existing project.

## Description

Package Checker is a powerful tool that provides insights into the packages used in your project. It helps you manage your project's dependencies, keeping you informed about the current and latest versions of each package, their installation status, and their sizes. The package also offers a treemap visualization that displays the size distribution of packages within your vendor directory, allowing you to identify large packages that might require optimization.

## Installation

The Laravel Package Checker can be easily installed via Composer. Run the following command in your terminal:

```bash
composer require iinmass/laravel-package-checker
```

After the installation is complete, the package will be ready to use in your Laravel project.

## Usage

To access the Package Checker, navigate to the following URL in your web browser: `http://your-app-url/package-checker/list`.
This page provides a comprehensive overview of your Laravel project's packages, including the required packages from composer.json, their versions, installation status, and sizes as well as the tree of all the packages and their dependencies.

## Configuration

The Laravel Package Checker requires no additional configuration. It seamlessly integrates with your Laravel project and retrieves the necessary information from the composer.json file and vendor directory.

## Contributing

Contributions to the Laravel Package Checker are welcome! If you would like to contribute to the project, please follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or fix.
3. Make the necessary modifications.
4. Commit your changes.
5. Push the branch to your fork.
6. Submit a pull request.
Please ensure that your code adheres to the project's coding standards and includes appropriate tests.

## License

The Laravel Package Checker is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Credits

The Laravel Package Checker is developed and maintained by [inmass](https://github.com/inmass)

## Support

If you encounter any issues or have any questions, please create a new issue on the [issue tracker](https://github.com/inmass/laravel-package-checker/issues). I will be happy to assist you.