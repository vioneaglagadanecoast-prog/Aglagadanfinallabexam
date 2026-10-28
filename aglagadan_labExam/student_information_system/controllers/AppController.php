<?php
declare(strict_types=1);

final class AppController
{
    private GenericModel $model;

    private const TITLES = [
        'departments' => 'Departments',
        'courses' => 'Courses',
        'students' => 'Students',
        'academics' => 'Academic Records',
    ];

    public function __construct(PDO $db, private array $config)
    {
        $this->model = new GenericModel($db);
    }

    public function dashboard(): void
    {
        Security::requireLogin();
        $counts = $this->model->counts();
        require dirname(__DIR__) . '/views/dashboard.php';
    }

    public function listing(string $entity): void
    {
        Security::requireLogin();
        $this->guardEntity($entity);
        $rows = $this->model->rows($entity);
        $title = self::TITLES[$entity];
        require dirname(__DIR__) . '/views/entity/list.php';
    }

    public function form(string $entity): void
    {
        Security::requireLogin();
        $this->guardEntity($entity);
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $record = $id ? $this->model->find($entity, $id) : null;
        if ($id && !$record) { $_SESSION['flash'] = ['type'=>'error','message'=>'Record not found.']; Security::redirect($entity); }
        $title = self::TITLES[$entity];
        $options = [
            'departments' => $this->model->departments(),
            'courses' => $this->model->courses(),
            'students' => $this->model->students(),
        ];
        $history = ($entity === 'students' && $id) ? $this->model->studentHistory($id) : [];
        require dirname(__DIR__) . '/views/entity/form.php';
    }

    public function save(string $entity): void
    {
        Security::requireLogin();
        Security::verifyCsrf();
        $this->guardEntity($entity);
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        try {
            $this->model->save($entity, $_POST, $id);
            Logger::write($this->config, 'info', 'record.saved', ['entity'=>$entity,'id'=>$id,'user_id'=>(int)$_SESSION['user_id']]);
            $_SESSION['flash'] = ['type'=>'success','message'=>self::TITLES[$entity] . ' record saved successfully.'];
            if ($entity === 'students' && $id) Security::redirect('students/view&id=' . $id);
            Security::redirect($entity);
        } catch (Throwable $e) {
            Logger::write($this->config, 'warning', 'record.save_failed', ['entity'=>$entity,'reason'=>$e->getMessage()]);
            $_SESSION['flash'] = ['type'=>'error','message'=>$e instanceof InvalidArgumentException ? $e->getMessage() : 'Unable to save this record. Check related records and try again.'];
            Security::redirect($entity . '/form' . ($id ? '&id=' . $id : ''));
        }
    }

    public function delete(string $entity): void
    {
        Security::requireLogin();
        Security::verifyCsrf();
        $this->guardEntity($entity);
        $id = (int)($_POST['id'] ?? 0);
        try {
            $this->model->delete($entity, $id);
            Logger::write($this->config, 'info', 'record.deleted', ['entity'=>$entity,'id'=>$id,'user_id'=>(int)$_SESSION['user_id']]);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Record deleted successfully.'];
        } catch (Throwable $e) {
            Logger::write($this->config, 'warning', 'record.delete_failed', ['entity'=>$entity,'id'=>$id,'reason'=>$e->getMessage()]);
            $_SESSION['flash'] = ['type'=>'error','message'=>'Could not delete this record. Remove dependent records first.'];
        }
        Security::redirect($entity);
    }

    public function viewStudent(): void
    {
        Security::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $student = $this->model->find('students', $id);
        if (!$student) { $_SESSION['flash']=['type'=>'error','message'=>'Student not found.']; Security::redirect('students'); }
        $history = $this->model->studentHistory($id);
        require dirname(__DIR__) . '/views/entity/student_view.php';
    }

    private function guardEntity(string $entity): void
    {
        if (!isset(self::TITLES[$entity])) {
            http_response_code(404);
            exit('Page not found.');
        }
    }
}
