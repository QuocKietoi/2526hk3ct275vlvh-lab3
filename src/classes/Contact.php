<?php

namespace CT275\Labs;

use PDO;
use PDOException;

class Contact
{
    private ?PDO $db;
    public ?string $avatar = null;

    public int $id = -1;
    public $name;
    public $phone;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct(?PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function fill(array $data): Contact
    {
        $this->name = trim($data['name'] ?? '');
        $this->phone = trim($data['phone'] ?? '');
        $this->notes = trim($data['notes'] ?? '');
        $this->avatar = $data['avatar'] ?? $this->avatar;
        return $this;
    }

    protected function fillFromDbRow(array $row): Contact
    {
        $this->id = (int)$row['id'];
        $this->name = $row['name'];
        $this->phone = $row['phone'];
        $this->notes = $row['notes'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        return $this;
    }

    // Kiểm tra dữ liệu hợp lệ
    public function validate(array $data): array
    {
        $errors = [];
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            $errors['name'] = 'Tên không được để trống.';
        }

        $validPhone = preg_match('/^(03|05|07|08|09|01[2|6|8|9])\d{8}$/', $data['phone'] ?? '');
        if (!$validPhone) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        $notes = trim($data['notes'] ?? '');
        if (strlen($notes) > 255) {
            $errors['notes'] = 'Ghi chú không được vượt quá 255 ký tự.';
        }

        return $errors;
    }

    // Lưu, cập nhật liên hệ
    public function save(): bool
    {
        try {
            if ($this->id > 0) {
                $sql = "UPDATE contacts 
                        SET name = :name, phone = :phone, notes = :notes, avatar = :avatar, updated_at = NOW()
                        WHERE id = :id";
                $statement = $this->db->prepare($sql);
                return $statement->execute([
                    ':name' => $this->name,
                    ':phone' => $this->phone,
                    ':notes' => $this->notes,
                    ':avatar' => $this->avatar,
                    ':id' => $this->id
                ]);
            } else {
                $sql = "INSERT INTO contacts (name, phone, notes, avatar, created_at, updated_at)
                        VALUES (:name, :phone, :notes, :avatar, NOW(), NOW())";
                $statement = $this->db->prepare($sql);
                $result = $statement->execute([
                    ':name' => $this->name,
                    ':phone' => $this->phone,
                    ':notes' => $this->notes,
                    ':avatar' => $this->avatar
                ]);
                if ($result) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                return $result;
            }
        } catch (PDOException $e) {
            // Ghi log hoặc xử lý lỗi
            return false;
        }
    }

    // Tìm liên hệ với ID
    public function find(int $id): ?Contact
    {
        $statement = $this->db->prepare('SELECT * FROM contacts WHERE id = :id');
        $statement->execute([':id' => $id]);
        if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            return $this->fillFromDbRow($row);
        }
        return null;
    }

    // Xóa liên hệ
    public function delete(): bool
    {
        if ($this->id <= 0) return false;
        $statement = $this->db->prepare('DELETE FROM contacts WHERE id = :id');
        return $statement->execute([':id' => $this->id]);
    }

    // Trả về
    public function all(): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts ORDER BY created_at DESC');
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }
        return $contacts;
    }

    // Đếm số liên hệ
    public function count(): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM contacts');
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    // Phân trang liên hệ
    public function paginate(int $offset = 0, int $limit = 10): array
    {
        $contacts = [];
        $sql = 'SELECT * FROM contacts ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }
        return $contacts;
    }

    // Lấy đường dẫn avatar
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
}
