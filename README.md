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

This is the default (`match_mode = and`). If pages that have only the tag of a sub category (without the parent tag) should be listed too, switch the setting to `own` (see Settings): then a category counts every page that has **its own tag**, and a parent category counts the pages of all categories below it as well.

| `match_mode` | `웹` lists | `프로그래밍` lists |
|---|---|---|
| `and` (default) | pages tagged 프로그래밍 **and** 파이썬 **and** 웹 | pages tagged 프로그래밍 |
| `own` | pages tagged 웹 | pages tagged 프로그래밍, 파이썬 **or** 웹 |

Clicking a category opens the tag plugin's page list for that tag combination.

## Install

Copy this folder to `lib/plugins/dokucategory` (or install the zip from the release page). The tag plugin is required.

## Settings

| Setting | Default | Description |
|---|---|---|
| `show_count` | on | show the page count after each category |
| `match_mode` | `and` | `and`: a sub category needs the tags of all its parents too. `own`: a sub category needs only its own tag; a parent also counts the pages of its sub categories |

## License

MIT
