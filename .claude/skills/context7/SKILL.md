---
name: context7
description: Use when you need current documentation for any software library, framework, or API. Queries the Context7 API for up-to-date docs instead of relying on potentially outdated training data.
allowed-tools: Bash(curl *)
argument-hint: "[library-name] [topic]"
---

# Context7

Retrieve current documentation for software libraries by querying the Context7 API via curl. Use instead of relying on potentially outdated training data.

## Workflow

### Step 1: Search for the Library

Find the Context7 library ID:

```bash
curl -s "https://context7.com/api/v2/libs/search?libraryName=LIBRARY_NAME&query=TOPIC" | jq '.results[0]'
```

**Parameters:**
- `libraryName` (required): Library name (e.g., "react", "nextjs", "fastapi", "symfony")
- `query` (required): Topic description for relevance ranking

**Response fields:**
- `id`: Library identifier for the context endpoint (e.g., `/websites/react_dev_reference`)
- `title`: Human-readable library name
- `description`: Brief description
- `totalSnippets`: Number of documentation snippets available

### Step 2: Fetch Documentation

Use the library ID from step 1:

```bash
curl -s "https://context7.com/api/v2/context?libraryId=LIBRARY_ID&query=TOPIC&type=txt"
```

**Parameters:**
- `libraryId` (required): Library ID from search results
- `query` (required): Specific topic to retrieve
- `type` (optional): `json` (default) or `txt` (plain text, more readable)

## Examples

### Symfony dependency injection

```bash
# Find Symfony library ID
curl -s "https://context7.com/api/v2/libs/search?libraryName=symfony&query=dependency+injection" | jq '.results[0].id'

# Fetch DI documentation
curl -s "https://context7.com/api/v2/context?libraryId=/symfony/symfony&query=dependency+injection&type=txt"
```

### PHPUnit assertions

```bash
# Find PHPUnit library ID
curl -s "https://context7.com/api/v2/libs/search?libraryName=phpunit&query=assertions" | jq '.results[0].id'

# Fetch assertions documentation
curl -s "https://context7.com/api/v2/context?libraryId=/sebastianbergmann/phpunit&query=assertions&type=txt"
```

### React hooks

```bash
# Find React library ID
curl -s "https://context7.com/api/v2/libs/search?libraryName=react&query=hooks" | jq '.results[0].id'

# Fetch useState documentation
curl -s "https://context7.com/api/v2/context?libraryId=/websites/react_dev_reference&query=useState&type=txt"
```

### Next.js routing

```bash
curl -s "https://context7.com/api/v2/libs/search?libraryName=nextjs&query=routing" | jq '.results[0].id'
curl -s "https://context7.com/api/v2/context?libraryId=/vercel/next.js&query=app+router&type=txt"
```

### FastAPI dependency injection

```bash
curl -s "https://context7.com/api/v2/libs/search?libraryName=fastapi&query=dependencies" | jq '.results[0].id'
curl -s "https://context7.com/api/v2/context?libraryId=/fastapi/fastapi&query=dependency+injection&type=txt"
```

## Tips

- Use `type=txt` for more readable output
- Use `jq` to filter and format JSON responses
- Be specific with `query` to improve relevance
- If first result is wrong, check additional results in the array
- URL-encode spaces in query parameters (use `+` or `%20`)
- No API key required for basic usage (rate-limited)
