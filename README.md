# dokucategory

A [DokuWiki](https://www.dokuwiki.org) plugin that builds a nested category tree from the tags of the [tag plugin](https://www.dokuwiki.org/plugin:tag) and shows the number of pages per category.

## Usage

```
dokucategory{{
  * 경전
    * 단학
      * 종교
}}
```

Uses the normal DokuWiki list syntax. Each category counts the pages that have **its own tag and the tags of all its parent categories**:

- `경전` – pages tagged 경전
- `단학` – pages tagged 경전 and 단학
- `종교` – pages tagged 경전, 단학 and 종교

Clicking a category opens the tag plugin's page list for that tag combination.

## Install

Copy this folder to `lib/plugins/dokucategory` (or install the zip from the release page). The tag plugin is required.

## Settings

| Setting | Default | Description |
|---|---|---|
| `show_count` | on | show the page count after each category |

## License

GPL 2
