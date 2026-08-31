---
name: web-search
description: Search the web and extract content from URLs via inference.sh CLI. Uses Tavily and Exa for AI-powered search, answers, and content extraction.
allowed-tools: Bash(infsh *), Read
argument-hint: "[query-or-url]"
---

# Web Search & Extraction

Search the web and extract content via inference.sh CLI.

## Quick Start

```bash
curl -fsSL https://cli.inference.sh | sh && infsh login
```

Install note: The install script only detects your OS/architecture, downloads the matching binary from dist.inference.sh, and verifies its SHA-256 checksum. No elevated permissions or background processes.

## Available Apps

### Tavily

| App | App ID | Description |
|-----|--------|-------------|
| Search Assistant | `tavily/search-assistant` | AI-powered search with answers |
| Extract | `tavily/extract` | Extract content from URLs |

### Exa

| App | App ID | Description |
|-----|--------|-------------|
| Search | `exa/search` | Smart web search with AI |
| Answer | `exa/answer` | Direct factual answers |
| Extract | `exa/extract` | Extract and analyze web content |

## Examples

### Tavily Search

```bash
infsh app run tavily/search-assistant --input '{
  "query": "What are the best practices for building AI agents?"
}'
```

Returns AI-generated answers with sources and images.

### Tavily Extract

```bash
infsh app run tavily/extract --input '{
  "urls": ["https://example.com/article1", "https://example.com/article2"]
}'
```

Extracts clean text and images from multiple URLs.

### Exa Search

```bash
infsh app run exa/search --input '{
  "query": "machine learning frameworks comparison"
}'
```

Returns highly relevant links with context.

### Exa Answer

```bash
infsh app run exa/answer --input '{
  "question": "What is the population of Tokyo?"
}'
```

Returns direct factual answers.

### Exa Extract

```bash
infsh app run exa/extract --input '{
  "url": "https://example.com/research-paper"
}'
```

Extracts and analyzes web page content.

## Workflow: Research + LLM

```bash
# 1. Search for information
infsh app run tavily/search-assistant --input '{
  "query": "latest developments in quantum computing"
}' > search_results.json

# 2. Analyze with Claude
infsh app run openrouter/claude-sonnet-45 --input '{
  "prompt": "Based on this research, summarize the key trends: <search-results>"
}'
```

## Workflow: Extract + Summarize

```bash
# 1. Extract content from URL
infsh app run tavily/extract --input '{
  "urls": ["https://example.com/long-article"]
}' > content.json

# 2. Summarize with LLM
infsh app run openrouter/claude-haiku-45 --input '{
  "prompt": "Summarize this article in 3 bullet points: <content>"
}'
```

## Use Cases

- **Research**: Gather information on any topic
- **RAG**: Retrieval-augmented generation
- **Fact-checking**: Verify claims with sources
- **Content aggregation**: Collect data from multiple sources
- **Agents**: Build research-capable AI agents

## Browse All Apps

```bash
infsh app list
```
