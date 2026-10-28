<?php
declare(strict_types=1);

final class GenericModel extends Model
{
    private const TABLES = [
        'departments' => ['id' => 'department_id', 'fields' => ['department_code', 'department_name', 'description']],
        'courses' => ['id' => 'course_id', 'fields' => ['department_id', 'course_code', 'course_name', 'duration_years', 'description']],
        'students' => ['id' => 'student_id', 'fields' => ['student_number', 'first_name', 'middle_name', 'last_name', 'sex', 'birth_date', 'email', 'phone', 'address', 'course_id', 'enrollment_date', 'status']],
        'academics' => ['id' => 'academic_id', 'fields' => ['student_id', 'school_year', 'semester', 'year_level', 'academic_status', 'remarks']],
    ];

    public function counts(): array
    {
        $out = [];
        foreach (['departments', 'courses', 'students', 'academics'] as $table) {
            $out[$table] = (int)$this->one("SELECT COUNT(*) AS total FROM `$table`")['total'];
        }
        return $out;
    }

    public function rows(string $entity): array
    {
        return match ($entity) {
            'departments' => $this->all('SELECT d.*, (SELECT COUNT(*) FROM courses c WHERE c.department_id=d.department_id) AS course_count FROM departments d ORDER BY department_name'),
            'courses' => $this->all('SELECT c.*, d.department_name FROM courses c JOIN departments d ON d.department_id=c.department_id ORDER BY course_name'),
            'students' => $this->all('SELECT s.*, c.course_name FROM students s JOIN courses c ON c.course_id=s.course_id ORDER BY last_name, first_name'),
            'academics' => $this->all('SELECT a.*, CONCAT(s.last_name, ", ", s.first_name) AS student_name, s.student_number FROM academics a JOIN students s ON s.student_id=a.student_id ORDER BY a.school_year DESC, s.last_name'),
            default => [],
        };
    }

    public function find(string $entity, int $id): ?array
    {
        $meta = self::TABLES[$entity] ?? null;
        if (!$meta) return null;
        $row = $this->one("SELECT * FROM `$entity` WHERE `{$meta['id']}` = ?", [$id]);
        if ($entity === 'students' && $row) {
            $row['course_name'] = $this->one('SELECT course_name FROM courses WHERE course_id=?', [$row['course_id']])['course_name'] ?? '';
        }
        if ($entity === 'academics' && $row) {
            $student = $this->one('SELECT student_number, first_name, last_name FROM students WHERE student_id=?', [$row['student_id']]);
            $row['student_name'] = $student ? $student['last_name'] . ', ' . $student['first_name'] : '';
            $row['student_number'] = $student['student_number'] ?? '';
        }
        if ($entity === 'courses' && $row) {
            $row['department_name'] = $this->one('SELECT department_name FROM departments WHERE department_id=?', [$row['department_id']])['department_name'] ?? '';
        }
        return $row;
    }

    public function save(string $entity, array $input, ?int $id = null): void
    {
        $meta = self::TABLES[$entity] ?? null;
        if (!$meta) throw new InvalidArgumentException('Unsupported record type.');

        $values = [];
        foreach ($meta['fields'] as $field) {
            $values[$field] = trim((string)($input[$field] ?? ''));
        }
        $this->validate($entity, $values, $id);

        foreach (['department_id', 'course_id', 'student_id', 'duration_years', 'year_level'] as $numeric) {
            if (array_key_exists($numeric, $values) && $values[$numeric] !== '') $values[$numeric] = (int)$values[$numeric];
        }
        foreach (['middle_name', 'description', 'email', 'phone', 'address', 'remarks', 'birth_date'] as $nullable) {
            if (array_key_exists($nullable, $values) && $values[$nullable] === '') $values[$nullable] = null;
        }

        if ($id) {
            $sets = implode(', ', array_map(fn($f) => "`$f` = ?", array_keys($values)));
            $this->execute("UPDATE `$entity` SET $sets WHERE `{$meta['id']}` = ?", [...array_values($values), $id]);
        } else {
            $cols = implode(', ', array_map(fn($f) => "`$f`", array_keys($values)));
            $marks = implode(', ', array_fill(0, count($values), '?'));
            $this->execute("INSERT INTO `$entity` ($cols) VALUES ($marks)", array_values($values));
        }
    }

    private function validate(string $entity, array $v, ?int $id): void
    {
        if ($entity === 'departments') {
            if ($v['department_code'] === '' || $v['department_name'] === '') throw new InvalidArgumentException('Department code and name are required.');
            if ($this->exists('departments', 'department_code', $v['department_code'], $id, 'department_id')) throw new InvalidArgumentException('That department code is already in use.');
        } elseif ($entity === 'courses') {
            if (!$v['department_id'] || $v['course_code'] === '' || $v['course_name'] === '') throw new InvalidArgumentException('Department, course code and course name are required.');
            if ((int)$v['duration_years'] < 1 || (int)$v['duration_years'] > 10) throw new InvalidArgumentException('Duration must be between 1 and 10 years.');
            if ($this->exists('courses', 'course_code', $v['course_code'], $id, 'course_id')) throw new InvalidArgumentException('That course code is already in use.');
        } elseif ($entity === 'students') {
            foreach (['student_number','first_name','last_name','course_id','enrollment_date','status'] as $f) if ($v[$f] === '') throw new InvalidArgumentException('Please complete all required student fields.');
            if ($this->exists('students', 'student_number', $v['student_number'], $id, 'student_id')) throw new InvalidArgumentException('That student number is already in use.');
            if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
            if (!in_array($v['status'], ['Active','Inactive','Graduated','Dropped'], true)) throw new InvalidArgumentException('Invalid student status.');
        } elseif ($entity === 'academics') {
            foreach (['student_id','school_year','semester','year_level','academic_status'] as $f) if ($v[$f] === '') throw new InvalidArgumentException('Please complete all required academic record fields.');
            if (!in_array($v['semester'], ['1st','2nd','Summer'], true)) throw new InvalidArgumentException('Invalid semester.');
            if (!in_array($v['academic_status'], ['Enrolled','Passed','Failed','Incomplete','On Leave'], true)) throw new InvalidArgumentException('Invalid academic status.');
            if ((int)$v['year_level'] < 1 || (int)$v['year_level'] > 10) throw new InvalidArgumentException('Year level must be between 1 and 10.');
        }
    }

    private function exists(string $table, string $field, string $value, ?int $id, string $idField): bool
    {
        $sql = "SELECT COUNT(*) AS n FROM `$table` WHERE `$field` = ?";
        $params = [$value];
        if ($id) { $sql .= " AND `$idField` <> ?"; $params[] = $id; }
        return (int)$this->one($sql, $params)['n'] > 0;
    }

    public function delete(string $entity, int $id): void
    {
        $meta = self::TABLES[$entity] ?? null;
        if (!$meta) throw new InvalidArgumentException('Unsupported record type.');
        $this->execute("DELETE FROM `$entity` WHERE `{$meta['id']}` = ?", [$id]);
    }

    public function departments(): array
    {
        return $this->all('SELECT department_id AS id, department_name AS name FROM departments ORDER BY department_name');
    }

    public function courses(): array
    {
        return $this->all('SELECT course_id AS id, course_name AS name FROM courses ORDER BY course_name');
    }

    public function students(): array
    {
        return $this->all('SELECT student_id AS id, CONCAT(student_number, " — ", last_name, ", ", first_name) AS name FROM students ORDER BY last_name');
    }

    public function studentHistory(int $studentId): array
    {
        return $this->all('SELECT * FROM academics WHERE student_id=? ORDER BY school_year DESC, semester', [$studentId]);
    }
}
