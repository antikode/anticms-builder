# Preparing AntiCmsBuilder for Packagist

## Steps to Publish on Packagist

### 1. Create a GitHub Repository

1. Create a new repository on GitHub: `antikode/anti-cms-builder`
2. Copy all files from `packages/antikode/AntiCmsBuilder/` to the new repository
3. Commit and push all files

### 2. Update Dependencies (if needed)

The package currently has some hard dependencies on App models. You may want to:

1. Make these dependencies optional by checking if classes exist
2. Use configuration to allow users to specify their own models
3. Create interfaces for better abstraction

### 3. Tag a Release

```bash
git tag -a v1.0.0 -m "Initial release"
git push origin v1.0.0
```

### 4. Submit to Packagist

1. Go to [packagist.org](https://packagist.org)
2. Sign in with your GitHub account
3. Click "Submit" and enter your repository URL: `https://github.com/antikode/anti-cms-builder`
4. Packagist will automatically read your `composer.json` and create the package

### 5. Set up Auto-Update Hook

1. In your GitHub repository settings, go to "Webhooks"
2. Add a new webhook with the URL provided by Packagist
3. This will automatically update Packagist when you push new releases

## Installation for Users

Once published, users can install with:

```bash
composer require antikode/anti-cms-builder
```

## Current Package Structure

```
AntiCmsBuilder/
├── src/                          # PHP source code
├── resources/js/                 # React components
├── tests/                        # Test files
├── config/anti-cms-builder.php   # Configuration file
├── composer.json                 # Package definition
├── README.md                     # Documentation
├── LICENSE                       # MIT License
├── CHANGELOG.md                  # Version history
└── .gitignore                    # Git ignore rules
```

## Notes

- The package is configured for Laravel auto-discovery
- React components can be published to user's resources directory
- Configuration file can be published for customization
- All dependencies are properly defined in composer.json
- PSR-4 autoloading is configured correctly