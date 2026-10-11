<?php
/**
 * DokuCategory Plugin: builds a category tree from the tags of the tag plugin
 *
 * Syntax:
 *   dokucategory{{
 *     * 프로그래밍
 *       * 파이썬
 *         * 웹
 *   }}
 *
 * A node lists the pages that carry the tag of the node AND the tags of all its ancestors.
 *
 * @license MIT
 */

class syntax_plugin_dokucategory extends DokuWiki_Syntax_Plugin {

    public function getType() { return 'substition'; }
    public function getPType() { return 'block'; }
    public function getSort() { return 155; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('dokucategory\{\{.*?\}\}', $mode, 'plugin_dokucategory');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $body = substr($match, strlen('dokucategory{{'), -2);
        return ['tree' => $this->parseTree($body)];
    }

    /**
     * Turns the indented list into a nested array of ['name' => string, 'children' => array]
     */
    protected function parseTree($body) {
        $widths = [];  // indentation width per open level
        $items  = [];  // flat list of [level, name]

        foreach (preg_split('/\R/u', $body) as $line) {
            if (trim($line) === '') continue;
            if (!preg_match('/^([ \t]*)\*[ \t]*(.*)$/u', $line, $m)) continue;
            $name = trim($m[2]);
            if ($name === '') continue;

            $width = strlen(str_replace("\t", '    ', $m[1]));

            while (!empty($widths) && end($widths) > $width) array_pop($widths);
            if (empty($widths) || end($widths) < $width) $widths[] = $width;
            $items[] = [count($widths) - 1, $name];
        }

        $i = 0;
        return $this->buildTree($items, $i, 0);
    }

    /**
     * Builds the nodes of one level from the flat list, consuming $items from $i on
     */
    protected function buildTree($items, &$i, $level) {
        $nodes = [];
        while ($i < count($items)) {
            list($lvl, $name) = $items[$i];
            if ($lvl < $level) break;
            $i++;
            $nodes[] = ['name' => $name, 'children' => $this->buildTree($items, $i, $lvl + 1)];
        }
        return $nodes;
    }

    public function render($format, Doku_Renderer $renderer, $data) {
        if ($format !== 'xhtml') return false;

        // the result depends on other pages' tags: never serve it from the page cache
        $renderer->info['cache'] = false;

        /** @var helper_plugin_tag $tag */
        $tag = plugin_load('helper', 'tag');
        if (!$tag) {
            $renderer->doc .= '<div class="error">dokucategory: tag plugin is required</div>';
            return true;
        }

        $cache = [];
        $renderer->doc .= '<div class="plugin_dokucategory">' . DOKU_LF;
        if (empty($data['tree'])) {
            $renderer->doc .= '<span class="dokucategory_empty">(empty)</span>';
        } else {
            $renderer->doc .= $this->renderNodes($data['tree'], $tag, null, [], $cache);
        }
        $renderer->doc .= '</div>' . DOKU_LF;
        return true;
    }

    /**
     * @param array      $nodes  sibling nodes
     * @param object     $tag    tag helper
     * @param array|null $parent page ids matching all ancestors (null = no ancestors)
     * @param string[]   $path   cleaned tags of all ancestors
     * @param array      $cache  tag => page ids
     */
    protected function renderNodes($nodes, $tag, $parent, $path, &$cache) {
        global $ID;
        $html = '<ul>' . DOKU_LF;
        // and: a child needs the tags of all its parents too / own: a child needs only its own tag
        $own = $this->getConf('match_mode') === 'own';

        foreach ($nodes as $node) {
            $clean = $tag->parseTagList($node['name'], true);
            $label = str_replace('_', ' ', trim($node['name'], '"'));
            $mine  = $clean ? $clean[0] : '';
            $tags  = array_merge($path, $mine === '' ? [] : [$mine]);

            if ($own) {
                // pages that have this tag or the tag of any category below it
                $set   = $this->tagsOf($node, $tag);
                $pages = [];
                foreach ($set as $t) {
                    $pages = array_merge($pages, $this->pagesWithTag($t, $tag, $cache));
                }
                $pages = array_values(array_unique($pages));
                $query = implode(' ', $set);                 // "a b c": any of them
            } else {
                // pages with this tag, restricted to the pages that match all ancestors
                $pages = $mine === '' ? [] : $this->pagesWithTag($mine, $tag, $cache);
                if ($parent !== null) $pages = array_values(array_intersect($pages, $parent));
                $query = implode(' +', $tags);               // "a +b +c": all of them
            }
            $pages = array_values(array_filter($pages, function ($id) use ($tag) {
                return page_exists($id) && $tag->isVisible($id);
            }));

            $url = wl($ID, ['do' => 'showtag', 'tag' => $query]);

            $html .= '<li class="level1"><div class="li">'
                . '<a href="' . $url . '" rel="tag">' . hsc($label) . '</a>'
                . ($this->getConf('show_count')
                    ? ' <span class="dokucategory_count">(' . count($pages) . ')</span>' : '')
                . '</div>' . DOKU_LF;
            if (!empty($node['children'])) {
                $html .= $this->renderNodes($node['children'], $tag, $own ? null : $pages, $tags, $cache);
            }
            $html .= '</li>' . DOKU_LF;
        }
        return $html . '</ul>' . DOKU_LF;
    }

    /** page ids that carry the (cleaned) tag, cached per tag */
    protected function pagesWithTag($clean, $tag, &$cache) {
        if (!isset($cache[$clean])) {
            $cache[$clean] = array_values($tag->getIndexedPagesMatchingTagQuery([$clean]));
        }
        return $cache[$clean];
    }

    /** cleaned tags of a node and of all nodes below it */
    protected function tagsOf($node, $tag) {
        $out = [];
        $clean = $tag->parseTagList($node['name'], true);
        if ($clean && $clean[0] !== '') $out[] = $clean[0];
        foreach ($node['children'] as $child) {
            $out = array_merge($out, $this->tagsOf($child, $tag));
        }
        return array_values(array_unique($out));
    }
}
