# dokucategory

A [DokuWiki](https://www.dokuwiki.org) plugin that builds a nested category tree from the tags of the [tag plugin](https://www.dokuwiki.org/plugin:tag) and shows the number of pages per category.

## Usage

```
dokucategory{{
  * 프로그래밍
    * 파이썬
      * 웹
}}
```

Uses the normal DokuWiki list syntax. Each category counts the pages that have **its own tag and the tags of all its parent categories**:

- `프로그래밍` – pages tagged 프로그래밍
- `파이썬` – pages tagged 프로그래밍 and 파이썬
- `웹` – pages tagged 프로그래밍, 파이썬 and 웹

Clicking a category opens the tag plugin's page list for that tag combination.

## Install

Copy this folder to `lib/plugins/dokucategory` (or install the zip from the release page). The tag plugin is required.

## Settings

| Setting | Default | Description |
|---|---|---|
| `show_count` | on | show the page count after each category |

## License

MIT
