<?php
final class Blog {
    public function __construct(public PDO $pdo) {}
    // Todos los valores externos se enlazan; los fragmentos SQL son constantes internas.
    public function query(string $sql, array $params = []): PDOStatement { $s = $this->pdo->prepare($sql); $s->execute($params); return $s; }
    public function categories(): array { return $this->query('SELECT * FROM categories ORDER BY name')->fetchAll(); }
    public function news(int $id, bool $public = false): array|false { return $this->query('SELECT n.*, c.name category FROM news n JOIN categories c ON c.id=n.category_id WHERE n.id=?' . ($public ? " AND n.status='published'" : ''), [$id])->fetch(); }
    public function listing(string $search, int $category, int $page, bool $public): array {
        $where = $public ? "n.status='published'" : '1=1'; $args = [];
        if ($search !== '') { $where .= ' AND n.title LIKE ?'; $args[] = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%'; $where .= " ESCAPE '!'"; }
        if ($category > 0) { $where .= ' AND n.category_id=?'; $args[] = $category; }
        $total = (int)$this->query("SELECT COUNT(*) FROM news n WHERE $where", $args)->fetchColumn();
        $pages = max(1, (int)ceil($total / 6)); $page = max(1, min($page, $pages));
        $s = $this->pdo->prepare("SELECT n.*, c.name category FROM news n JOIN categories c ON c.id=n.category_id WHERE $where ORDER BY " . ($public ? 'n.published_at' : 'n.created_at') . ' DESC, n.id DESC LIMIT ? OFFSET ?');
        foreach ($args as $i => $arg) $s->bindValue($i + 1, $arg, is_int($arg) ? PDO::PARAM_INT : PDO::PARAM_STR);
        $s->bindValue(count($args) + 1, 6, PDO::PARAM_INT); $s->bindValue(count($args) + 2, ($page - 1) * 6, PDO::PARAM_INT); $s->execute();
        return ['rows' => $s->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page];
    }
    public function saveNews(array $d, ?int $id): void {
        $old = $id ? $this->news($id) : false;
        // Publicar asigna fecha; editar una noticia publicada conserva su fecha original.
        $published = $d['status'] === 'published' ? ($old['published_at'] ?? date('Y-m-d H:i:s')) : null;
        $args = [$d['title'], $d['summary'], $d['content'], $d['category_id'], $d['image'], $d['image_alt'], $d['status'], $published];
        if ($id) { $args[] = $id; $this->query('UPDATE news SET title=?,summary=?,content=?,category_id=?,image=?,image_alt=?,status=?,published_at=?,updated_at=CURRENT_TIMESTAMP WHERE id=?', $args); }
        else $this->query('INSERT INTO news(title,summary,content,category_id,image,image_alt,status,published_at) VALUES(?,?,?,?,?,?,?,?)', $args);
    }
}
