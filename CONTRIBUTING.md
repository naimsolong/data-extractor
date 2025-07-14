# Contributing to Laravel Data Extractor

Thank you for considering contributing to Laravel Data Extractor! We welcome contributions from the community.

## Development Setup

1. **Fork the repository** and clone your fork locally
2. **Install dependencies**:
   ```bash
   composer install
   ```
3. **Copy the configuration**:
   ```bash
   cp phpstan.neon.dist phpstan.neon
   ```

## Development Workflow

### Code Quality

Before submitting any changes, ensure your code passes all quality checks:

```bash
# Format code
composer format

# Run static analysis
composer analyse

# Run tests
composer test

# Run tests with coverage
composer test-coverage
```

### Testing

- All new features must include tests
- Tests are written using **Pest PHP**
- Use Orchestra Testbench for Laravel package testing
- Ensure tests pass on all supported Laravel versions (10, 11, 12)

### Code Style

- Follow PSR-12 coding standards
- Use Laravel Pint for code formatting (`composer format`)
- Maintain consistency with existing codebase patterns

## Contribution Guidelines

### Pull Requests

1. **Create a feature branch** from `main`
2. **Make your changes** with clear, descriptive commits
3. **Add tests** for new functionality
4. **Update documentation** if needed
5. **Run quality checks** (format, analyse, test)
6. **Submit pull request** with clear description

### Commit Messages

Use clear and descriptive commit messages:
- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and pull requests when applicable

### Bug Reports

When reporting bugs, include:
- Laravel version
- PHP version
- Package version
- Steps to reproduce
- Expected vs actual behavior
- Code examples (if applicable)

### Feature Requests

For new features:
- Explain the use case
- Provide examples of how it would work
- Consider backward compatibility
- Discuss implementation approach

## Development Standards

### Security

- Never commit sensitive information
- Validate all inputs appropriately
- Follow Laravel security best practices
- Use proper SQL escaping for generated queries

### Architecture

- Follow existing patterns and conventions
- Use dependency injection where appropriate
- Maintain separation of concerns
- Keep classes focused and cohesive

### Documentation

- Update CLAUDE.md for architectural changes
- Add PHPDoc comments for public methods
- Include usage examples for new features
- Keep README.md current

## Getting Help

- Check existing issues and pull requests
- Review the codebase and tests for examples
- Ask questions in issues for clarification

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).