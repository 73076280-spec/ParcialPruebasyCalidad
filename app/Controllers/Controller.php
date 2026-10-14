<?php
final class Controller {
    public function __construct(private Blog $blog) {}
    private function view(string $template, array $vars = []): void { extract($vars); require ROOT . '/app/Views/layout.php'; }
    private function message(string $message, int $code = 400): void { http_response_code($code); $this->view('message', compact('message')); }
    public function run(): void {
        $r = is_string($_GET['r'] ?? null) ? $_GET['r'] : 'home';
        $method = $_SERVER['REQUEST_METHOD'];
        $routes = ['home', 'detail', 'login', 'logout', 'dashboard', 'news', 'news-edit', 'news-delete', 'categories', 'category-edit', 'category-delete'];
        if (!in_array($r, $routes, true)) { $this->message('Página no encontrada.', 404); return; }
        // La autorización se comprueba antes de cualquier lectura o escritura administrativa.
        if (!in_array($r, ['home', 'detail', 'login'], true) && empty($_SESSION['admin'])) redirect('login');
        if (!in_array($method, ['GET', 'POST'], true)) { $this->message('Método no permitido.', 405); return; }
        if ($method === 'POST' && !validCsrf($_POST['_token'] ?? null)) { $this->message('La sesión del formulario venció. Recargue la página.', 403); return; }
        if ($method === 'POST' && in_array($r, ['home', 'detail', 'dashboard', 'news', 'categories'], true)) { $this->message('Método no permitido.', 405); return; }
        if ($r === 'logout') {
            if ($method !== 'POST') { $this->message('El cierre de sesión requiere POST.', 405); return; }
            $_SESSION = []; session_regenerate_id(true); redirect('home');
        }
        if ($r === 'login') { $this->login($method); return; }
        $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        if (in_array($r, ['home', 'news'], true)) {
            $search = mb_substr(trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : ''), 0, 160);
            $category = filter_var($_GET['category'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $page = filter_var($_GET['page'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
            $listing = $this->blog->listing($search, $category, $page, $r === 'home');
            $this->view('listing', ['listing' => $listing, 'categories' => $this->blog->categories(), 'search' => $search, 'category' => $category, 'admin' => $r === 'news']); return;
        }
        if ($r === 'detail') { $news = $this->blog->news($id, true); if (!$news) { $this->message('Noticia no encontrada.', 404); return; } $this->view('detail', compact('news')); return; }
        if ($r === 'dashboard') {
            $stats = $this->blog->query("SELECT COUNT(*) total, SUM(status='published') published FROM news")->fetch();
            $stats['categories'] = count($this->blog->categories()); $this->view('dashboard', compact('stats')); return;
        }
        if ($r === 'categories') { $this->view('categories', ['categories' => $this->blog->query('SELECT c.*, (SELECT COUNT(*) FROM news n WHERE n.category_id=c.id) total FROM categories c ORDER BY name')->fetchAll()]); return; }
        if ($r === 'category-edit') { $this->categoryEdit($id, $method); return; }
        if ($r === 'news-edit') { $this->newsEdit($id, $method); return; }
        if (in_array($r, ['news-delete', 'category-delete'], true)) {
            $table = $r === 'news-delete' ? 'news' : 'categories';
            $item = $this->blog->query("SELECT * FROM $table WHERE id=?", [$id])->fetch();
            if (!$item) { $this->message('Registro no encontrado.', 404); return; }
            if ($method === 'POST') {
                try { $this->blog->query("DELETE FROM $table WHERE id=?", [$id]); }
                catch (PDOException $ex) { if ($ex->getCode() !== '23000') throw $ex; $this->message('No puede eliminar una categoría con noticias. Reasigne sus noticias primero.'); return; }
                // Las imágenes antiguas se conservan; evita borrar archivos aún referenciados.
                $_SESSION['flash'] = $table === 'news' ? 'Noticia eliminada correctamente.' : 'Categoría eliminada correctamente.'; redirect($table === 'news' ? 'news' : 'categories');
            }
            $related = $table === 'categories' ? (int)$this->blog->query('SELECT COUNT(*) FROM news WHERE category_id=?', [$id])->fetchColumn() : 0;
            $this->view('delete', ['item' => $item, 'route' => $r, 'id' => $id, 'related' => $related]); return;
        }
    }
    private function login(string $method): void {
        $errors = []; $email = '';
        if ($method === 'POST') {
            $email = normalizeText(inputText($_POST, 'email'));
            $user = $this->blog->query('SELECT * FROM admins WHERE email=?', [$email])->fetch();
            if ($user && password_verify(inputText($_POST, 'password'), $user['password_hash'])) {
                session_regenerate_id(true); $_SESSION['admin'] = $user['id']; $_SESSION['csrf'] = bin2hex(random_bytes(32)); redirect('dashboard');
            }
            $errors['password'] = 'Correo o contraseña incorrectos.';
        }
        $this->view('login', compact('errors', 'email'));
    }
    private function categoryEdit(int $id, string $method): void {
        $data = $id ? $this->blog->query('SELECT * FROM categories WHERE id=?', [$id])->fetch() : ['name' => '']; $errors = [];
        if (!$data) { $this->message('Categoría no encontrada.', 404); return; }
        if ($method === 'POST') {
            $data['name'] = inputText($_POST, 'name');
            $errors = validateCategory($data);
            if (!$errors) {
                try {
                    $name = normalizeText($data['name']);
                    if ($id) $this->blog->query('UPDATE categories SET name=? WHERE id=?', [$name, $id]);
                    else $this->blog->query('INSERT INTO categories(name) VALUES(?)', [$name]);
                    $_SESSION['flash'] = $id ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.'; redirect('categories');
                } catch (PDOException $ex) { if ($ex->getCode() !== '23000') throw $ex; $errors['name'] = 'Ya existe una categoría con ese nombre.'; }
            }
        }
        $this->view('category-form', compact('data', 'errors', 'id'));
    }
    private function newsEdit(int $id, string $method): void {
        $data = $id ? $this->blog->news($id) : ['title' => '', 'summary' => '', 'content' => '', 'category_id' => '', 'image' => null, 'image_alt' => '', 'status' => 'draft']; $errors = [];
        if (!$data) { $this->message('Noticia no encontrada.', 404); return; }
        if ($method === 'POST') {
            foreach (['title', 'summary', 'content', 'category_id', 'image_alt', 'status'] as $field) $data[$field] = inputText($_POST, $field);
            $file = $_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
            $hasNew = $file['error'] !== UPLOAD_ERR_NO_FILE;
            $errors = validateNews($data, (bool)$data['image'] || $hasNew);
            if (!isset($errors['category_id']) && !$this->blog->query('SELECT id FROM categories WHERE id=?', [$data['category_id']])->fetch()) $errors['category_id'] = 'La categoría seleccionada no existe.';
            if ($problem = validateImage($file)) $errors['image'] = $problem;
            if (!$errors) {
                $newPath = null;
                if ($hasNew) {
                    $data['image'] = bin2hex(random_bytes(16)) . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $newPath = ROOT . '/public/uploads/' . $data['image'];
                    if (!move_uploaded_file($file['tmp_name'], $newPath)) { $errors['image'] = 'No se pudo guardar la imagen. Revise los permisos.'; }
                }
                if (!$errors) {
                    $normalized = $data;
                    foreach (['title', 'summary', 'content', 'image_alt'] as $field) $normalized[$field] = normalizeText($data[$field]);
                    try { $this->blog->saveNews($normalized, $id ?: null); }
                    catch (Throwable $ex) { if ($newPath) unlink($newPath); throw $ex; }
                    $_SESSION['flash'] = $id ? 'Noticia actualizada correctamente.' : 'Noticia creada correctamente.'; redirect('news');
                }
            }
        }
        $this->view('news-form', ['data' => $data, 'errors' => $errors, 'id' => $id, 'categories' => $this->blog->categories()]);
    }
}
