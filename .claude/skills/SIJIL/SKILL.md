```markdown
# SIJIL Development Patterns

> Auto-generated skill from repository analysis

## Overview
This skill teaches the core development patterns and conventions used in the SIJIL JavaScript codebase. SIJIL is a JavaScript project with no detected framework, focusing on clear file organization, consistent code style, and a simple approach to testing. This guide covers naming conventions, import/export patterns, and common workflows to streamline contributions and maintenance.

## Coding Conventions

### File Naming
- Use **camelCase** for file names.
  - Example: `userProfile.js`, `dataFetcher.js`

### Import Style
- Use **relative imports** for modules within the project.
  - Example:
    ```javascript
    import { fetchData } from './dataFetcher';
    ```

### Export Style
- Use **named exports** for all exported functions, objects, or constants.
  - Example:
    ```javascript
    // In dataFetcher.js
    export function fetchData(url) { ... }
    ```

### Commit Messages
- Commit messages are **freeform** and may use various prefixes.
- Average commit message length is about 83 characters.
  - Example:
    ```
    Add user authentication logic and update error handling in login flow
    ```

## Workflows

### Adding a New Module
**Trigger:** When you need to add a new feature or utility module.
**Command:** `/add-module`

1. Create a new file using camelCase naming (e.g., `newFeature.js`).
2. Implement your logic using named exports.
3. Use relative imports to include your module where needed.
4. Write a corresponding test file (see Testing Patterns).

### Refactoring Code
**Trigger:** When improving code structure or readability.
**Command:** `/refactor`

1. Identify code to refactor.
2. Rename files to camelCase if necessary.
3. Update imports to use relative paths.
4. Ensure all exports are named.
5. Update or add tests as needed.

### Writing Tests
**Trigger:** When adding or updating functionality.
**Command:** `/write-test`

1. Create a test file with the pattern `*.test.*` (e.g., `dataFetcher.test.js`).
2. Write tests for each named export in the module.
3. Use the project's preferred (undetected) testing framework.

## Testing Patterns

- Test files follow the `*.test.*` naming convention.
  - Example: `userProfile.test.js`
- Each test file should cover all named exports from its corresponding module.
- The specific testing framework is not detected; follow existing test patterns in the repository.

## Commands
| Command        | Purpose                                             |
|----------------|-----------------------------------------------------|
| /add-module    | Scaffold a new module with proper conventions       |
| /refactor      | Refactor code to align with SIJIL patterns          |
| /write-test    | Create a test file for a module                     |
```
