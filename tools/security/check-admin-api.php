<?php
// Read-only route checks in an isolated CLI process; no login cookie is created.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(1); }
$root = isset($argv[1]) ? $argv[1] : dirname(dirname(__DIR__));
chdir($root);
require 'bootstrap/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
set_exception_handler(function ($exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
});
config(array('session.driver' => 'array'));
$admin = App\Models\User::whereHas('roles', function ($query) {
    $query->where('name', 'admin');
})->first();
if (!$admin) { throw new RuntimeException('An existing admin account is required for route checks.'); }
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
function checkAdminGet($path, $params = array()) {
    global $kernel, $admin;
    Auth::setUser($admin);
    $request = Illuminate\Http\Request::create('https://zeligen.art'.$path, 'GET', $params);
    $request->headers->set('Accept', 'application/json');
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    if ($status !== 200) { throw new RuntimeException($path.' returned HTTP '.$status); }
    $data = json_decode($response->getContent(), true);
    if (!is_array($data)) { throw new RuntimeException($path.' did not return JSON data.'); }
    echo $path.' HTTP 200'.PHP_EOL;
    return $data;
}
$contexts = checkAdminGet('/admin/api/contexts', array('_with' => 'pages_tree', 'is_hide' => 0));
if (!count($contexts)) { throw new RuntimeException('No visible contexts returned.'); }
$pageIds = array();
foreach ($contexts as $context) {
    if (!isset($context['pages_tree']) || !is_array($context['pages_tree'])) {
        throw new RuntimeException('Context has no page tree.');
    }
    foreach ($context['pages_tree'] as $page) {
        if (!isset($page['id']) || !isset($page['title'])) { throw new RuntimeException('Incomplete menu item.'); }
        $pageIds[] = $page['id'];
    }
}
if (!count($pageIds)) { throw new RuntimeException('Menu tree is empty.'); }
checkAdminGet('/admin/api/contexts', array('_with' => 'pages_tree', '_fields' => 'id,name', 'is_hide' => 0));
$empty = checkAdminGet('/admin/api/contexts', array('_with' => 'pages_tree', 'id' => -1));
if ($empty !== array()) { throw new RuntimeException('Empty filter did not return an empty list.'); }
checkAdminGet('/admin/api/pages', array('_with' => 'tags', '_limit' => 2));
checkAdminGet('/admin/api/pages/'.$pageIds[0]);
checkAdminGet('/admin/api/pages', array('_q' => 'menu compatibility check', '_limit' => 2));
checkAdminGet('/admin/api/templates');
checkAdminGet('/admin/api/tags');
$searchMode = config('apihandler.fulltext');
config(array('apihandler.fulltext' => 'native'));
$builder = App\Models\Page::query();
$parser = new Marcelgwerder\ApiHandler\Parser($builder, array());
$search = new ReflectionMethod($parser, 'parseFullTextSearch');
$search->setAccessible(true);
$search->invoke($parser, 'menu compatibility check', array('title'));
if (!in_array('*', $builder->getQuery()->columns)) {
    throw new RuntimeException('Native search did not preserve the default SELECT.');
}
config(array('apihandler.fulltext' => $searchMode));
echo 'Native search SQL construction passed (query not executed).'.PHP_EOL;
echo count($contexts).' contexts; '.count($pageIds).' root menu items. Read-only admin API checks passed.'.PHP_EOL;
